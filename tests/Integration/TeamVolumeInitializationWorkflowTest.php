<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Services\TeamVolumeInitializationService;
use App\Models\User;
use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\TeamVolume;
use App\Models\UserNetwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class TeamVolumeInitializationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private TeamVolumeInitializationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TeamVolumeInitializationService();
        $this->createMyGrowNetTiers();
    }

    public function test_complete_team_volume_initialization_workflow()
    {
        // Create a complex network structure for testing
        $network = $this->createComplexNetworkStructure();
        
        // Run the complete initialization
        $results = $this->service->initializeTeamVolumeTracking();

        // Verify no errors occurred
        $this->assertEmpty($results['errors'], 'Initialization should complete without errors');
        $this->assertGreaterThan(0, $results['users_processed']);
        $this->assertGreaterThan(0, $results['network_paths_updated']);
        $this->assertGreaterThan(0, $results['team_volumes_created']);

        // Verify network paths are correctly built
        $this->verifyNetworkPaths($network);

        // Verify team volumes are correctly calculated
        $this->verifyTeamVolumeCalculations($network);

        // Verify performance bonuses are initialized
        $this->verifyPerformanceBonuses($network);

        // Verify user fields are updated
        $this->verifyUserFieldUpdates($network);
    }

    public function test_handles_large_network_efficiently()
    {
        // Create a larger network (100 users in 5 levels)
        $network = $this->createLargeNetworkStructure(100, 5);

        $startTime = microtime(true);
        $results = $this->service->initializeTeamVolumeTracking();
        $endTime = microtime(true);

        $executionTime = $endTime - $startTime;

        // Should complete within reasonable time (adjust based on system)
        $this->assertLessThan(30, $executionTime, 'Should complete within 30 seconds for 100 users');
        $this->assertEmpty($results['errors']);
        $this->assertEquals(100, $results['users_processed']);
    }

    public function test_validation_detects_inconsistencies()
    {
        // Create valid network first
        $network = $this->createComplexNetworkStructure();
        $this->service->initializeTeamVolumeTracking();

        // Introduce inconsistencies
        $this->introduceDataInconsistencies($network);

        // Run validation
        $validation = $this->service->validateTeamVolumeCalculations();

        $this->assertFalse($validation['is_valid']);
        $this->assertNotEmpty($validation['issues']);
    }

    public function test_recalculation_fixes_incorrect_data()
    {
        // Create network and initialize
        $network = $this->createComplexNetworkStructure();
        $this->service->initializeTeamVolumeTracking();

        // Corrupt some data
        $user = $network['users'][0];
        TeamVolume::where('user_id', $user->id)->update([
            'personal_volume' => 999, // Wrong value
            'team_volume' => 999
        ]);

        // Recalculate
        $results = $this->service->recalculateTeamVolumes([$user->id]);

        $this->assertEquals(1, $results['recalculated']);

        // Verify correction
        $teamVolume = TeamVolume::where('user_id', $user->id)->first();
        $expectedPersonalVolume = Investment::where('user_id', $user->id)
            ->where('status', 'active')
            ->sum('amount');
        
        $this->assertEquals($expectedPersonalVolume, $teamVolume->personal_volume);
    }

    public function test_statistics_provide_accurate_metrics()
    {
        $network = $this->createComplexNetworkStructure();
        $this->service->initializeTeamVolumeTracking();

        $stats = $this->service->getTeamVolumeStatistics();

        // Verify statistics accuracy
        $this->assertEquals(count($network['users']), $stats['users_with_network_paths']);
        $this->assertEquals(count($network['users']), $stats['users_with_team_volumes']);
        $this->assertGreaterThan(0, $stats['total_team_volume']);
        $this->assertGreaterThan(0, $stats['average_team_volume']);
    }

    private function createComplexNetworkStructure(): array
    {
        $silverTier = InvestmentTier::where('name', 'Silver')->first();
        $bronzeTier = InvestmentTier::where('name', 'Bronze')->first();

        // Create network: root -> [user1, user2] -> [user3, user4, user5, user6]
        $root = User::factory()->create(['current_investment_tier_id' => $silverTier->id]);
        
        $user1 = User::factory()->create([
            'referrer_id' => $root->id,
            'current_investment_tier_id' => $bronzeTier->id
        ]);
        
        $user2 = User::factory()->create([
            'referrer_id' => $root->id,
            'current_investment_tier_id' => $bronzeTier->id
        ]);
        
        $user3 = User::factory()->create([
            'referrer_id' => $user1->id,
            'current_investment_tier_id' => $bronzeTier->id
        ]);
        
        $user4 = User::factory()->create([
            'referrer_id' => $user1->id,
            'current_investment_tier_id' => $bronzeTier->id
        ]);
        
        $user5 = User::factory()->create([
            'referrer_id' => $user2->id,
            'current_investment_tier_id' => $bronzeTier->id
        ]);
        
        $user6 = User::factory()->create([
            'referrer_id' => $user2->id,
            'current_investment_tier_id' => $bronzeTier->id
        ]);

        $users = [$root, $user1, $user2, $user3, $user4, $user5, $user6];

        // Create investments for each user
        $investments = [];
        foreach ($users as $i => $user) {
            $amount = 1000 + ($i * 200); // Varying amounts
            $investment = Investment::factory()->create([
                'user_id' => $user->id,
                'amount' => $amount,
                'status' => 'active'
            ]);
            $investments[] = $investment;
        }

        return [
            'users' => $users,
            'investments' => $investments,
            'structure' => [
                'root' => $root,
                'level1' => [$user1, $user2],
                'level2' => [$user3, $user4, $user5, $user6]
            ]
        ];
    }

    private function createLargeNetworkStructure(int $totalUsers, int $maxLevels): array
    {
        $bronzeTier = InvestmentTier::where('name', 'Bronze')->first();
        $users = [];
        
        // Create root user
        $root = User::factory()->create(['current_investment_tier_id' => $bronzeTier->id]);
        $users[] = $root;
        Investment::factory()->create(['user_id' => $root->id, 'amount' => 1000, 'status' => 'active']);

        // Create users level by level
        $currentLevel = [$root];
        
        for ($level = 1; $level < $maxLevels && count($users) < $totalUsers; $level++) {
            $nextLevel = [];
            
            foreach ($currentLevel as $parent) {
                $childrenCount = min(3, $totalUsers - count($users)); // Max 3 children per parent
                
                for ($i = 0; $i < $childrenCount && count($users) < $totalUsers; $i++) {
                    $user = User::factory()->create([
                        'referrer_id' => $parent->id,
                        'current_investment_tier_id' => $bronzeTier->id
                    ]);
                    
                    Investment::factory()->create([
                        'user_id' => $user->id,
                        'amount' => rand(500, 2000),
                        'status' => 'active'
                    ]);
                    
                    $users[] = $user;
                    $nextLevel[] = $user;
                }
            }
            
            $currentLevel = $nextLevel;
        }

        return ['users' => $users];
    }

    private function verifyNetworkPaths(array $network): void
    {
        foreach ($network['users'] as $user) {
            $user->refresh();
            $this->assertNotNull($user->network_path, "User {$user->id} should have network path");
            $this->assertNotNull($user->network_level, "User {$user->id} should have network level");
        }

        // Verify specific path structure
        $root = $network['structure']['root'];
        $root->refresh();
        $this->assertEquals((string) $root->id, $root->network_path);
        $this->assertEquals(0, $root->network_level);

        foreach ($network['structure']['level1'] as $user) {
            $user->refresh();
            $this->assertStringStartsWith($root->id . '.', $user->network_path);
            $this->assertEquals(1, $user->network_level);
        }
    }

    private function verifyTeamVolumeCalculations(array $network): void
    {
        foreach ($network['users'] as $user) {
            $teamVolume = TeamVolume::where('user_id', $user->id)->first();
            $this->assertNotNull($teamVolume, "User {$user->id} should have team volume record");
            
            // Verify personal volume matches investments
            $expectedPersonalVolume = Investment::where('user_id', $user->id)
                ->where('status', 'active')
                ->sum('amount');
            $this->assertEquals($expectedPersonalVolume, $teamVolume->personal_volume);
            
            // Team volume should be >= personal volume
            $this->assertGreaterThanOrEqual($teamVolume->personal_volume, $teamVolume->team_volume);
        }
    }

    private function verifyPerformanceBonuses(array $network): void
    {
        // Check that Silver tier users have performance bonuses calculated
        $silverUsers = collect($network['users'])->filter(function ($user) {
            return $user->membershipTier && $user->membershipTier->name === 'Silver';
        });

        foreach ($silverUsers as $user) {
            $teamVolume = TeamVolume::where('user_id', $user->id)->first();
            if ($teamVolume && $teamVolume->team_volume > 0) {
                $this->assertDatabaseHas('performance_bonuses', [
                    'user_id' => $user->id,
                    'status' => 'eligible'
                ]);
            }
        }
    }

    private function verifyUserFieldUpdates(array $network): void
    {
        foreach ($network['users'] as $user) {
            $user->refresh();
            $this->assertNotNull($user->current_team_volume);
            $this->assertNotNull($user->current_personal_volume);
            $this->assertNotNull($user->current_team_depth);
            $this->assertNotNull($user->active_referrals_count);
        }
    }

    private function introduceDataInconsistencies(array $network): void
    {
        $user = $network['users'][0];
        
        // Create team volume where team < personal (invalid)
        TeamVolume::where('user_id', $user->id)->update([
            'team_volume' => 100,
            'personal_volume' => 1000 // team < personal is invalid
        ]);

        // Create user with referrer but no network path
        User::factory()->create([
            'referrer_id' => $user->id,
            'network_path' => null // Missing network path
        ]);
    }

    private function createMyGrowNetTiers(): void
    {
        $tiers = [
            [
                'name' => 'Bronze',
                'minimum_investment' => 0,
                'monthly_fee' => 150,
                'monthly_share' => 50,
                'monthly_team_volume_bonus_rate' => 0,
                'fixed_profit_rate' => 0,
                'direct_referral_rate' => 12,
                'level2_referral_rate' => 6,
                'level3_referral_rate' => 4,
                'order' => 1,
                'is_active' => true
            ],
            [
                'name' => 'Silver',
                'minimum_investment' => 0,
                'monthly_fee' => 300,
                'monthly_share' => 150,
                'monthly_team_volume_bonus_rate' => 2,
                'fixed_profit_rate' => 0,
                'direct_referral_rate' => 12,
                'level2_referral_rate' => 6,
                'level3_referral_rate' => 4,
                'order' => 2,
                'is_active' => true
            ]
        ];

        foreach ($tiers as $tier) {
            InvestmentTier::create($tier);
        }
    }
}