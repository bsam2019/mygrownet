<?php

namespace Tests\Unit\GrowNet;

use App\Domain\GrowNet\ValueObjects\MemberId;
use App\Domain\GrowNet\ValueObjects\Money;
use App\Domain\GrowNet\ValueObjects\Percentage;
use App\Domain\GrowNet\ValueObjects\ReferralCode;
use App\Domain\GrowNet\ValueObjects\CommissionLevel;
use App\Domain\GrowNet\ValueObjects\MembershipTier;
use App\Domain\GrowNet\ValueObjects\SubscriptionStatus;
use App\Domain\GrowNet\ValueObjects\VerificationLevel;
use App\Domain\GrowNet\ValueObjects\NetworkLevel;
use Tests\TestCase;

class ValueObjectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'mygrownet.commission_rates' => [
                1 => 12.0,
                2 => 6.0,
                3 => 4.0,
                4 => 2.0,
                5 => 1.0,
            ],
            'mygrownet.membership_tiers' => [
                'bronze' => ['monthly_fee' => 50, 'team_volume_requirement' => 0],
                'silver' => ['monthly_fee' => 100, 'team_volume_requirement' => 5000],
                'gold' => ['monthly_fee' => 200, 'team_volume_requirement' => 15000],
                'diamond' => ['monthly_fee' => 500, 'team_volume_requirement' => 50000],
                'elite' => ['monthly_fee' => 1000, 'team_volume_requirement' => 100000],
            ],
            'mygrownet.tier_advancement_bonuses' => [
                'Bronze' => 100,
                'Silver' => 250,
                'Gold' => 500,
                'Diamond' => 1000,
                'Elite' => 2500,
            ],
        ]);
    }

    // --- MemberId ---

    public function test_member_id_creates_with_positive_int(): void
    {
        $id = new MemberId(42);
        $this->assertEquals(42, $id->value());
        $this->assertEquals('42', (string) $id);
    }

    public function test_member_id_accepts_zero_for_transient(): void
    {
        $id = new MemberId(0);
        $this->assertEquals(0, $id->value());
    }

    public function test_member_id_rejects_negative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new MemberId(-1);
    }

    public function test_member_id_equals(): void
    {
        $a = new MemberId(5);
        $b = new MemberId(5);
        $c = new MemberId(10);
        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    // --- Money ---

    public function test_money_creates_with_valid_amount(): void
    {
        $m = new Money(100.50);
        $this->assertEquals(100.50, $m->amount());
        $this->assertEquals('ZMW', $m->currency());
    }

    public function test_money_rejects_negative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Money(-1);
    }

    public function test_money_zero_allowed(): void
    {
        $m = new Money(0);
        $this->assertEquals(0, $m->amount());
    }

    public function test_money_custom_currency(): void
    {
        $m = new Money(50, 'USD');
        $this->assertEquals(50, $m->amount());
        $this->assertEquals('USD', $m->currency());
    }

    public function test_money_add(): void
    {
        $a = new Money(100);
        $b = new Money(50);
        $r = $a->add($b);
        $this->assertEquals(150, $r->amount());
    }

    public function test_money_add_different_currency_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Money(100, 'ZMW'))->add(new Money(50, 'USD'));
    }

    public function test_money_subtract(): void
    {
        $a = new Money(100);
        $b = new Money(30);
        $r = $a->subtract($b);
        $this->assertEquals(70, $r->amount());
    }

    public function test_money_subtract_insufficient_funds_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Money(30))->subtract(new Money(100));
    }

    public function test_money_subtract_different_currency_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Money(100, 'ZMW'))->subtract(new Money(30, 'USD'));
    }

    public function test_money_multiply(): void
    {
        $m = new Money(25);
        $r = $m->multiply(4);
        $this->assertEquals(100, $r->amount());
    }

    public function test_money_multiply_by_zero(): void
    {
        $m = new Money(50);
        $r = $m->multiply(0);
        $this->assertEquals(0, $r->amount());
    }

    public function test_money_is_greater_and_less_than(): void
    {
        $a = new Money(100);
        $b = new Money(200);
        $this->assertTrue($b->isGreaterThan($a));
        $this->assertTrue($a->isLessThan($b));
        $this->assertFalse($a->isGreaterThan($b));
    }

    public function test_money_equals(): void
    {
        $a = new Money(10, 'USD');
        $b = new Money(10, 'USD');
        $c = new Money(10, 'ZMW');
        $d = new Money(20, 'USD');
        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
        $this->assertFalse($a->equals($d));
    }

    public function test_money_string_representation(): void
    {
        $m = new Money(100.50, 'ZMW');
        $this->assertStringContainsString('100.50', (string) $m);
        $this->assertStringContainsString('ZMW', (string) $m);
    }

    // --- Percentage ---

    public function test_percentage_creates_with_valid_value(): void
    {
        $p = new Percentage(25);
        $this->assertEquals(25, $p->value());
        $this->assertEquals(0.25, $p->asDecimal());
    }

    public function test_percentage_rejects_negative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Percentage(-1);
    }

    public function test_percentage_rejects_over_100(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Percentage(101);
    }

    public function test_percentage_zero_and_hundred(): void
    {
        $p0 = new Percentage(0);
        $p100 = new Percentage(100);
        $this->assertEquals(0, $p0->value());
        $this->assertEquals(100, $p100->value());
        $this->assertEquals(1.0, $p100->asDecimal());
    }

    public function test_percentage_apply_to(): void
    {
        $p = new Percentage(10);
        $money = new Money(200);
        $result = $p->applyTo($money);
        $this->assertEquals(20, $result->amount());
    }

    public function test_percentage_equals(): void
    {
        $this->assertTrue((new Percentage(15))->equals(new Percentage(15)));
        $this->assertFalse((new Percentage(15))->equals(new Percentage(20)));
    }

    public function test_percentage_to_string(): void
    {
        $this->assertEquals('25%', (string) new Percentage(25));
    }

    // --- ReferralCode ---

    public function test_referral_code_creates_with_valid_string(): void
    {
        $rc = new ReferralCode('ABC123');
        $this->assertEquals('ABC123', $rc->value());
    }

    public function test_referral_code_rejects_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ReferralCode('');
    }

    public function test_referral_code_rejects_whitespace(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ReferralCode('   ');
    }

    public function test_referral_code_equals_case_insensitive(): void
    {
        $this->assertTrue((new ReferralCode('ABC'))->equals(new ReferralCode('abc')));
        $this->assertFalse((new ReferralCode('ABC'))->equals(new ReferralCode('XYZ')));
    }

    public function test_referral_code_to_string(): void
    {
        $rc = new ReferralCode('JOINME');
        $this->assertEquals('JOINME', (string) $rc);
    }

    // --- CommissionLevel ---

    public function test_commission_level_values(): void
    {
        $this->assertEquals(1, CommissionLevel::Level1->value);
        $this->assertEquals(7, CommissionLevel::Level7->value);
    }

    public function test_commission_level_label(): void
    {
        $this->assertEquals('Level 1', CommissionLevel::Level1->label());
        $this->assertEquals('Level 7', CommissionLevel::Level7->label());
    }

    public function test_commission_level_rate_returns_config_or_default(): void
    {
        $this->assertEquals(12.0, CommissionLevel::Level1->rate());
        $this->assertEquals(6.0, CommissionLevel::Level2->rate());
        $this->assertEquals(4.0, CommissionLevel::Level3->rate());
        $this->assertEquals(2.0, CommissionLevel::Level4->rate());
        $this->assertEquals(1.0, CommissionLevel::Level5->rate());
        $this->assertEquals(0.5, CommissionLevel::Level6->rate());
        $this->assertEquals(0.25, CommissionLevel::Level7->rate());
    }

    // --- MembershipTier ---

    public function test_membership_tier_values(): void
    {
        $this->assertEquals('associate', MembershipTier::Associate->value);
        $this->assertEquals('elite', MembershipTier::Elite->value);
    }

    public function test_membership_tier_display_name(): void
    {
        $this->assertEquals('Associate', MembershipTier::Associate->displayName());
        $this->assertEquals('Bronze', MembershipTier::Bronze->displayName());
        $this->assertEquals('Elite', MembershipTier::Elite->displayName());
    }

    public function test_membership_tier_next(): void
    {
        $this->assertEquals(MembershipTier::Bronze, MembershipTier::Associate->next());
        $this->assertEquals(MembershipTier::Silver, MembershipTier::Bronze->next());
        $this->assertEquals(MembershipTier::Gold, MembershipTier::Silver->next());
        $this->assertEquals(MembershipTier::Diamond, MembershipTier::Gold->next());
        $this->assertEquals(MembershipTier::Elite, MembershipTier::Diamond->next());
        $this->assertNull(MembershipTier::Elite->next());
    }

    public function test_membership_tier_monthly_fee_uses_default(): void
    {
        $this->assertEquals(0, MembershipTier::Associate->monthlyFee());
        $this->assertEquals(50, MembershipTier::Bronze->monthlyFee());
        $this->assertEquals(100, MembershipTier::Silver->monthlyFee());
        $this->assertEquals(200, MembershipTier::Gold->monthlyFee());
        $this->assertEquals(500, MembershipTier::Diamond->monthlyFee());
        $this->assertEquals(1000, MembershipTier::Elite->monthlyFee());
    }

    public function test_membership_tier_team_volume_requirement(): void
    {
        $this->assertEquals(0, MembershipTier::Associate->teamVolumeRequirement());
        $this->assertEquals(0, MembershipTier::Bronze->teamVolumeRequirement());
        $this->assertEquals(5000, MembershipTier::Silver->teamVolumeRequirement());
        $this->assertEquals(15000, MembershipTier::Gold->teamVolumeRequirement());
    }

    // --- SubscriptionStatus ---

    public function test_subscription_status_active_values(): void
    {
        $this->assertTrue(SubscriptionStatus::Active->isActive());
        $this->assertTrue(SubscriptionStatus::Grace->isActive());
        $this->assertFalse(SubscriptionStatus::Inactive->isActive());
        $this->assertFalse(SubscriptionStatus::Expired->isActive());
        $this->assertFalse(SubscriptionStatus::Suspended->isActive());
        $this->assertFalse(SubscriptionStatus::Cancelled->isActive());
        $this->assertFalse(SubscriptionStatus::Pending->isActive());
    }

    public function test_subscription_status_values(): void
    {
        $this->assertEquals('active', SubscriptionStatus::Active->value);
        $this->assertEquals('grace', SubscriptionStatus::Grace->value);
        $this->assertEquals('cancelled', SubscriptionStatus::Cancelled->value);
    }

    // --- VerificationLevel ---

    public function test_verification_level_values(): void
    {
        $this->assertEquals('basic', VerificationLevel::Basic->value);
        $this->assertEquals('verified', VerificationLevel::Verified->value);
        $this->assertEquals('premium', VerificationLevel::Premium->value);
    }

    public function test_verification_level_daily_withdrawal_limit(): void
    {
        $this->assertEquals(1000, VerificationLevel::Basic->dailyWithdrawalLimit());
        $this->assertEquals(5000, VerificationLevel::Verified->dailyWithdrawalLimit());
        $this->assertEquals(20000, VerificationLevel::Premium->dailyWithdrawalLimit());
    }

    // --- NetworkLevel ---

    public function test_network_level_creates_with_valid_level(): void
    {
        $nl = new NetworkLevel(3);
        $this->assertEquals(3, $nl->value());
    }

    public function test_network_level_rejects_below_1(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new NetworkLevel(0);
    }

    public function test_network_level_rejects_above_7(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new NetworkLevel(8);
    }

    public function test_network_level_equals(): void
    {
        $this->assertTrue((new NetworkLevel(3))->equals(new NetworkLevel(3)));
        $this->assertFalse((new NetworkLevel(3))->equals(new NetworkLevel(5)));
    }

    public function test_network_level_to_string(): void
    {
        $this->assertEquals('Level 4', (string) new NetworkLevel(4));
    }
}
