<?php

namespace Tests\Unit\GrowNet;

use App\Domain\GrowNet\Entities\Member;
use App\Domain\GrowNet\Entities\Commission;
use App\Domain\GrowNet\Entities\Referral;
use App\Domain\GrowNet\Entities\TeamVolume;
use App\Domain\GrowNet\Entities\TierUpgrade;
use App\Domain\GrowNet\Entities\StarterKit;
use App\Domain\GrowNet\Entities\LoyaltyPoints;
use App\Domain\GrowNet\ValueObjects\MemberId;
use App\Domain\GrowNet\ValueObjects\Money;
use App\Domain\GrowNet\ValueObjects\CommissionLevel;
use App\Domain\GrowNet\ValueObjects\MembershipTier;
use App\Domain\GrowNet\ValueObjects\ReferralCode;
use App\Domain\GrowNet\ValueObjects\NetworkLevel;
use App\Domain\GrowNet\ValueObjects\SubscriptionStatus;
use App\Domain\GrowNet\ValueObjects\VerificationLevel;
use PHPUnit\Framework\TestCase;

class EntityTest extends TestCase
{
    // --- Member Entity ---

    public function test_member_create_returns_new_member(): void
    {
        $member = Member::create(userId: 1, referrerId: 5, referralCode: 'REFCODE');

        $this->assertEquals(0, $member->id()->value());
        $this->assertEquals(1, $member->userId());
        $this->assertEquals(5, $member->referrerId()?->value());
        $this->assertEquals('REFCODE', $member->referralCode()?->value());
        $this->assertEquals(0, $member->referralCount());
        $this->assertFalse($member->isActive());
        $this->assertFalse($member->hasActiveSubscription());
        $this->assertFalse($member->hasStarterKit());
    }

    public function test_member_create_without_referrer(): void
    {
        $member = Member::create(userId: 2);

        $this->assertNull($member->referrerId());
        $this->assertNull($member->referralCode());
    }

    public function test_member_remaining_withdrawal_limit(): void
    {
        $member = Member::reconstitute([
            'id' => 1,
            'user_id' => 1,
            'verification_level' => 'premium',
            'daily_withdrawal_used' => 5000,
        ]);

        $this->assertEquals(15000, $member->remainingDailyWithdrawalLimit());
    }

    public function test_member_remaining_withdrawal_limit_no_usage(): void
    {
        $member = Member::reconstitute([
            'id' => 1,
            'user_id' => 1,
            'verification_level' => 'basic',
        ]);

        $this->assertEquals(1000, $member->remainingDailyWithdrawalLimit());
    }

    public function test_member_remaining_withdrawal_limit_caps_at_zero(): void
    {
        $member = Member::reconstitute([
            'id' => 1,
            'user_id' => 1,
            'verification_level' => 'basic',
            'daily_withdrawal_used' => 2000,
        ]);

        $this->assertEquals(0, $member->remainingDailyWithdrawalLimit());
    }

    public function test_member_available_credit(): void
    {
        $member = Member::reconstitute([
            'id' => 1,
            'user_id' => 1,
            'loan_limit' => 10000,
            'loan_balance' => 3000,
        ]);

        $this->assertEquals(7000, $member->availableCredit()->amount());
    }

    public function test_member_available_credit_caps_at_zero(): void
    {
        $member = Member::reconstitute([
            'id' => 1,
            'user_id' => 1,
            'loan_limit' => 5000,
            'loan_balance' => 8000,
        ]);

        $this->assertEquals(0, $member->availableCredit()->amount());
    }

    public function test_member_has_active_subscription(): void
    {
        $activeMember = Member::reconstitute([
            'id' => 1, 'user_id' => 1,
            'subscription_status' => 'active',
        ]);
        $this->assertTrue($activeMember->hasActiveSubscription());

        $inactiveMember = Member::reconstitute([
            'id' => 2, 'user_id' => 2,
            'subscription_status' => 'inactive',
        ]);
        $this->assertFalse($inactiveMember->hasActiveSubscription());
    }

    public function test_member_reconstitute_restores_full_state(): void
    {
        $member = Member::reconstitute([
            'id' => 42,
            'user_id' => 10,
            'referrer_id' => 5,
            'referral_code' => 'MYCODE',
            'referral_count' => 7,
            'direct_referrals' => 3,
            'rank' => 'Gold Leader',
            'current_professional_level' => 'silver',
            'balance' => 5000.00,
            'total_earnings' => 10000.00,
            'total_referral_earnings' => 4000.00,
            'total_profit_earnings' => 6000.00,
            'bonus_balance' => 250.00,
            'current_team_volume' => 15000.00,
            'current_personal_volume' => 5000.00,
            'current_team_depth' => 5,
            'active_referrals_count' => 3,
            'subscription_status' => 'grace',
            'loyalty_points' => 1200,
            'has_starter_kit' => true,
            'starter_kit_tier' => 'silver',
            'loan_balance' => 2000.00,
            'loan_limit' => 10000.00,
            'verification_level' => 'verified',
            'daily_withdrawal_used' => 200,
            'is_currently_active' => true,
        ]);

        $this->assertEquals(42, $member->id()->value());
        $this->assertEquals(10, $member->userId());
        $this->assertEquals(5, $member->referrerId()?->value());
        $this->assertEquals('MYCODE', $member->referralCode()?->value());
        $this->assertEquals(7, $member->referralCount());
        $this->assertEquals(3, $member->directReferrals());
        $this->assertEquals('Gold Leader', $member->rank());
        $this->assertEquals(MembershipTier::Silver, $member->tier());
        $this->assertEquals(5000, $member->balance()->amount());
        $this->assertEquals(15000, $member->currentTeamVolume());
        $this->assertTrue($member->hasActiveSubscription());
        $this->assertTrue($member->hasStarterKit());
        $this->assertEquals('silver', $member->starterKitTier());
        $this->assertEquals(VerificationLevel::Verified, $member->verificationLevel());
        $this->assertTrue($member->isActive());
    }

    public function test_member_to_array_includes_all_fields(): void
    {
        $member = Member::reconstitute([
            'id' => 1, 'user_id' => 1, 'referrer_id' => 2,
            'referral_code' => 'JOIN',
            'referral_count' => 5, 'direct_referrals' => 2,
            'rank' => 'Silver', 'current_professional_level' => 'bronze',
            'balance' => 1000, 'total_earnings' => 5000,
            'total_referral_earnings' => 2000, 'total_profit_earnings' => 3000,
            'bonus_balance' => 100,
            'current_team_volume' => 5000, 'current_personal_volume' => 1000,
            'current_team_depth' => 3, 'active_referrals_count' => 2,
            'subscription_status' => 'active',
            'loyalty_points' => 500, 'has_starter_kit' => true,
            'starter_kit_tier' => 'bronze',
            'loan_balance' => 0, 'loan_limit' => 5000,
            'verification_level' => 'basic',
            'daily_withdrawal_used' => 0,
            'is_currently_active' => true,
        ]);

        $arr = $member->toArray();
        $this->assertEquals(1, $arr['id']);
        $this->assertEquals(2, $arr['referrer_id']);
        $this->assertEquals('JOIN', $arr['referral_code']);
        $this->assertEquals(5, $arr['referral_count']);
        $this->assertEquals(1000, $arr['balance']);
        $this->assertTrue($arr['has_starter_kit']);
        $this->assertTrue($arr['is_currently_active']);
        $this->assertEquals('active', $arr['subscription_status']);
    }

    // --- Commission Entity ---

    public function test_commission_create_returns_new_commission(): void
    {
        $commission = Commission::create(
            referrerId: new MemberId(1),
            referredMemberId: new MemberId(2),
            referredName: 'John Doe',
            level: CommissionLevel::Level1,
            amount: new Money(120.00),
            originalAmount: new Money(120.00),
            type: 'referral',
            source: 'starter_kit',
            description: 'Direct referral bonus',
        );

        $this->assertEquals(0, $commission->id());
        $this->assertEquals(1, $commission->referrerId()->value());
        $this->assertEquals(2, $commission->referredMemberId()->value());
        $this->assertEquals('John Doe', $commission->referredName());
        $this->assertEquals(CommissionLevel::Level1, $commission->level());
        $this->assertEquals(120.00, $commission->amount()->amount());
        $this->assertEquals('pending', $commission->status());
        $this->assertEquals('referral', $commission->type());
        $this->assertInstanceOf(\DateTimeImmutable::class, $commission->createdAt());
    }

    public function test_commission_mark_as_paid(): void
    {
        $commission = Commission::create(
            referrerId: new MemberId(1),
            referredMemberId: new MemberId(2),
            referredName: 'Jane',
            level: CommissionLevel::Level2,
            amount: new Money(60.00),
            originalAmount: new Money(60.00),
        );

        $this->assertEquals('pending', $commission->status());
        $commission->markAsPaid();
        $this->assertEquals('paid', $commission->status());
    }

    public function test_commission_to_array(): void
    {
        $commission = Commission::create(
            referrerId: new MemberId(1),
            referredMemberId: new MemberId(2),
            referredName: 'Test User',
            level: CommissionLevel::Level3,
            amount: new Money(40.00),
            originalAmount: new Money(50.00),
        );

        $arr = $commission->toArray();
        $this->assertEquals(1, $arr['referrer_id']);
        $this->assertEquals(2, $arr['referred_member_id']);
        $this->assertEquals('Test User', $arr['referred_name']);
        $this->assertEquals(40.00, $arr['amount']);
        $this->assertEquals(50.00, $arr['original_amount']);
        $this->assertEquals('pending', $arr['status']);
        $this->assertEquals(3, $arr['level']);
    }

    // --- Referral Entity ---

    public function test_referral_construction(): void
    {
        $referral = new Referral(
            id: 1,
            referrerId: new MemberId(10),
            referredMemberId: new MemberId(20),
            referredName: 'Alice',
            referredEmail: 'alice@example.com',
            level: new NetworkLevel(1),
            createdAt: new \DateTimeImmutable('2026-01-15'),
            tier: 'bronze',
            hasStarterKit: true,
            starterKitTier: 'bronze',
            isActive: true,
            personalVolume: 500,
        );

        $this->assertEquals(1, $referral->id());
        $this->assertEquals(10, $referral->referrerId()->value());
        $this->assertEquals(20, $referral->referredMemberId()->value());
        $this->assertEquals('Alice', $referral->referredName());
        $this->assertEquals('alice@example.com', $referral->referredEmail());
        $this->assertEquals(1, $referral->level()->value());
        $this->assertEquals('bronze', $referral->tier());
        $this->assertTrue($referral->isActive());
        $this->assertEquals(500, $referral->personalVolume());
    }

    public function test_referral_defaults(): void
    {
        $referral = new Referral(
            id: 0,
            referrerId: new MemberId(1),
            referredMemberId: new MemberId(2),
            referredName: 'Bob',
            referredEmail: 'bob@test.com',
            level: new NetworkLevel(2),
            createdAt: new \DateTimeImmutable(),
        );

        $this->assertNull($referral->tier());
        $this->assertFalse($referral->isActive());
        $this->assertEquals(0, $referral->personalVolume());
    }

    public function test_referral_to_array(): void
    {
        $referral = new Referral(
            id: 5,
            referrerId: new MemberId(1),
            referredMemberId: new MemberId(3),
            referredName: 'Charlie',
            referredEmail: 'charlie@test.com',
            level: new NetworkLevel(1),
            createdAt: new \DateTimeImmutable('2026-03-01'),
        );

        $arr = $referral->toArray();
        $this->assertEquals(5, $arr['id']);
        $this->assertEquals(1, $arr['referrer_id']);
        $this->assertEquals('Charlie', $arr['name']);
        $this->assertEquals('charlie@test.com', $arr['email']);
    }

    // --- TeamVolume Entity ---

    public function test_team_volume_construction(): void
    {
        $tv = new TeamVolume(
            id: 1,
            memberId: new MemberId(42),
            personalVolume: 1000,
            teamVolume: 15000,
            leftLegVolume: 8000,
            rightLegVolume: 7000,
            totalVolume: 16000,
            activeReferralsCount: 5,
            periodStart: new \DateTimeImmutable('2026-01-01'),
            periodEnd: new \DateTimeImmutable('2026-01-31'),
            createdAt: new \DateTimeImmutable('2026-01-31'),
        );

        $this->assertEquals(1, $tv->id());
        $this->assertEquals(42, $tv->memberId()->value());
        $this->assertEquals(1000, $tv->personalVolume());
        $this->assertEquals(15000, $tv->teamVolume());
        $this->assertEquals(8000, $tv->leftLegVolume());
        $this->assertEquals(7000, $tv->rightLegVolume());
        $this->assertEquals(16000, $tv->totalVolume());
        $this->assertEquals(5, $tv->activeReferralsCount());
    }

    public function test_team_volume_to_array(): void
    {
        $tv = new TeamVolume(
            id: 2,
            memberId: new MemberId(1),
            personalVolume: 500,
            teamVolume: 5000,
            leftLegVolume: 3000,
            rightLegVolume: 2000,
            totalVolume: 5500,
            activeReferralsCount: 3,
            periodStart: new \DateTimeImmutable('2026-02-01'),
            periodEnd: new \DateTimeImmutable('2026-02-28'),
            createdAt: new \DateTimeImmutable('2026-02-28'),
        );

        $arr = $tv->toArray();
        $this->assertEquals(1, $arr['member_id']);
        $this->assertEquals(500, $arr['personal_volume']);
        $this->assertEquals(5000, $arr['team_volume']);
        $this->assertEquals(3000, $arr['left_leg_volume']);
        $this->assertEquals(2000, $arr['right_leg_volume']);
        $this->assertEquals(3, $arr['active_referrals_count']);
    }

    // --- TierUpgrade Entity ---

    public function test_tier_upgrade_create(): void
    {
        $upgrade = TierUpgrade::create(
            memberId: new MemberId(1),
            fromTier: MembershipTier::Bronze,
            toTier: MembershipTier::Silver,
            reason: 'team_volume_met',
            achievementBonus: new Money(250),
            teamVolumeAtUpgrade: 6000,
            activeReferralsAtUpgrade: 3,
        );

        $this->assertEquals(0, $upgrade->id());
        $this->assertEquals(1, $upgrade->memberId()->value());
        $this->assertEquals(MembershipTier::Bronze, $upgrade->fromTier());
        $this->assertEquals(MembershipTier::Silver, $upgrade->toTier());
        $this->assertEquals('team_volume_met', $upgrade->reason());
        $this->assertEquals(250, $upgrade->achievementBonus()->amount());
    }

    public function test_tier_upgrade_defaults(): void
    {
        $upgrade = TierUpgrade::create(
            memberId: new MemberId(1),
            fromTier: MembershipTier::Associate,
            toTier: MembershipTier::Bronze,
        );

        $this->assertEquals('manual', $upgrade->reason());
        $this->assertEquals(0, $upgrade->achievementBonus()->amount());
    }

    public function test_tier_upgrade_to_array(): void
    {
        $upgrade = TierUpgrade::create(
            memberId: new MemberId(1),
            fromTier: MembershipTier::Silver,
            toTier: MembershipTier::Gold,
            reason: 'automatic_qualification',
            achievementBonus: new Money(500),
            teamVolumeAtUpgrade: 20000,
            activeReferralsAtUpgrade: 5,
        );

        $arr = $upgrade->toArray();
        $this->assertEquals(1, $arr['member_id']);
        $this->assertEquals('silver', $arr['from_tier']);
        $this->assertEquals('gold', $arr['to_tier']);
        $this->assertEquals(500, $arr['achievement_bonus']);
        $this->assertEquals(20000, $arr['team_volume']);
        $this->assertEquals(5, $arr['active_referrals']);
    }

    // --- StarterKit Entity ---

    public function test_starter_kit_construction(): void
    {
        $kit = new StarterKit(
            id: 1,
            memberId: new MemberId(10),
            tier: 'silver',
            status: 'purchased',
            price: new Money(299),
            purchasedAt: new \DateTimeImmutable('2026-06-15'),
        );

        $this->assertEquals(1, $kit->id());
        $this->assertEquals(10, $kit->memberId()->value());
        $this->assertEquals('silver', $kit->tier());
        $this->assertEquals('purchased', $kit->status());
        $this->assertEquals(299, $kit->price()->amount());
        $this->assertTrue($kit->isActive());
    }

    public function test_starter_kit_is_active_checks_status(): void
    {
        $activeKit = new StarterKit(1, new MemberId(1), 'bronze', 'active', new Money(99), new \DateTimeImmutable());
        $this->assertTrue($activeKit->isActive());

        $inactiveKit = new StarterKit(2, new MemberId(2), 'bronze', 'expired', new Money(99), new \DateTimeImmutable());
        $this->assertFalse($inactiveKit->isActive());
    }

    public function test_starter_kit_to_array(): void
    {
        $kit = new StarterKit(
            id: 1,
            memberId: new MemberId(5),
            tier: 'gold',
            status: 'active',
            price: new Money(999),
            purchasedAt: new \DateTimeImmutable('2026-07-01'),
        );

        $arr = $kit->toArray();
        $this->assertEquals(5, $arr['member_id']);
        $this->assertEquals('gold', $arr['tier']);
        $this->assertEquals(999, $arr['price']);
        $this->assertTrue($arr['has_kit']);
    }

    // --- LoyaltyPoints Entity ---

    public function test_loyalty_points_construction(): void
    {
        $lp = new LoyaltyPoints(
            id: 1,
            memberId: new MemberId(1),
            lpAmount: 500,
            bpAmount: 100,
            type: 'earned',
            description: 'Monthly bonus',
            status: 'active',
            createdAt: new \DateTimeImmutable('2026-07-15'),
        );

        $this->assertEquals(1, $lp->id());
        $this->assertEquals(1, $lp->memberId()->value());
        $this->assertEquals(500, $lp->lpAmount());
        $this->assertEquals(100, $lp->bpAmount());
        $this->assertEquals('earned', $lp->type());
        $this->assertEquals('active', $lp->status());
    }

    public function test_loyalty_points_to_array(): void
    {
        $lp = new LoyaltyPoints(
            id: 1,
            memberId: new MemberId(1),
            lpAmount: 200,
            bpAmount: 50,
            type: 'referral',
            description: 'Referral reward',
            status: 'active',
            createdAt: new \DateTimeImmutable('2026-07-20'),
        );

        $arr = $lp->toArray();
        $this->assertEquals(1, $arr['member_id']);
        $this->assertEquals(200, $arr['lp_amount']);
        $this->assertEquals(50, $arr['bp_amount']);
        $this->assertEquals('referral', $arr['type']);
        $this->assertEquals('active', $arr['status']);
    }
}
