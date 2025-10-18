<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PhysicalRewardAllocationService;
use App\Services\TierQualificationTrackingService;
use App\Models\User;
use App\Models\PhysicalReward;
use App\Models\PhysicalRewardAllocation;
use App\Models\InvestmentTier;
use App\Models\TeamVolume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class PhysicalRewardAllocationServiceTest extends TestCase
{
    use RefreshDatabase;

    private PhysicalRewardAllocationService $service;
    private User $user;
    private InvestmentTier $tier;
    private PhysicalReward $reward;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the tier qualification service to avoid complex dependencies
        $tierQualificationService = $this->createMock(TierQualificationTrackingService::class);
        $tierQualificationService->method('getConsecutiveMonthsAtTier')->willReturn(6);
        
        $this->service = new PhysicalRewardAllocationService($tierQualificationService);

        // Create minimal test data without excessive relationships
        $this->tier = InvestmentTier::create([
            'name' => 'Gold',
            'monthly_fee' => 500,
            'minimum_investment' => 5000,
            'order' => 3,
            'is_active' => true
        ]);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'current_investment_tier_id' => $this->tier->id
        ]);

        // Create team volume directly without factory
        TeamVolume::create([
            'user_id' => $this->user->id,
            'team_volume' => 50000,
            'personal_volume' => 5000,
            'active_referrals_count' => 10,
            'team_depth' => 3,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString()
        ]);

        $this->reward = PhysicalReward::create([
            'name' => 'Test Motorbike',
            'category' => 'vehicle',
            'estimated_value' => 12000,
            'required_membership_tiers' => ['Gold'],
            'required_referrals' => 10,
            'required_team_volume' => 50000,
            'required_team_depth' => 3,
            'required_subscription_amount' => 500,
            'required_sustained_months' => 6,
            'available_quantity' => 10,
            'allocated_quantity' => 0,
            'maintenance_period_months' => 12,
            'requires_performance_maintenance' => true,
            'income_generating' => true,
            'estimated_monthly_income' => 800,
            'is_active' => true
        ]);
    }

    public function test_can_check_user_eligibility_for_reward()
    {
        $isEligible = $this->service->isUserEligibleForReward($this->user, $this->reward);
        
        $this->assertTrue($isEligible);
    }

    public function test_user_not_eligible_if_team_volume_insufficient()
    {
        // Update team volume to be below requirement
        $this->user->teamVolume->update(['team_volume' => 30000]);
        
        $isEligible = $this->service->isUserEligibleForReward($this->user, $this->reward);
        
        $this->assertFalse($isEligible);
    }

    public function test_user_not_eligible_if_referrals_insufficient()
    {
        // Update referrals to be below requirement
        $this->user->teamVolume->update(['active_referrals_count' => 5]);
        
        $isEligible = $this->service->isUserEligibleForReward($this->user, $this->reward);
        
        $this->assertFalse($isEligible);
    }

    public function test_user_not_eligible_if_wrong_tier()
    {
        // Update reward to require different tier
        $this->reward->update(['required_membership_tiers' => ['Diamond']]);
        
        $isEligible = $this->service->isUserEligibleForReward($this->user, $this->reward);
        
        $this->assertFalse($isEligible);
    }

    public function test_can_allocate_reward_to_eligible_user()
    {
        $allocation = $this->service->allocateRewardToUser($this->user, $this->reward);
        
        $this->assertInstanceOf(PhysicalRewardAllocation::class, $allocation);
        $this->assertEquals($this->user->id, $allocation->user_id);
        $this->assertEquals($this->reward->id, $allocation->physical_reward_id);
        $this->assertEquals('allocated', $allocation->status);
        $this->assertEquals(50000, $allocation->team_volume_at_allocation);
        $this->assertEquals(10, $allocation->active_referrals_at_allocation);
        $this->assertEquals(3, $allocation->team_depth_at_allocation);
    }

    public function test_cannot_allocate_reward_to_ineligible_user()
    {
        // Make user ineligible
        $this->user->teamVolume->update(['team_volume' => 30000]);
        
        $allocation = $this->service->allocateRewardToUser($this->user, $this->reward);
        
        $this->assertNull($allocation);
    }

    public function test_cannot_allocate_unavailable_reward()
    {
        // Make reward unavailable
        $this->reward->update(['allocated_quantity' => 10]); // Same as available_quantity
        
        $allocation = $this->service->allocateRewardToUser($this->user, $this->reward);
        
        $this->assertNull($allocation);
    }

    public function test_can_check_and_allocate_rewards_for_user()
    {
        $allocatedRewards = $this->service->checkAndAllocateRewards($this->user);
        
        $this->assertCount(1, $allocatedRewards);
        $this->assertEquals($this->reward->id, $allocatedRewards->first()->physical_reward_id);
    }

    public function test_does_not_allocate_duplicate_rewards()
    {
        // First allocation
        $this->service->allocateRewardToUser($this->user, $this->reward);
        
        // Second attempt should not allocate
        $allocatedRewards = $this->service->checkAndAllocateRewards($this->user);
        
        $this->assertCount(0, $allocatedRewards);
    }

    public function test_can_get_available_rewards_for_user()
    {
        $availableRewards = $this->service->getAvailableRewardsForUser($this->user);
        
        $this->assertCount(1, $availableRewards);
        $this->assertEquals($this->reward->id, $availableRewards->first()->id);
    }

    public function test_can_record_asset_income()
    {
        $allocation = $this->service->allocateRewardToUser($this->user, $this->reward);
        $allocation->markAsDelivered();
        
        $result = $this->service->recordAssetIncome($allocation, 800.0, 'taxi_service');
        
        $this->assertTrue($result);
        $allocation->refresh();
        $this->assertEquals(800.0, $allocation->total_income_generated);
    }

    public function test_cannot_record_income_for_non_income_generating_reward()
    {
        $this->reward->update(['income_generating' => false]);
        $allocation = $this->service->allocateRewardToUser($this->user, $this->reward);
        
        $result = $this->service->recordAssetIncome($allocation, 800.0);
        
        $this->assertFalse($result);
    }

    public function test_can_get_user_reward_progress()
    {
        $allocation = $this->service->allocateRewardToUser($this->user, $this->reward);
        
        $progress = $this->service->getUserRewardProgress($this->user);
        
        $this->assertEquals('Gold', $progress['current_tier']);
        $this->assertEquals(50000, $progress['team_volume']);
        $this->assertEquals(10, $progress['active_referrals']);
        $this->assertEquals(3, $progress['team_depth']);
        $this->assertCount(1, $progress['allocated_rewards']);
        $this->assertCount(0, $progress['available_rewards']); // Already allocated
    }

    public function test_can_get_asset_income_report()
    {
        $allocation = $this->service->allocateRewardToUser($this->user, $this->reward);
        $allocation->markAsDelivered();
        $this->service->recordAssetIncome($allocation, 800.0);
        $this->service->recordAssetIncome($allocation, 750.0);
        
        $report = $this->service->getAssetIncomeReport($this->user);
        
        $this->assertEquals(1550.0, $report['total_income_generated']);
        $this->assertEquals(1, $report['active_assets']);
        $this->assertCount(1, $report['asset_details']);
        $this->assertEquals('Test Motorbike', $report['asset_details'][0]['reward_name']);
        $this->assertEquals(1550.0, $report['asset_details'][0]['total_income']);
    }

    public function test_maintenance_check_marks_compliant_user()
    {
        $allocation = PhysicalRewardAllocation::create([
            'user_id' => $this->user->id,
            'physical_reward_id' => $this->reward->id,
            'tier_id' => $this->tier->id,
            'team_volume_at_allocation' => 50000,
            'active_referrals_at_allocation' => 10,
            'team_depth_at_allocation' => 3,
            'status' => 'delivered',
            'maintenance_compliant' => true,
            'last_maintenance_check' => now()->subMonths(2),
            'allocated_at' => now()->subMonths(3)
        ]);

        $results = $this->service->processMaintenanceChecks();

        $this->assertGreaterThan(0, $results['checked']);
        $this->assertGreaterThan(0, $results['compliant']);
        
        $allocation->refresh();
        $this->assertTrue($allocation->maintenance_compliant);
    }

    public function test_maintenance_check_handles_violations()
    {
        // Make user ineligible (insufficient team volume)
        TeamVolume::where('user_id', $this->user->id)->update(['team_volume' => 30000]);
        
        $allocation = PhysicalRewardAllocation::create([
            'user_id' => $this->user->id,
            'physical_reward_id' => $this->reward->id,
            'tier_id' => $this->tier->id,
            'team_volume_at_allocation' => 50000,
            'active_referrals_at_allocation' => 10,
            'team_depth_at_allocation' => 3,
            'status' => 'delivered',
            'maintenance_compliant' => true,
            'last_maintenance_check' => now()->subMonths(2),
            'allocated_at' => now()->subMonths(3)
        ]);

        $results = $this->service->processMaintenanceChecks();

        $this->assertGreaterThan(0, $results['violations']);
        
        $allocation->refresh();
        $this->assertFalse($allocation->maintenance_compliant);
    }
}