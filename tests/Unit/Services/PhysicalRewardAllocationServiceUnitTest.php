<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\PhysicalRewardAllocationService;
use App\Services\TierQualificationTrackingService;
use App\Models\User;
use App\Models\PhysicalReward;
use App\Models\InvestmentTier;
use App\Models\TeamVolume;

class PhysicalRewardAllocationServiceUnitTest extends TestCase
{
    public function test_service_instantiation()
    {
        $tierQualificationService = $this->createMock(TierQualificationTrackingService::class);
        $service = new PhysicalRewardAllocationService($tierQualificationService);
        
        $this->assertInstanceOf(PhysicalRewardAllocationService::class, $service);
    }

    public function test_eligibility_check_with_mocked_data()
    {
        $tierQualificationService = $this->createMock(TierQualificationTrackingService::class);
        $tierQualificationService->method('getConsecutiveMonthsAtTier')->willReturn(6);
        
        $service = new PhysicalRewardAllocationService($tierQualificationService);

        // Mock user
        $user = $this->createMock(User::class);
        
        // Mock tier
        $tier = $this->createMock(InvestmentTier::class);
        $tier->method('__get')->with('name')->willReturn('Gold');
        $tier->method('__get')->with('monthly_fee')->willReturn(500);
        
        $user->method('__get')->with('membershipTier')->willReturn($tier);

        // Mock team volume
        $teamVolume = $this->createMock(TeamVolume::class);
        $teamVolume->method('__get')->with('team_volume')->willReturn(50000);
        $teamVolume->method('__get')->with('active_referrals_count')->willReturn(10);
        $teamVolume->method('__get')->with('team_depth')->willReturn(3);
        
        $user->method('getCurrentTeamVolume')->willReturn($teamVolume);

        // Mock reward
        $reward = $this->createMock(PhysicalReward::class);
        $reward->method('__get')->with('required_membership_tiers')->willReturn(['Gold']);
        $reward->method('__get')->with('required_referrals')->willReturn(10);
        $reward->method('__get')->with('required_team_volume')->willReturn(50000);
        $reward->method('__get')->with('required_team_depth')->willReturn(3);
        $reward->method('__get')->with('required_subscription_amount')->willReturn(500);
        $reward->method('__get')->with('required_sustained_months')->willReturn(6);

        $isEligible = $service->isUserEligibleForReward($user, $reward);
        
        $this->assertTrue($isEligible);
    }

    public function test_eligibility_fails_with_insufficient_team_volume()
    {
        $tierQualificationService = $this->createMock(TierQualificationTrackingService::class);
        $service = new PhysicalRewardAllocationService($tierQualificationService);

        // Mock user
        $user = $this->createMock(User::class);
        
        // Mock tier
        $tier = $this->createMock(InvestmentTier::class);
        $tier->method('__get')->with('name')->willReturn('Gold');
        $tier->method('__get')->with('monthly_fee')->willReturn(500);
        
        $user->method('__get')->with('membershipTier')->willReturn($tier);

        // Mock team volume with insufficient volume
        $teamVolume = $this->createMock(TeamVolume::class);
        $teamVolume->method('__get')->with('team_volume')->willReturn(30000); // Below requirement
        $teamVolume->method('__get')->with('active_referrals_count')->willReturn(10);
        $teamVolume->method('__get')->with('team_depth')->willReturn(3);
        
        $user->method('getCurrentTeamVolume')->willReturn($teamVolume);

        // Mock reward
        $reward = $this->createMock(PhysicalReward::class);
        $reward->method('__get')->with('required_membership_tiers')->willReturn(['Gold']);
        $reward->method('__get')->with('required_referrals')->willReturn(10);
        $reward->method('__get')->with('required_team_volume')->willReturn(50000);
        $reward->method('__get')->with('required_team_depth')->willReturn(3);
        $reward->method('__get')->with('required_subscription_amount')->willReturn(500);

        $isEligible = $service->isUserEligibleForReward($user, $reward);
        
        $this->assertFalse($isEligible);
    }

    public function test_eligibility_fails_with_wrong_tier()
    {
        $tierQualificationService = $this->createMock(TierQualificationTrackingService::class);
        $service = new PhysicalRewardAllocationService($tierQualificationService);

        // Mock user with Silver tier
        $user = $this->createMock(User::class);
        
        // Mock tier
        $tier = $this->createMock(InvestmentTier::class);
        $tier->method('__get')->with('name')->willReturn('Silver');
        $tier->method('__get')->with('monthly_fee')->willReturn(300);
        
        $user->method('__get')->with('membershipTier')->willReturn($tier);

        // Mock team volume
        $teamVolume = $this->createMock(TeamVolume::class);
        $teamVolume->method('__get')->with('team_volume')->willReturn(50000);
        $teamVolume->method('__get')->with('active_referrals_count')->willReturn(10);
        $teamVolume->method('__get')->with('team_depth')->willReturn(3);
        
        $user->method('getCurrentTeamVolume')->willReturn($teamVolume);

        // Mock reward that requires Gold tier
        $reward = $this->createMock(PhysicalReward::class);
        $reward->method('__get')->with('required_membership_tiers')->willReturn(['Gold']);
        $reward->method('__get')->with('required_referrals')->willReturn(10);
        $reward->method('__get')->with('required_team_volume')->willReturn(50000);
        $reward->method('__get')->with('required_team_depth')->willReturn(3);
        $reward->method('__get')->with('required_subscription_amount')->willReturn(500);

        $isEligible = $service->isUserEligibleForReward($user, $reward);
        
        $this->assertFalse($isEligible);
    }
}