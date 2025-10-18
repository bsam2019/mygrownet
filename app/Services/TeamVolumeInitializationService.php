<?php

namespace App\Services;

use App\Models\User;
use App\Models\Investment;
use App\Models\TeamVolume;
use App\Models\UserNetwork;
use App\Models\ReferralCommission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TeamVolumeInitializationService
{
    /**
     * Initialize team volume tracking for existing users
     */
    public function initializeTeamVolumeTracking(bool $dryRun = false): array
    {
        $results = [
            'users_processed' => 0,
            'team_volumes_created' => 0,
            'network_paths_updated' => 0,
            'performance_bonuses_calculated' => 0,
            'errors' => []
        ];

        try {
            if (!$dryRun) {
                DB::beginTransaction();
            }

            // Step 1: Build network relationships for efficient queries
            $networkResults = $this->buildNetworkRelationships($dryRun);
            $results['network_paths_updated'] = $networkResults['paths_updated'];

            // Step 2: Calculate historical team volumes
            $volumeResults = $this->calculateHistoricalTeamVolumes($dryRun);
            $results['users_processed'] = $volumeResults['users_processed'];
            $results['team_volumes_created'] = $volumeResults['volumes_created'];

            // Step 3: Initialize performance bonus eligibility
            $bonusResults = $this->initializePerformanceBonusEligibility($dryRun);
            $results['performance_bonuses_calculated'] = $bonusResults['bonuses_calculated'];

            if (!$dryRun) {
                DB::commit();
            }

            Log::info('Team volume initialization completed', $results);

        } catch (\Exception $e) {
            if (!$dryRun) {
                DB::rollBack();
            }
            
            $results['errors'][] = $e->getMessage();
            Log::error('Team volume initialization failed', [
                'error' => $e->getMessage(),
                'results' => $results
            ]);
            
            throw $e;
        }

        return $results;
    }

    /**
     * Build network relationships using existing referral data
     */
    public function buildNetworkRelationships(bool $dryRun = false): array
    {
        $pathsUpdated = 0;

        // Process users level by level to build network paths efficiently
        $maxLevel = 10; // Safety limit
        
        for ($level = 0; $level < $maxLevel; $level++) {
            $processed = 0;
            
            if ($level === 0) {
                // Process root users (no referrer)
                $rootUsers = User::whereNull('referrer_id')
                    ->whereNull('network_path')
                    ->select('id')
                    ->get();

                foreach ($rootUsers as $user) {
                    if (!$dryRun) {
                        User::where('id', $user->id)->update([
                            'network_path' => (string) $user->id,
                            'network_level' => 0,
                            'updated_at' => now()
                        ]);
                    }
                    $processed++;
                }
            } else {
                // Process users whose referrers already have network paths
                $users = User::select('users.id', 'users.referrer_id', 'referrers.network_path as referrer_path', 'referrers.network_level as referrer_level')
                    ->join('users as referrers', 'users.referrer_id', '=', 'referrers.id')
                    ->whereNotNull('users.referrer_id')
                    ->whereNull('users.network_path')
                    ->whereNotNull('referrers.network_path')
                    ->where('referrers.network_level', $level - 1)
                    ->get();

                foreach ($users as $user) {
                    $path = $user->referrer_path . '.' . $user->id;
                    $userLevel = $user->referrer_level + 1;

                    if (!$dryRun) {
                        User::where('id', $user->id)->update([
                            'network_path' => $path,
                            'network_level' => $userLevel,
                            'updated_at' => now()
                        ]);

                        // Create UserNetwork entries for efficient queries
                        $this->createUserNetworkEntries($user->id, $path);
                    }
                    $processed++;
                }
            }

            $pathsUpdated += $processed;
            
            if ($processed === 0) {
                break; // No more users to process at this level
            }
        }

        return ['paths_updated' => $pathsUpdated];
    }

    /**
     * Create UserNetwork entries for a user based on their network path
     */
    private function createUserNetworkEntries(int $userId, string $networkPath): void
    {
        $pathParts = explode('.', $networkPath);
        
        // Skip the user's own ID (last part)
        array_pop($pathParts);
        
        foreach ($pathParts as $level => $referrerId) {
            UserNetwork::updateOrCreate([
                'user_id' => $userId,
                'referrer_id' => (int) $referrerId,
                'level' => $level + 1
            ], [
                'path' => $networkPath,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Calculate historical team volumes from existing referral and investment data
     */
    public function calculateHistoricalTeamVolumes(bool $dryRun = false): array
    {
        $usersProcessed = 0;
        $volumesCreated = 0;

        // Process users in smaller batches with memory management
        User::select('id', 'network_path', 'network_level')
            ->whereNotNull('network_path')
            ->chunk(20, function ($users) use ($dryRun, &$usersProcessed, &$volumesCreated) {
                foreach ($users as $user) {
                    // Clear memory after each user to prevent accumulation
                    gc_collect_cycles();
                    
                    $volumeData = $this->calculateUserTeamVolume($user);
                    
                    if (!$dryRun) {
                        // Create team volume record for current period
                        TeamVolume::updateOrCreate([
                            'user_id' => $user->id,
                            'period_start' => now()->startOfMonth(),
                            'period_end' => now()->endOfMonth()
                        ], array_merge($volumeData, [
                            'created_at' => now(),
                            'updated_at' => now()
                        ]));

                        // Update user's current team volume fields
                        User::where('id', $user->id)->update([
                            'current_team_volume' => $volumeData['team_volume'],
                            'current_personal_volume' => $volumeData['personal_volume'],
                            'current_team_depth' => $volumeData['team_depth'],
                            'active_referrals_count' => $volumeData['active_referrals_count'],
                            'updated_at' => now()
                        ]);

                        $volumesCreated++;
                    }
                    
                    $usersProcessed++;
                }
                
                // Force garbage collection after each chunk
                gc_collect_cycles();
            });

        return [
            'users_processed' => $usersProcessed,
            'volumes_created' => $volumesCreated
        ];
    }

    /**
     * Calculate team volume data for a specific user
     */
    private function calculateUserTeamVolume(User $user): array
    {
        // Calculate personal volume from active investments
        $personalVolume = Investment::where('user_id', $user->id)
            ->where('status', 'active')
            ->sum('amount') ?? 0;

        // Initialize counters
        $teamVolume = $personalVolume;
        $activeReferralsCount = 0;
        $maxDepth = 0;

        // Process downline members in chunks to avoid memory issues
        if ($user->network_path) {
            User::where('network_path', 'LIKE', $user->network_path . '.%')
                ->where('network_level', '>', $user->network_level)
                ->where('network_level', '<=', $user->network_level + 5)
                ->select('id', 'network_level')
                ->chunk(50, function ($downlineMembers) use (&$teamVolume, &$activeReferralsCount, &$maxDepth, $user) {
                    foreach ($downlineMembers as $member) {
                        $memberVolume = Investment::where('user_id', $member->id)
                            ->where('status', 'active')
                            ->sum('amount') ?? 0;

                        $teamVolume += $memberVolume;
                        
                        // Count as active referral if it's a direct referral with investments
                        if ($member->network_level === $user->network_level + 1 && $memberVolume > 0) {
                            $activeReferralsCount++;
                        }

                        $depth = $member->network_level - $user->network_level;
                        $maxDepth = max($maxDepth, $depth);
                    }
                });
        }

        return [
            'personal_volume' => $personalVolume,
            'team_volume' => $teamVolume,
            'team_depth' => $maxDepth,
            'active_referrals_count' => $activeReferralsCount
        ];
    }

    /**
     * Initialize performance bonus eligibility based on existing user activity
     */
    public function initializePerformanceBonusEligibility(bool $dryRun = false): array
    {
        $bonusesCalculated = 0;

        // Get users with team volumes
        TeamVolume::with('user.membershipTier')
            ->where('period_start', '>=', now()->startOfMonth())
            ->chunk(50, function ($teamVolumes) use ($dryRun, &$bonusesCalculated) {
                foreach ($teamVolumes as $teamVolume) {
                    $user = $teamVolume->user;
                    $tier = $user->membershipTier;

                    if (!$tier) {
                        continue;
                    }

                    // Calculate performance bonus based on team volume
                    $performanceBonus = $this->calculatePerformanceBonus($teamVolume, $tier);

                    if ($performanceBonus > 0 && !$dryRun) {
                        // Record performance bonus eligibility
                        DB::table('performance_bonuses')->updateOrInsert([
                            'user_id' => $user->id,
                            'period_start' => $teamVolume->period_start,
                            'period_end' => $teamVolume->period_end
                        ], [
                            'team_volume' => $teamVolume->team_volume,
                            'bonus_rate' => $tier->monthly_team_volume_bonus_rate,
                            'bonus_amount' => $performanceBonus,
                            'status' => 'eligible',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);

                        $bonusesCalculated++;
                    }
                }
            });

        return ['bonuses_calculated' => $bonusesCalculated];
    }

    /**
     * Calculate performance bonus for a user based on team volume and tier
     */
    private function calculatePerformanceBonus(TeamVolume $teamVolume, $tier): float
    {
        if (!$tier->monthly_team_volume_bonus_rate || $tier->monthly_team_volume_bonus_rate <= 0) {
            return 0;
        }

        return $teamVolume->team_volume * ($tier->monthly_team_volume_bonus_rate / 100);
    }

    /**
     * Get team volume statistics for reporting
     */
    public function getTeamVolumeStatistics(): array
    {
        return [
            'total_users' => User::count(),
            'users_with_network_paths' => User::whereNotNull('network_path')->count(),
            'users_with_team_volumes' => TeamVolume::distinct('user_id')->count(),
            'total_team_volume' => TeamVolume::sum('team_volume'),
            'average_team_volume' => TeamVolume::avg('team_volume'),
            'users_with_active_referrals' => User::where('active_referrals_count', '>', 0)->count(),
            'max_network_depth' => User::max('network_level'),
            'performance_bonus_eligible_users' => DB::table('performance_bonuses')
                ->where('status', 'eligible')
                ->distinct('user_id')
                ->count()
        ];
    }

    /**
     * Validate team volume calculations
     */
    public function validateTeamVolumeCalculations(): array
    {
        $issues = [];

        // Check for users without network paths who have referrers
        $usersWithoutPaths = User::whereNotNull('referrer_id')
            ->whereNull('network_path')
            ->count();

        if ($usersWithoutPaths > 0) {
            $issues[] = "Found {$usersWithoutPaths} users with referrers but no network paths";
        }

        // Check for inconsistent team volume calculations
        $inconsistentVolumes = TeamVolume::whereRaw('team_volume < personal_volume')->count();
        
        if ($inconsistentVolumes > 0) {
            $issues[] = "Found {$inconsistentVolumes} team volumes where team_volume < personal_volume";
        }

        // Check for users with team volumes but no investments
        $volumesWithoutInvestments = TeamVolume::whereRaw('personal_volume > 0')
            ->whereDoesntHave('user.investments', function ($query) {
                $query->where('status', 'active');
            })
            ->count();

        if ($volumesWithoutInvestments > 0) {
            $issues[] = "Found {$volumesWithoutInvestments} users with personal volume but no active investments";
        }

        return [
            'is_valid' => empty($issues),
            'issues' => $issues,
            'validation_timestamp' => now()
        ];
    }

    /**
     * Recalculate team volumes for specific users
     */
    public function recalculateTeamVolumes(array $userIds, bool $dryRun = false): array
    {
        $recalculated = 0;

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if (!$user || !$user->network_path) {
                continue;
            }

            $volumeData = $this->calculateUserTeamVolume($user);

            if (!$dryRun) {
                TeamVolume::updateOrCreate([
                    'user_id' => $user->id,
                    'period_start' => now()->startOfMonth(),
                    'period_end' => now()->endOfMonth()
                ], array_merge($volumeData, [
                    'updated_at' => now()
                ]));

                User::where('id', $user->id)->update([
                    'current_team_volume' => $volumeData['team_volume'],
                    'current_personal_volume' => $volumeData['personal_volume'],
                    'current_team_depth' => $volumeData['team_depth'],
                    'active_referrals_count' => $volumeData['active_referrals_count'],
                    'updated_at' => now()
                ]);
            }

            $recalculated++;
        }

        return ['recalculated' => $recalculated];
    }
}