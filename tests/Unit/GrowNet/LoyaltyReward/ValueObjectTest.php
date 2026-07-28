<?php

namespace Tests\Unit\GrowNet\LoyaltyReward;

use App\Domain\GrowNet\LoyaltyReward\ValueObjects\LoyaltyAmount;
use App\Domain\GrowNet\LoyaltyReward\ValueObjects\CycleStatus;
use App\Domain\GrowNet\LoyaltyReward\ValueObjects\CycleId;
use App\Domain\GrowNet\LoyaltyReward\ValueObjects\ActivityType;
use PHPUnit\Framework\TestCase;

class ValueObjectTest extends TestCase
{
    // --- LoyaltyAmount ---

    public function test_loyalty_amount_from_kwacha(): void
    {
        $amount = LoyaltyAmount::fromKwacha(1000);
        $this->assertEquals(1000, $amount->toKwacha());
        $this->assertEquals(1000.0, $amount->toFloat());
    }

    public function test_loyalty_amount_zero(): void
    {
        $amount = LoyaltyAmount::zero();
        $this->assertTrue($amount->equals(LoyaltyAmount::fromKwacha(0)));
        $this->assertEquals(0, $amount->toKwacha());
    }

    public function test_loyalty_amount_rejects_negative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        LoyaltyAmount::fromKwacha(-100);
    }

    public function test_loyalty_amount_add(): void
    {
        $a = LoyaltyAmount::fromKwacha(500);
        $b = LoyaltyAmount::fromKwacha(300);
        $result = $a->add($b);
        $this->assertEquals(800, $result->toKwacha());
        $this->assertTrue($result->equals(LoyaltyAmount::fromKwacha(800)));
    }

    public function test_loyalty_amount_subtract(): void
    {
        $a = LoyaltyAmount::fromKwacha(500);
        $b = LoyaltyAmount::fromKwacha(200);
        $result = $a->subtract($b);
        $this->assertEquals(300, $result->toKwacha());
    }

    public function test_loyalty_amount_subtract_more_than_balance_throws(): void
    {
        $this->expectException(\DomainException::class);
        LoyaltyAmount::fromKwacha(100)->subtract(LoyaltyAmount::fromKwacha(200));
    }

    public function test_loyalty_amount_calculate_percentage(): void
    {
        $amount = LoyaltyAmount::fromKwacha(1000);
        $thirty = $amount->calculatePercentage(30);
        $this->assertEquals(300, $thirty->toKwacha());

        $hundred = $amount->calculatePercentage(100);
        $this->assertEquals(1000, $hundred->toKwacha());

        $zero = $amount->calculatePercentage(0);
        $this->assertEquals(0, $zero->toKwacha());
    }

    public function test_loyalty_amount_calculate_percentage_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        LoyaltyAmount::fromKwacha(100)->calculatePercentage(101);
    }

    public function test_loyalty_amount_get_max_cash_conversion(): void
    {
        $amount = LoyaltyAmount::fromKwacha(1000);
        $maxCash = $amount->getMaxCashConversion();
        // 40% of 1000 = 400
        $this->assertEquals(400, $maxCash->toKwacha());
    }

    public function test_loyalty_amount_can_convert_to_cash(): void
    {
        $this->assertTrue(LoyaltyAmount::fromKwacha(100)->canConvertToCash());
        $this->assertTrue(LoyaltyAmount::fromKwacha(500)->canConvertToCash());
        $this->assertFalse(LoyaltyAmount::fromKwacha(99)->canConvertToCash());
        $this->assertFalse(LoyaltyAmount::fromKwacha(0)->canConvertToCash());
    }

    public function test_loyalty_amount_comparison(): void
    {
        $small = LoyaltyAmount::fromKwacha(100);
        $large = LoyaltyAmount::fromKwacha(200);

        $this->assertTrue($large->isGreaterThan($small));
        $this->assertFalse($small->isGreaterThan($large));

        $this->assertTrue($small->isLessThan($large));
        $this->assertFalse($large->isLessThan($small));

        $this->assertTrue($small->isLessThanOrEqual(LoyaltyAmount::fromKwacha(100)));
        $this->assertTrue($small->isLessThanOrEqual($large));

        $this->assertTrue($large->isGreaterThanOrEqual(LoyaltyAmount::fromKwacha(200)));
        $this->assertTrue($large->isGreaterThanOrEqual($small));
    }

    public function test_loyalty_amount_equals(): void
    {
        $this->assertTrue(LoyaltyAmount::fromKwacha(50)->equals(LoyaltyAmount::fromKwacha(50)));
        $this->assertFalse(LoyaltyAmount::fromKwacha(50)->equals(LoyaltyAmount::fromKwacha(51)));
    }

    public function test_loyalty_amount_format(): void
    {
        $amount = LoyaltyAmount::fromKwacha(1500);
        $this->assertEquals('K1,500.00', $amount->format());
    }

    // --- CycleStatus ---

    public function test_cycle_status_active(): void
    {
        $status = CycleStatus::active();
        $this->assertTrue($status->isActive());
        $this->assertFalse($status->isCompleted());
        $this->assertFalse($status->isSuspended());
        $this->assertFalse($status->isTerminated());
        $this->assertEquals('active', $status->value);
        $this->assertEquals('Active', $status->getDisplayName());
    }

    public function test_cycle_status_completed(): void
    {
        $status = CycleStatus::completed();
        $this->assertTrue($status->isCompleted());
        $this->assertFalse($status->isActive());
        $this->assertEquals('Completed', $status->getDisplayName());
    }

    public function test_cycle_status_suspended(): void
    {
        $status = CycleStatus::suspended();
        $this->assertTrue($status->isSuspended());
        $this->assertFalse($status->isActive());
        $this->assertEquals('Suspended', $status->getDisplayName());
    }

    public function test_cycle_status_terminated(): void
    {
        $status = CycleStatus::terminated();
        $this->assertTrue($status->isTerminated());
        $this->assertFalse($status->isActive());
        $this->assertEquals('Terminated', $status->getDisplayName());
    }

    public function test_cycle_status_from_string(): void
    {
        $this->assertTrue(CycleStatus::from('active')->isActive());
        $this->assertTrue(CycleStatus::from('completed')->isCompleted());
    }

    // --- CycleId ---

    public function test_cycle_id_generate_creates_uuid(): void
    {
        $id = CycleId::generate();
        $this->assertNotEmpty($id->toString());
        $this->assertStringContainsString('-', $id->toString());
    }

    public function test_cycle_id_from_string(): void
    {
        $id = CycleId::fromString('abc-123');
        $this->assertEquals('abc-123', $id->toString());
    }

    public function test_cycle_id_rejects_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CycleId::fromString('');
    }

    public function test_cycle_id_equals(): void
    {
        $a = CycleId::fromString('uuid-1');
        $b = CycleId::fromString('uuid-1');
        $c = CycleId::fromString('uuid-2');
        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    // --- ActivityType ---

    public function test_activity_type_values(): void
    {
        $this->assertEquals('learning_module', ActivityType::LEARNING_MODULE->value);
        $this->assertEquals('marketplace_purchase', ActivityType::MARKETPLACE_PURCHASE->value);
        $this->assertEquals('webinar_attendance', ActivityType::WEBINAR_ATTENDANCE->value);
    }

    public function test_activity_type_display_names(): void
    {
        $this->assertEquals('Learning Module Completed', ActivityType::LEARNING_MODULE->getDisplayName());
        $this->assertEquals('Marketplace Purchase', ActivityType::MARKETPLACE_PURCHASE->getDisplayName());
        $this->assertEquals('Webinar Attended', ActivityType::WEBINAR_ATTENDANCE->getDisplayName());
    }

    public function test_activity_type_is_learning(): void
    {
        $this->assertTrue(ActivityType::LEARNING_MODULE->isLearningActivity());
        $this->assertTrue(ActivityType::QUIZ_COMPLETION->isLearningActivity());
        $this->assertTrue(ActivityType::WEBINAR_ATTENDANCE->isLearningActivity());
        $this->assertFalse(ActivityType::MARKETPLACE_PURCHASE->isLearningActivity());
        $this->assertFalse(ActivityType::COMMUNITY_DISCUSSION->isLearningActivity());
    }

    public function test_activity_type_is_business(): void
    {
        $this->assertTrue(ActivityType::MARKETPLACE_PURCHASE->isBusinessActivity());
        $this->assertTrue(ActivityType::MARKETPLACE_LISTING->isBusinessActivity());
        $this->assertTrue(ActivityType::COMMUNITY_DISCUSSION->isBusinessActivity());
        $this->assertTrue(ActivityType::BUSINESS_PLAN->isBusinessActivity());
        $this->assertFalse(ActivityType::LEARNING_MODULE->isBusinessActivity());
        $this->assertFalse(ActivityType::QUIZ_COMPLETION->isBusinessActivity());
    }
}
