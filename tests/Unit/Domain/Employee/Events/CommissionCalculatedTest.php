<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Employee\Events;

use App\Domain\Employee\Events\CommissionCalculated;
use App\Domain\Employee\ValueObjects\EmployeeId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class CommissionCalculatedTest extends TestCase
{
    private EmployeeId $employeeId;
    private DateTimeImmutable $calculationDate;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->employeeId = EmployeeId::generate();
        $this->calculationDate = new DateTimeImmutable('2024-01-15');
    }

    public function test_can_create_commission_calculated_event(): void
    {
        $event = new CommissionCalculated(
            employeeId: $this->employeeId,
            investmentId: 123,
            userId: 456,
            commissionType: 'investment_facilitation',
            baseAmount: 10000.0,
            commissionRate: 2.5,
            commissionAmount: 250.0,
            calculationDate: $this->calculationDate,
            calculationMethod: 'percentage',
            calculationDetails: ['tier' => 'basic', 'multiplier' => 1.0],
            notes: 'Standard commission calculation'
        );

        $this->assertTrue($this->employeeId->equals($event->employeeId));
        $this->assertEquals(123, $event->investmentId);
        $this->assertEquals(456, $event->userId);
        $this->assertEquals('investment_facilitation', $event->commissionType);
        $this->assertEquals(10000.0, $event->baseAmount);
        $this->assertEquals(2.5, $event->commissionRate);
        $this->assertEquals(250.0, $event->commissionAmount);
        $this->assertEquals($this->calculationDate, $event->calculationDate);
        $this->assertEquals('percentage', $event->calculationMethod);
        $this->assertEquals(['tier' => 'basic', 'multiplier' => 1.0], $event->calculationDetails);
        $this->assertEquals('Standard commission calculation', $event->notes);
    }

    public function test_to_array_returns_correct_data(): void
    {
        $event = new CommissionCalculated(
            employeeId: $this->employeeId,
            investmentId: 123,
            userId: 456,
            commissionType: 'investment_facilitation',
            baseAmount: 10000.0,
            commissionRate: 2.5,
            commissionAmount: 250.0,
            calculationDate: $this->calculationDate,
            calculationMethod: 'percentage'
        );

        $array = $event->toArray();

        $this->assertEquals($this->employeeId->toString(), $array['employee_id']);
        $this->assertEquals(123, $array['investment_id']);
        $this->assertEquals(456, $array['user_id']);
        $this->assertEquals('investment_facilitation', $array['commission_type']);
        $this->assertEquals(10000.0, $array['base_amount']);
        $this->assertEquals(2.5, $array['commission_rate']);
        $this->assertEquals(250.0, $array['commission_amount']);
        $this->assertEquals('2024-01-15', $array['calculation_date']);
        $this->assertEquals('percentage', $array['calculation_method']);
        $this->assertArrayHasKey('occurred_at', $array);
    }

    public function test_get_event_name_returns_correct_name(): void
    {
        $event = new CommissionCalculated(
            employeeId: $this->employeeId,
            investmentId: 123,
            userId: 456,
            commissionType: 'investment_facilitation',
            baseAmount: 10000.0,
            commissionRate: 2.5,
            commissionAmount: 250.0,
            calculationDate: $this->calculationDate,
            calculationMethod: 'percentage'
        );

        $this->assertEquals('employee.commission_calculated', $event->getEventName());
    }

    public function test_commission_type_checks(): void
    {
        $investmentEvent = new CommissionCalculated(
            employeeId: $this->employeeId,
            investmentId: 123,
            userId: 456,
            commissionType: 'investment_facilitation',
            baseAmount: 10000.0,
            commissionRate: 2.5,
            commissionAmount: 250.0,
            calculationDate: $this->calculationDate,
            calculationMethod: 'percentage'
        );

        $this->assertTrue($investmentEvent->isInvestmentFacilitation());
        $this->assertFalse($investmentEvent->isReferral());
        $this->assertFalse($investmentEvent->isPerformanceBonus());
        $this->assertFalse($investmentEvent->isRetentionBonus());

        $referralEvent = new CommissionCalculated(
            employeeId: $this->employeeId,
            investmentId: null,
            userId: 456,
            commissionType: 'referral',
            baseAmount: 5000.0,
            commissionRate: 1.0,
            commissionAmount: 50.0,
            calculationDate: $this->calculationDate,
            calculationMethod: 'percentage'
        );

        $this->assertFalse($referralEvent->isInvestmentFacilitation());
        $this->assertTrue($referralEvent->isReferral());
        $this->assertFalse($referralEvent->isPerformanceBonus());
        $this->assertFalse($referralEvent->isRetentionBonus());
    }

    public function test_is_significant_amount_returns_true_for_large_commissions(): void
    {
        $event = new CommissionCalculated(
            employeeId: $this->employeeId,
            investmentId: 123,
            userId: 456,
            commissionType: 'investment_facilitation',
            baseAmount: 100000.0,
            commissionRate: 2.5,
            commissionAmount: 2500.0, // Significant amount
            calculationDate: $this->calculationDate,
            calculationMethod: 'percentage'
        );

        $this->assertTrue($event->isSignificantAmount());

        $smallEvent = new CommissionCalculated(
            employeeId: $this->employeeId,
            investmentId: 123,
            userId: 456,
            commissionType: 'investment_facilitation',
            baseAmount: 10000.0,
            commissionRate: 1.0,
            commissionAmount: 100.0, // Small amount
            calculationDate: $this->calculationDate,
            calculationMethod: 'percentage'
        );

        $this->assertFalse($smallEvent->isSignificantAmount());
    }

    public function test_is_valid_returns_true_for_valid_event(): void
    {
        $event = new CommissionCalculated(
            employeeId: $this->employeeId,
            investmentId: 123,
            userId: 456,
            commissionType: 'investment_facilitation',
            baseAmount: 10000.0,
            commissionRate: 2.5,
            commissionAmount: 250.0,
            calculationDate: $this->calculationDate,
            calculationMethod: 'percentage'
        );

        $this->assertTrue($event->isValid());
    }

    public function test_is_valid_returns_false_for_invalid_commission_type(): void
    {
        $event = new CommissionCalculated(
            employeeId: $this->employeeId,
            investmentId: 123,
            userId: 456,
            commissionType: 'invalid_type',
            baseAmount: 10000.0,
            commissionRate: 2.5,
            commissionAmount: 250.0,
            calculationDate: $this->calculationDate,
            calculationMethod: 'percentage'
        );

        $this->assertFalse($event->isValid());
    }

    public function test_is_valid_returns_false_for_investment_facilitation_without_investment_id(): void
    {
        $event = new CommissionCalculated(
            employeeId: $this->employeeId,
            investmentId: null, // Missing investment ID
            userId: 456,
            commissionType: 'investment_facilitation',
            baseAmount: 10000.0,
            commissionRate: 2.5,
            commissionAmount: 250.0,
            calculationDate: $this->calculationDate,
            calculationMethod: 'percentage'
        );

        $this->assertFalse($event->isValid());
    }

    public function test_is_valid_returns_false_for_referral_without_user_id(): void
    {
        $event = new CommissionCalculated(
            employeeId: $this->employeeId,
            investmentId: null,
            userId: null, // Missing user ID
            commissionType: 'referral',
            baseAmount: 5000.0,
            commissionRate: 1.0,
            commissionAmount: 50.0,
            calculationDate: $this->calculationDate,
            calculationMethod: 'percentage'
        );

        $this->assertFalse($event->isValid());
    }

    public function test_is_valid_returns_false_for_negative_amounts(): void
    {
        $event = new CommissionCalculated(
            employeeId: $this->employeeId,
            investmentId: 123,
            userId: 456,
            commissionType: 'investment_facilitation',
            baseAmount: -1000.0, // Negative base amount
            commissionRate: 2.5,
            commissionAmount: 250.0,
            calculationDate: $this->calculationDate,
            calculationMethod: 'percentage'
        );

        $this->assertFalse($event->isValid());
    }
}