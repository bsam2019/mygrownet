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

class PhysicalRewardAllocationServiceSimpleTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_can_be_instantiated()
    {
        $tierQualificationService = $this->createMock(TierQualificationTrackingService::class);
        $service = new PhysicalRewardAllocationService($tierQualificationService);
        
        $this->assertInstanceOf(PhysicalRewardAllocationService::class, $service);
    }

    public function test_can_check_basic_eligibility()
    {
        $tierQualificationService = $this->createMock(TierQualificationTrackingService::class);
        $tierQualificationService->method('getConsecutiveMonthsAtTier')->willReturn(6);
        
        $service = new PhysicalRewardAllocationService($tierQualificationService);

        // Create minimal test data
        $tier = new InvestmentTier([
            'name' => 'Gold',
            'monthly_fee' => 500
        ]);
        $tier->id = 1;

        $user = new User([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);
        $user->id = 1;
        $user->setRelation('membershipTier', $tier);

        $teamVolume = new TeamVolume([
            'user_id' => 1,
            'team_volume' => 50000,
            'active_referrals_count' => 10,
            'team_depth' => 3
        ]);
        $user->setRelation('teamVolume', collect([$teamVolume]));

        $reward = new PhysicalReward([
            'name' => 'Test Motorbike',
            'required_membership_tiers' => ['Gold'],
            'required_referrals' => 10,
            'required_team_volume' => 50000,
            'required_team_depth' => 3,
            'required_subscription_amount' => 500,
            'required_sustained_months' => 6,
            'is_active' => true
        ]);

        // Mock the getCurrentTeamVolume method
        $user->setRelation('currentTeamVolume', $teamVolume);

        $isEligible = $service->isUserEligibleForReward($user, $reward);
        
        $this->assertTrue($isEligible);
    }

    public function test_user_not_eligible_with_insufficient_team_volume()
    {
        $tierQualificationService = $this->createMock(TierQualificationTrackingService::class);
        $service = new PhysicalRewardAllocationService($tierQualificationService);

        $tier = new InvestmentTier(['name' => 'Gold', 'monthly_fee' => 500]);
        $tier->id = 1;

        $user = new User(['name' => 'Test User', 'email' => 'test@example.com']);
        $user->id = 1;
        $user->setRelation('membershipTier', $tier);

        $teamVolume = new TeamVolume([
            'user_id' => 1,
            'team_volume' => 30000, // Below requirement
            'active_referrals_count' => 10,
            'team_depth' => 3
        ]);
        $user->setRelation('currentTeamVolume', $teamVolume);

        $reward = new PhysicalReward([
            'required_membership_tiers' => ['Gold'],
            'required_referrals' => 10,
            'required_team_volume' => 50000,
            'required_team_depth' => 3,
            'required_subscription_amount' => 500,
            'is_active' => true
        ]);

        $isEligible = $service->isUserEligibleForReward($user, $reward);
        
        $this->assertFalse($isEligible);
    }

    public function test_user_not_eligible_with_wrong_tier()
    {
        $tierQualificationService = $this->createMock(TierQualificationTrackingService::class);
        $service = new PhysicalRewardAllocationService($tierQualificationService);

        $tier = new InvestmentTier(['name' => 'Silver', 'monthly_fee' => 300]);
        $tier->id = 1;

        $user = new User(['name' => 'Test User', 'email' => 'test@example.com']);
        $user->id = 1;
        $user->setRelation('membershipTier', $tier);

        $teamVolume = new TeamVolume([
            'user_id' => 1,
            'team_volume' => 50000,
            'active_referrals_count' => 10,
            'team_depth' => 3
        ]);
        $user->setRelation('currentTeamVolume', $teamVolume);

        $reward = new PhysicalReward([
            'required_membership_tiers' => ['Gold'], // Requires Gold, user has Silver
            'required_referrals' => 10,
            'required_team_volume' => 50000,
            'required_team_depth' => 3,
            'required_subscription_amount' => 500,
            'is_active' => true
        ]);

        $isEligible = $service->isUserEligibleForReward($user, $reward);
        
        $this->assertFalse($isEligible);
    }
}