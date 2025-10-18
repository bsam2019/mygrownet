<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\TeamVolumeInitializationService;
use App\Models\User;
use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\TeamVolume;
use App\Models\UserNetwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class TeamVolumeInitializationServiceTest extends TestCase
{
    use RefreshDatabase;

    private TeamVolumeInitializationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TeamVolumeInitializationService();
        
        // Create test tiers
        $this->createTestTiers();
    }

    public function test_builds_network_relationships_correctly()
    {
        // Create referral chain: root -> user1 -> user2 -> user3
        $root = User::factory()->create(['referrer_id' => null]);
        $user1 = User::factory()->create(['referrer_id' => $root->id]);
        $user2 = User::factory()->create(['referrer_id' => $user1->id]);
        $user3 = User::factory()->create(['referrer_id' => $user2->id]);

        $results = $this->service->buildNetworkRelationships();

        // Verify network paths are created
        $root->refresh();
        $user1->refresh();
        $user2->refresh();
        $user3->refresh();

        $this->assertEquals((string) $root->id, $root->network_path);
        $this->assertEquals(0, $root->network_level);

        $this->assertEquals($root->id . '.' . $user1->id, $user1->network_path);
        $this->assertEquals(1, $user1->network_level);

        $this->assertEquals($root->id . '.' . $user1->id . '.' . $user2->id, $user2->network_path);
        $this->assertEquals(2, $user2->network_level);

        $this->assertEquals($root->id . '.' . $user1->id . '.' . $user2->id . '.' . $user3->id, $user3->network_path);
        $this->assertEquals(3, $user3->network_level);

        // Verify UserNetwork entries are created
        $this->assertDatabaseHas('user_networks', [
            'user_id' => $user1->id,
            'referrer_id' => $root->id,
            'level' => 1
        ]);

        $this->assertDatabaseHas('user_networks', [
            'user_id' => $user2->id,
            'referrer_id' => $root->id,
            'level' => 2
        ]);

        $this->assertDatabaseHas('user_networks', [
            'user_id' => $user2->id,
            'referrer_id' => $user1->id,
            'level' => 1
        ]);

        $this->assertEquals(4, $results['paths_updated']);
    }

    public function test_calculates_historical_team_volumes_correctly()
    {
        $bronzeTier = InvestmentTier::where('name', 'Bronze')->first();
        
        // Create referral network with investments
        $root = User::factory()->create([
            'referrer_id' => null,
            'network_path' => '1',
            'network_level' => 0,
            'current_investment_tier_id' => $bronzeTier->id
        ]);
        
        $user1 = User::factory()->create([
            'referrer_id' => $root->id,
            'network_path' => '1.2',
            'network_level' => 1,
            'current_investment_tier_id' => $bronzeTier->id
        ]);
        
        $user2 = User::factory()->create([
            'referrer_id' => $user1->id,
            'network_path' => '1.2.3',
            'network_level' => 2,
            'current_investment_tier_id' => $bronzeTier->id
        ]);

        // Create investments
        Investment::factory()->create([
            'user_id' => $root->id,
            'amount' => 1000,
            'status' => 'active'
        ]);

        Investment::factory()->create([
            'user_id' => $user1->id,
            'amount' => 500,
            'status' => 'active'
        ]);

        Investment::factory()->create([
            'user_id' => $user2->id,
            'amount' => 300,
            'status' => 'active'
        ]);

        $results = $this->service->calculateHistoricalTeamVolumes();

        // Verify team volumes are calculated correctly
        $rootVolume = TeamVolume::where('user_id', $root->id)->first();
        $this->assertNotNull($rootVolume);
        $this->assertEquals(1000, $rootVolume->personal_volume);
        $this->assertEquals(1800, $rootVolume->team_volume); // 1000 + 500 + 300
        $this->assertEquals(2, $rootVolume->team_depth);
        $this->assertEquals(1, $rootVolume->active_referrals_count);

        $user1Volume = TeamVolume::where('user_id', $user1->id)->first();
        $this->assertNotNull($user1Volume);
        $this->assertEquals(500, $user1Volume->personal_volume);
        $this->assertEquals(800, $user1Volume->team_volume); // 500 + 300
        $this->assertEquals(1, $user1Volume->team_depth);
        $this->assertEquals(1, $user1Volume->active_referrals_count);

        $user2Volume = TeamVolume::where('user_id', $user2->id)->first();
        $this->assertNotNull($user2Volume);
        $this->assertEquals(300, $user2Volume->personal_volume);
        $this->assertEquals(300, $user2Volume->team_volume); // Only personal volume
        $this->assertEquals(0, $user2Volume->team_depth);
        $this->assertEquals(0, $user2Volume->active_referrals_count);

        $this->assertEquals(3, $results['users_processed']);
        $this->assertEquals(3, $results['volumes_created']);
    }

    public function test_initializes_performance_bonus_eligibility()
    {
        $silverTier = InvestmentTier::where('name', 'Silver')->first();
        
        $user = User::factory()->create([
            'current_investment_tier_id' => $silverTier->id
        ]);

        // Create team volume record
        TeamVolume::create([
            'user_id' => $user->id,
            'personal_volume' => 1000,
            'team_volume' => 5000,
            'team_depth' => 2,
            'active_referrals_count' => 3,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth()
        ]);

        $results = $this->service->initializePerformanceBonusEligibility();

        // Verify performance bonus is calculated
        $this->assertDatabaseHas('performance_bonuses', [
            'user_id' => $user->id,
            'team_volume' => 5000,
            'bonus_rate' => $silverTier->monthly_team_volume_bonus_rate,
            'bonus_amount' => 5000 * ($silverTier->monthly_team_volume_bonus_rate / 100),
            'status' => 'eligible'
        ]);

        $this->assertEquals(1, $results['bonuses_calculated']);
    }

    public function test_full_initialization_workflow()
    {
        $bronzeTier = InvestmentTier::where('name', 'Bronze')->first();
        $silverTier = InvestmentTier::where('name', 'Silver')->first();

        // Create network structure
        $root = User::factory()->create([
            'referrer_id' => null,
            'current_investment_tier_id' => $silverTier->id
        ]);
        
        $user1 = User::factory()->create([
            'referrer_id' => $root->id,
            'current_investment_tier_id' => $bronzeTier->id
        ]);
        
        $user2 = User::factory()->create([
            'referrer_id' => $user1->id,
            'current_investment_tier_id' => $bronzeTier->id
        ]);

        // Create investments
        Investment::factory()->create([
            'user_id' => $root->id,
            'amount' => 2000,
            'status' => 'active'
        ]);

        Investment::factory()->create([
            'user_id' => $user1->id,
            'amount' => 1000,
            'status' => 'active'
        ]);

        Investment::factory()->create([
            'user_id' => $user2->id,
            'amount' => 500,
            'status' => 'active'
        ]);

        // Run full initialization
        $results = $this->service->initializeTeamVolumeTracking();

        // Verify all components are working
        $this->assertGreaterThan(0, $results['users_processed']);
        $this->assertGreaterThan(0, $results['team_volumes_created']);
        $this->assertGreaterThan(0, $results['network_paths_updated']);
        $this->assertEmpty($results['errors']);

        // Verify network paths
        $root->refresh();
        $user1->refresh();
        $user2->refresh();

        $this->assertNotNull($root->network_path);
        $this->assertNotNull($user1->network_path);
        $this->assertNotNull($user2->network_path);

        // Verify team volumes
        $this->assertDatabaseHas('team_volumes', ['user_id' => $root->id]);
        $this->assertDatabaseHas('team_volumes', ['user_id' => $user1->id]);
        $this->assertDatabaseHas('team_volumes', ['user_id' => $user2->id]);

        // Verify user fields are updated
        $this->assertNotNull($root->current_team_volume);
        $this->assertNotNull($user1->current_team_volume);
        $this->assertNotNull($user2->current_team_volume);
    }

    public function test_dry_run_mode_makes_no_changes()
    {
        $user = User::factory()->create(['referrer_id' => null]);
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'amount' => 1000,
            'status' => 'active'
        ]);

        // Count records before dry run
        $userNetworksBefore = UserNetwork::count();
        $teamVolumesBefore = TeamVolume::count();
        $performanceBonusesBefore = DB::table('performance_bonuses')->count();

        // Run in dry-run mode
        $results = $this->service->initializeTeamVolumeTracking(true);

        // Verify no records were created
        $this->assertEquals($userNetworksBefore, UserNetwork::count());
        $this->assertEquals($teamVolumesBefore, TeamVolume::count());
        $this->assertEquals($performanceBonusesBefore, DB::table('performance_bonuses')->count());

        // Verify user fields weren't updated
        $user->refresh();
        $this->assertNull($user->network_path);
        $this->assertNull($user->current_team_volume);
    }

    public function test_validates_team_volume_calculations()
    {
        // Create valid setup
        $user = User::factory()->create([
            'network_path' => '1',
            'network_level' => 0
        ]);

        Investment::factory()->create([
            'user_id' => $user->id,
            'amount' => 1000,
            'status' => 'active'
        ]);

        TeamVolume::create([
            'user_id' => $user->id,
            'personal_volume' => 1000,
            'team_volume' => 1000,
            'team_depth' => 0,
            'active_referrals_count' => 0,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth()
        ]);

        $validation = $this->service->validateTeamVolumeCalculations();

        $this->assertTrue($validation['is_valid']);
        $this->assertEmpty($validation['issues']);
    }

    public function test_detects_validation_issues()
    {
        // Create user with referrer but no network path
        $referrer = User::factory()->create();
        $user = User::factory()->create(['referrer_id' => $referrer->id]);

        // Create team volume where team < personal (invalid)
        TeamVolume::create([
            'user_id' => $user->id,
            'personal_volume' => 1000,
            'team_volume' => 500, // Invalid: team < personal
            'team_depth' => 0,
            'active_referrals_count' => 0,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth()
        ]);

        $validation = $this->service->validateTeamVolumeCalculations();

        $this->assertFalse($validation['is_valid']);
        $this->assertNotEmpty($validation['issues']);
    }

    public function test_recalculates_specific_users()
    {
        $user = User::factory()->create([
            'network_path' => '1',
            'network_level' => 0
        ]);

        Investment::factory()->create([
            'user_id' => $user->id,
            'amount' => 1500,
            'status' => 'active'
        ]);

        // Create initial team volume with wrong data
        TeamVolume::create([
            'user_id' => $user->id,
            'personal_volume' => 1000, // Wrong amount
            'team_volume' => 1000,
            'team_depth' => 0,
            'active_referrals_count' => 0,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth()
        ]);

        // Recalculate
        $results = $this->service->recalculateTeamVolumes([$user->id]);

        // Verify correction
        $teamVolume = TeamVolume::where('user_id', $user->id)->first();
        $this->assertEquals(1500, $teamVolume->personal_volume);
        $this->assertEquals(1500, $teamVolume->team_volume);
        $this->assertEquals(1, $results['recalculated']);
    }

    public function test_gets_team_volume_statistics()
    {
        // Create test data
        $user1 = User::factory()->create(['network_path' => '1']);
        $user2 = User::factory()->create(['network_path' => '2', 'active_referrals_count' => 2]);

        TeamVolume::create([
            'user_id' => $user1->id,
            'personal_volume' => 1000,
            'team_volume' => 1500,
            'team_depth' => 1,
            'active_referrals_count' => 1,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth()
        ]);

        TeamVolume::create([
            'user_id' => $user2->id,
            'personal_volume' => 2000,
            'team_volume' => 3000,
            'team_depth' => 2,
            'active_referrals_count' => 2,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth()
        ]);

        $stats = $this->service->getTeamVolumeStatistics();

        $this->assertArrayHasKey('total_users', $stats);
        $this->assertArrayHasKey('users_with_network_paths', $stats);
        $this->assertArrayHasKey('users_with_team_volumes', $stats);
        $this->assertArrayHasKey('total_team_volume', $stats);
        $this->assertArrayHasKey('average_team_volume', $stats);
        $this->assertArrayHasKey('users_with_active_referrals', $stats);

        $this->assertEquals(2, $stats['users_with_network_paths']);
        $this->assertEquals(2, $stats['users_with_team_volumes']);
        $this->assertEquals(4500, $stats['total_team_volume']);
        $this->assertEquals(1, $stats['users_with_active_referrals']);
    }

    private function createTestTiers(): void
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