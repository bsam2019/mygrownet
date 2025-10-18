<?php

declare(strict_types=1);

namespace App\Domain\Employee\Events;

use App\Domain\Employee\ValueObjects\EmployeeId;
use DateTimeImmutable;

/**
 * Domain event fired when an employee's commission is calculated
 */
final readonly class CommissionCalculated
{
    public function __construct(
        public EmployeeId $employeeId,
        public ?int $investmentId, // Link to specific investment
        public ?int $userId, // Client who made the investment
        public string $commissionType, // investment_facilitation, referral, performance_bonus, retention_bonus
        public float $baseAmount,
        public float $commissionRate,
        public float $commissionAmount,
        public DateTimeImmutable $calculationDate,
        public string $calculationMethod, // percentage, fixed, tiered
        public array $calculationDetails = [], // Additional calculation metadata
        public ?string $notes = null,
        public DateTimeImmutable $occurredAt = new DateTimeImmutable()
    ) {}

    /**
     * Get event data as array for serialization
     */
    public function toArray(): array
    {
        return [
            'employee_id' => $this->employeeId->toString(),
            'investment_id' => $this->investmentId,
            'user_id' => $this->userId,
            'commission_type' => $this->commissionType,
            'base_amount' => $this->baseAmount,
            'commission_rate' => $this->commissionRate,
            'commission_amount' => $this->commissionAmount,
            'calculation_date' => $this->calculationDate->format('Y-m-d'),
            'calculation_method' => $this->calculationMethod,
            'calculation_details' => $this->calculationDetails,
            'notes' => $this->notes,
            'occurred_at' => $this->occurredAt->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Get event name for logging/tracking
     */
    public function getEventName(): string
    {
        return 'employee.commission_calculated';
    }

    /**
     * Check if this is an investment facilitation commission
     */
    public function isInvestmentFacilitation(): bool
    {
        return $this->commissionType === 'investment_facilitation';
    }

    /**
     * Check if this is a referral commission
     */
    public function isReferral(): bool
    {
        return $this->commissionType === 'referral';
    }

    /**
     * Check if this is a performance bonus
     */
    public function isPerformanceBonus(): bool
    {
        return $this->commissionType === 'performance_bonus';
    }

    /**
     * Check if this is a retention bonus
     */
    public function isRetentionBonus(): bool
    {
        return $this->commissionType === 'retention_bonus';
    }

    /**
     * Check if commission amount is significant (>= 1000 ZMW)
     */
    public function isSignificantAmount(): bool
    {
        return $this->commissionAmount >= 1000.0;
    }

    /**
     * Validate event data integrity
     */
    public function isValid(): bool
    {
        $validCommissionTypes = ['investment_facilitation', 'referral', 'performance_bonus', 'retention_bonus'];
        $validCalculationMethods = ['percentage', 'fixed', 'tiered'];
        
        return in_array($this->commissionType, $validCommissionTypes, true)
            && in_array($this->calculationMethod, $validCalculationMethods, true)
            && $this->baseAmount >= 0
            && $this->commissionRate >= 0
            && $this->commissionAmount >= 0
            && $this->calculationDate <= $this->occurredAt
            && ($this->isInvestmentFacilitation() ? $this->investmentId !== null : true)
            && ($this->isReferral() ? $this->userId !== null : true);
    }
}