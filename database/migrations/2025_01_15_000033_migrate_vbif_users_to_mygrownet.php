<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\ReferralCommission;

return new class extends Migration
{
    // VBIF to MyGrowNet tier mapping
    private const TIER_MAPPING = [
        'Basic' => 'Bronze',
        'Starter' => 'Silver', 
        'Builder' => 'Gold',
        'Leader' => 'Diamond',
        'Elite' => 'Elite'
    ];

    public function up(): void
    {
        // Step 1: Map existing investment tiers to MyGrowNet membership tiers
        $this->mapInvestmentTiers();

        // Step 2: Migrate user tier assignments
        $this->migrateUserTiers();

        // Step 3: Update commission history for five-level structure
        $this->updateCommissionHistory();

        // Step 4: Initialize network paths for efficient queries
        $this->initializeNetworkPaths();

        // Step 5: Initialize team volume tracking
        $this->initializeTeamVolumeTracking();
    }

    public function down(): void
    {
        // Reverse the migration by restoring VBIF tiers
        $this->restoreVBIFTiers();
    }

    private function mapInvestmentTiers(): void
    {
        echo "Mapping existing investment tiers to MyGrowNet membership tiers...\n";

        $existingTiers = InvestmentTier::whereIn('name', array_keys(self::TIER_MAPPING))->get();
        $myGrowNetTiers = InvestmentTier::whereIn('name', array_values(self::TIER_MAPPING))->get()->keyBy('name');

        foreach ($existingTiers as $tier) {
            $newTierName = self::TIER_MAPPING[$tier->name];
            $newTier = $myGrowNetTiers->get($newTierName);

            if (!$newTier) {
                echo "Warning: MyGrowNet tier '{$newTierName}' not found for VBIF tier '{$tier->name}'\n";
                continue;
            }

            // Archive old VBIF tier
            $tier->update([
                'is_active' => false,
                'is_archived' => true,
                'description' => $tier->description . ' [ARCHIVED - Migrated to MyGrowNet]'
            ]);

            echo "Mapped {$tier->name} -> {$newTier->name}\n";
        }
    }

    private function migrateUserTiers(): void
    {
        echo "Migrating user tier assignments...\n";

        $myGrowNetTiers = InvestmentTier::whereIn('name', array_values(self::TIER_MAPPING))
            ->get()
            ->keyBy('name');

        // Process users in batches to avoid memory issues
        DB::table('users')
            ->join('investment_tiers', 'users.current_investment_tier_id', '=', 'investment_tiers.id')
            ->whereIn('investment_tiers.name', array_keys(self::TIER_MAPPING))
            ->select('users.id', 'users.current_investment_tier_id', 'investment_tiers.name as tier_name')
            ->orderBy('users.id')
            ->chunk(100, function($users) use ($myGrowNetTiers) {
                foreach ($users as $user) {
                    $oldTierName = $user->tier_name;
                    $newTierName = self::TIER_MAPPING[$oldTierName];
                    $newTier = $myGrowNetTiers->get($newTierName);

                    if (!$newTier) {
                        echo "Warning: Could not find MyGrowNet tier '{$newTierName}' for user {$user->id}\n";
                        continue;
                    }

                    // Update user's tier and subscription status
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'current_investment_tier_id' => $newTier->id,
                            'subscription_status' => 'active',
                            'subscription_start_date' => now(),
                            'subscription_end_date' => now()->addMonth(),
                            'monthly_subscription_fee' => $newTier->monthly_fee,
                            'updated_at' => now()
                        ]);

                    // Add tier history entry
                    $tierHistory = DB::table('users')->where('id', $user->id)->value('tier_history');
                    $history = $tierHistory ? json_decode($tierHistory, true) : [];
                    $history[] = [
                        'tier_id' => $newTier->id,
                        'date' => now()->toDateTimeString(),
                        'reason' => 'mygrownet_migration'
                    ];

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'tier_history' => json_encode($history),
                            'updated_at' => now()
                        ]);

                    echo "Migrated user {$user->id}: {$oldTierName} -> {$newTierName}\n";
                }
            });
    }

    private function updateCommissionHistory(): void
    {
        echo "Updating commission history for five-level structure...\n";

        // Update existing commissions with new structure
        DB::table('referral_commissions')
            ->whereIn('level', [1, 2, 3])
            ->update([
                'commission_type' => 'REFERRAL',
                'package_type' => 'VBIF_INVESTMENT',
                'updated_at' => now()
            ]);

        // Calculate package amounts where missing
        DB::table('referral_commissions')
            ->whereNull('package_amount')
            ->where('percentage', '>', 0)
            ->orderBy('id')
            ->chunk(100, function($commissions) {
                foreach ($commissions as $commission) {
                    $packageAmount = $commission->amount / ($commission->percentage / 100);
                    
                    DB::table('referral_commissions')
                        ->where('id', $commission->id)
                        ->update([
                            'package_amount' => $packageAmount,
                            'updated_at' => now()
                        ]);
                }
            });

        echo "Commission history updated\n";
    }

    private function initializeNetworkPaths(): void
    {
        echo "Initializing network paths for efficient queries...\n";

        // Update network paths for users with referrers
        DB::table('users')
            ->whereNotNull('referrer_id')
            ->whereNull('network_path')
            ->orderBy('id')
            ->chunk(100, function($users) {
                foreach ($users as $user) {
                    $this->updateUserNetworkPath($user->id);
                }
            });

        echo "Network paths initialized\n";
    }

    private function updateUserNetworkPath(int $userId): void
    {
        $user = DB::table('users')->where('id', $userId)->first();
        
        if (!$user || !$user->referrer_id) {
            // Root user
            DB::table('users')
                ->where('id', $userId)
                ->update([
                    'network_path' => (string) $userId,
                    'network_level' => 0,
                    'updated_at' => now()
                ]);
            return;
        }

        $referrer = DB::table('users')->where('id', $user->referrer_id)->first();
        
        if (!$referrer) {
            return;
        }

        // Ensure referrer has network path
        if (!$referrer->network_path) {
            $this->updateUserNetworkPath($referrer->id);
            $referrer = DB::table('users')->where('id', $user->referrer_id)->first();
        }

        $path = $referrer->network_path ? $referrer->network_path . '.' . $userId : (string) $userId;
        $level = ($referrer->network_level ?? 0) + 1;

        DB::table('users')
            ->where('id', $userId)
            ->update([
                'network_path' => $path,
                'network_level' => $level,
                'updated_at' => now()
            ]);
    }

    private function initializeTeamVolumeTracking(): void
    {
        echo "Initializing team volume tracking...\n";

        // Create team volume records for all users
        DB::table('users')
            ->select('id')
            ->orderBy('id')
            ->chunk(100, function($users) {
                foreach ($users as $user) {
                    $this->createInitialTeamVolume($user->id);
                }
            });

        echo "Team volume tracking initialized\n";
    }

    private function createInitialTeamVolume(int $userId): void
    {
        // Calculate personal volume from investments
        $personalVolume = DB::table('investments')
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->sum('amount') ?? 0;

        // Calculate team volume from direct referrals
        $directReferrals = DB::table('users')
            ->where('referrer_id', $userId)
            ->pluck('id');

        $teamVolume = $personalVolume;
        $activeReferralsCount = 0;

        foreach ($directReferrals as $referralId) {
            $referralInvestments = DB::table('investments')
                ->where('user_id', $referralId)
                ->where('status', 'active')
                ->sum('amount') ?? 0;

            $teamVolume += $referralInvestments;
            
            if ($referralInvestments > 0) {
                $activeReferralsCount++;
            }
        }

        // Insert team volume record
        DB::table('team_volumes')->updateOrInsert([
            'user_id' => $userId,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth()
        ], [
            'personal_volume' => $personalVolume,
            'team_volume' => $teamVolume,
            'team_depth' => $directReferrals->count() > 0 ? 1 : 0,
            'active_referrals_count' => $activeReferralsCount,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Update user's current team volume fields
        DB::table('users')
            ->where('id', $userId)
            ->update([
                'current_team_volume' => $teamVolume,
                'current_personal_volume' => $personalVolume,
                'current_team_depth' => $directReferrals->count() > 0 ? 1 : 0,
                'active_referrals_count' => $activeReferralsCount,
                'updated_at' => now()
            ]);
    }

    private function restoreVBIFTiers(): void
    {
        echo "Restoring VBIF tiers...\n";

        // Reactivate archived VBIF tiers
        InvestmentTier::whereIn('name', array_keys(self::TIER_MAPPING))
            ->update([
                'is_active' => true,
                'is_archived' => false
            ]);

        echo "VBIF tiers restored\n";
    }
};