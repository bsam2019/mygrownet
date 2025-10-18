<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\RetroactiveAssetAllocationService;
use App\Domain\Reward\Services\PhysicalRewardAllocationService;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\TeamVolume;
use App\Models\PhysicalReward;
use App\Models\PhysicalRewardAllocation;
use App\Models\ReferralCommission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RetroactiveAssetAllocationServiceTest extends TestCase
{
    use RefreshDatabase;

    private RetroactiveAssetAllocationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $allocationService = $this->createMock(PhysicalRewardAllocationService::class);
        $this->service = new RetroactiveAssetAllocationService($allocationService);
        
        $this->createTestTiers();
        $this->createTestAssets();
    }

    public function test_identifies_eligible_users_correctly()
    {
        // Create users with different eligibility scenarios
        $silverTier = InvestmentTier::where('name', 'Silver')->first();
        $goldTier = InvestmentTier::where('name', 'Gold')->first();

        // Eligible Silver user
        $eligibleUser = User::factory()->create([
            'current_investment_tier_id' => $silverTier->id,
            'tier_upgraded_at' => now()->subMonths(4), // 4 months at tier
            'tier_history' => json_encode([
                ['tier_id' => $silverTier->id, 'date' => now()->subMonths(4)->toDateTimeString(), 'reason' => 'upgrade']
            ])
        ]);

        TeamVolume::create([
            'user_id' => $eligibleUser->id,
            'personal_volume' => 5000,
            'team_volume' => 20000, // Exceeds Silver requirement of 15000
            'active_referrals_count' => 5, // Exceeds Silver requirement of 3
            'team_depth' => 2,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth()
        ]);

        // Ineligible user (insufficient team volume)
        $ineligibleUser = User::factory()->create([
            'current_investment_tier_id' => $goldTier->id,
            'tier_upgraded_at' => now()->subMonths(8)
        ]);

        TeamVolume::create([
            'user_id' => $ineligibleUser->id,
            'personal_volume' => 2000,
            'team_volume' => 30000, // Below Gold requirement of 50000
            'active_referrals_count' => 15,
            'team_depth' => 3,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth()
        ]);

        $eligibleUsers = $this->service->identifyEligibleUsers();

        $this->assertCount(1, $eligibleUsers);
        $this->assertEquals($eligibleUser->id, $eligibleUsers[0]['user']->id);
        $this->assertTrue($eligibleUsers[0]['eligibility']['eligible']);
    }

    public function test_evaluates_user_eligibility_based_on_criteria()
    {
        $silverTier = InvestmentTier::where('name', 'Silver')->first();
        
        $user = User::factory()->create([
            'current_investment_tier_id' => $silverTier->id,
            'tier_upgraded_at' => now()->subMonths(4)
        ]);

        TeamVolume::create([
            'user_id' => $user->id,
            'team_volume' => 20000,
            'active_referrals_count' => 5,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth()
        ]);

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('evaluateUserEligibility');
        $method->setAccessible(true);

        $eligibility = $method->invoke($this->service, $user, $silverTier);

        $this->assertTrue($eligibility['eligible']);
        $this->assertTrue($eligibility['meets_months']);
        $this->assertTrue($eligibility['meets_volume']);
        $this->assertTrue($eligibility['meets_referrals']);
        $this->assertEquals(4, $eligibility['months_at_tier']);
    }

    public function test_calculates_historical_performance_correctly()
    {
        $user = User::factory()->create();

        // Create historical team volumes
        for ($i = 0; $i < 6; $i++) {
            TeamVolume::create([
                'user_id' => $user->id,
                'team_volume' => 10000 + ($i * 2000),
                'active_referrals_count' => 3 + $i,
                'period_start' => now()->subMonths($i)->startOfMonth(),
                'period_end' => now()->subMonths($i)->endOfMonth()
            ]);
        }

        // Create commission history
        ReferralCommission::factory()->create([
            'referrer_id' => $user->id,
            'amount' => 5000,
            'status' => 'paid'
        ]);

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('calculateHistoricalPerformance');
        $method->setAccessible(true);

        $performance = $method->invoke($this->service, $user);

        $this->assertArrayHasKey('avg_team_volume', $performance);
        $this->assertArrayHasKey('max_team_volume', $performance);
        $this->assertArrayHasKey('consistent_months', $performance);
        $this->assertArrayHasKey('total_commissions', $performance);
        $this->assertArrayHasKey('performance_score', $performance);

        $this->assertEquals(6, $performance['consistent_months']);
        $this->assertEquals(5000, $performance['total_commissions']);
        $this->assertGreaterThan(0, $performance['performance_score']);
    }

    public function test_allocates_assets_to_qualified_users()
    {
        $silverTier = InvestmentTier::where('name', 'Silver')->first();
        
        $user = User::factory()->create([
            'current_investment_tier_id' => $silverTier->id,
            'tier_upgraded_at' => now()->subMonths(4)
        ]);

        TeamVolume::create([
            'user_id' => $user->id,
            'team_volume' => 20000,
            'active_referrals_count' => 5,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth()
        ]);

        $results = $this->service->allocateRetroactiveAssets();

        $this->assertEquals(1, $results['users_evaluated']);
        $this->assertEquals(1, $results['users_qualified']);
        $this->assertEquals(1, $results['assets_allocated']);
        $this->assertEquals(1, $results['maintenance_schedules_created']);
        $this->assertEmpty($results['errors']);

        // Verify allocation was created
        $this->assertDatabaseHas('physical_reward_allocations', [
            'user_id' => $user->id,
            'tier_id' => $silverTier->id,
            'allocation_status' => 'allocated'
        ]);

        // Verify maintenance schedule was created
        $allocation = PhysicalRewardAllocation::where('user_id', $user->id)->first();
        $this->assertDatabaseHas('asset_maintenance_schedules', [
            'allocation_id' => $allocation->id,
            'milestone_type' => 'performance_review'
        ]);
    }

    public function test_does_not_allocate_duplicate_assets()
    {
        $silverTier = InvestmentTier::where('name', 'Silver')->first();
        $asset = PhysicalReward::where('type', 'SMARTPHONE')->first();
        
        $user = User::factory()->create([
            'current_investment_tier_id' => $silverTier->id,
            'tier_upgraded_at' => now()->subMonths(4)
        ]);

        TeamVolume::create([
            'user_id' => $user->id,
            'team_volume' => 20000,
            'active_referrals_count' => 5,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth()
        ]);

        // Create existing allocation
        PhysicalRewardAllocation::create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'tier_id' => $silverTier->id,
            'allocation_status' => 'allocated',
            'maintenance_status' => 'pending',
            'allocated_at' => now()
        ]);

        $results = $this->service->allocateRetroactiveAssets();

        $this->assertEquals(1, $results['users_evaluated']);
        $this->assertEquals(0, $results['users_qualified']); // Should not qualify due to existing allocation
        $this->assertEquals(0, $results['assets_allocated']);
    }

    public function test_dry_run_mode_makes_no_changes()
    {
        $silverTier = InvestmentTier::where('name', 'Silver')->first();
        
        $user = User::factory()->create([
            'current_investment_tier_id' => $silverTier->id,
            'tier_upgraded_at' => now()->subMonths(4)
        ]);

        TeamVolume::create([
            'user_id' => $user->id,
            'team_volume' => 20000,
            'active_referrals_count' => 5,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth()
        ]);

        // Count records before dry run
        $allocationsBefore = PhysicalRewardAllocation::count();
        $schedulesBefore = DB::table('asset_maintenance_schedules')->count();

        // Run in dry-run mode
        $results = $this->service->allocateRetroactiveAssets(true);

        // Verify no records were created
        $this->assertEquals($allocationsBefore, PhysicalRewardAllocation::count());
        $this->assertEquals($schedulesBefore, DB::table('asset_maintenance_schedules')->count());

        // But results should show what would have been done
        $this->assertEquals(1, $results['users_evaluated']);
        $this->assertEquals(1, $results['users_qualified']);
    }

    public function test_validates_asset_allocations()
    {
        // Create valid allocation
        $silverTier = InvestmentTier::where('name', 'Silver')->first();
        $asset = PhysicalReward::where('type', 'SMARTPHONE')->first();
        
        $user = User::factory()->create([
            'current_investment_tier_id' => $silverTier->id,
            'current_team_volume' => 20000
        ]);

        PhysicalRewardAllocation::create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'tier_id' => $silverTier->id,
            'allocation_status' => 'allocated',
            'maintenance_status' => 'pending',
            'maintenance_due_at' => now()->addMonths(12),
            'allocated_at' => now()
        ]);

        $validation = $this->service->validateAssetAllocations();

        $this->assertTrue($validation['is_valid']);
        $this->assertEmpty($validation['issues']);
    }

    public function test_detects_validation_issues()
    {
        $silverTier = InvestmentTier::where('name', 'Silver')->first();
        $asset = PhysicalReward::where('type', 'SMARTPHONE')->first();

        // Create user with no team volume (invalid)
        $user = User::factory()->create([
            'current_investment_tier_id' => $silverTier->id,
            'current_team_volume' => 0
        ]);

        PhysicalRewardAllocation::create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'tier_id' => $silverTier->id,
            'allocation_status' => 'allocated',
            'maintenance_status' => 'pending',
            'maintenance_due_at' => now()->subDays(30), // Overdue
            'allocated_at' => now()->subMonths(2)
        ]);

        $validation = $this->service->validateAssetAllocations();

        $this->assertFalse($validation['is_valid']);
        $this->assertNotEmpty($validation['issues']);
        $this->assertStringContains('no team volume', implode(' ', $validation['issues']));
        $this->assertStringContains('overdue maintenance', implode(' ', $validation['issues']));
    }

    public function test_gets_asset_allocation_statistics()
    {
        $silverTier = InvestmentTier::where('name', 'Silver')->first();
        $asset = PhysicalReward::where('type', 'SMARTPHONE')->first();
        
        $user = User::factory()->create(['current_investment_tier_id' => $silverTier->id]);

        PhysicalRewardAllocation::create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'tier_id' => $silverTier->id,
            'allocation_status' => 'allocated',
            'maintenance_status' => 'pending',
            'allocated_at' => now()
        ]);

        $stats = $this->service->getAssetAllocationStatistics();

        $this->assertArrayHasKey('total_users', $stats);
        $this->assertArrayHasKey('high_tier_users', $stats);
        $this->assertArrayHasKey('users_with_allocations', $stats);
        $this->assertArrayHasKey('total_allocations', $stats);
        $this->assertArrayHasKey('allocations_by_tier', $stats);
        $this->assertArrayHasKey('allocations_by_status', $stats);

        $this->assertEquals(1, $stats['users_with_allocations']);
        $this->assertEquals(1, $stats['total_allocations']);
        $this->assertEquals(1, $stats['allocations_by_tier']['Silver']);
        $this->assertEquals(1, $stats['allocations_by_status']['allocated']);
    }

    public function test_processes_maintenance_schedules()
    {
        $silverTier = InvestmentTier::where('name', 'Silver')->first();
        $asset = PhysicalReward::where('type', 'SMARTPHONE')->first();
        
        $user = User::factory()->create([
            'current_investment_tier_id' => $silverTier->id,
            'current_team_volume' => 20000
        ]);

        $allocation = PhysicalRewardAllocation::create([
            'user_id' => $user->id,
            'physical_reward_id' => $asset->id,
            'tier_id' => $silverTier->id,
            'team_volume_at_allocation' => 15000,
            'allocation_status' => 'allocated',
            'maintenance_status' => 'pending',
            'allocated_at' => now()->subMonths(3)
        ]);

        // Create overdue maintenance item
        DB::table('asset_maintenance_schedules')->insert([
            'allocation_id' => $allocation->id,
            'milestone_type' => 'performance_review',
            'due_date' => now()->subDays(1), // Overdue
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        TeamVolume::create([
            'user_id' => $user->id,
            'team_volume' => 20000, // Above threshold
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth()
        ]);

        $results = $this->service->processMaintenanceSchedules();

        $this->assertEquals(1, $results['processed']);
        $this->assertEquals(1, $results['completed']);

        // Verify maintenance item was marked as completed
        $this->assertDatabaseHas('asset_maintenance_schedules', [
            'allocation_id' => $allocation->id,
            'status' => 'completed'
        ]);

        // Verify allocation status was updated
        $allocation->refresh();
        $this->assertEquals('on_track', $allocation->maintenance_status);
    }

    private function createTestTiers(): void
    {
        $tiers = [
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
            ],
            [
                'name' => 'Gold',
                'minimum_investment' => 0,
                'monthly_fee' => 500,
                'monthly_share' => 300,
                'monthly_team_volume_bonus_rate' => 5,
                'fixed_profit_rate' => 0,
                'direct_referral_rate' => 12,
                'level2_referral_rate' => 6,
                'level3_referral_rate' => 4,
                'order' => 3,
                'is_active' => true
            ]
        ];

        foreach ($tiers as $tier) {
            InvestmentTier::create($tier);
        }
    }

    private function createTestAssets(): void
    {
        $assets = [
            [
                'type' => 'SMARTPHONE',
                'model' => 'Test Smartphone',
                'value' => 3000,
                'status' => 'available'
            ],
            [
                'type' => 'TABLET',
                'model' => 'Test Tablet',
                'value' => 4000,
                'status' => 'available'
            ],
            [
                'type' => 'MOTORBIKE',
                'model' => 'Test Motorbike',
                'value' => 12000,
                'status' => 'available'
            ]
        ];

        foreach ($assets as $asset) {
            PhysicalReward::create($asset);
        }
    }
}