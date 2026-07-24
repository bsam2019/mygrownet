<?php

namespace App\Domain\Investment\Entities;

class Investment
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly ?int $categoryId,
        public readonly ?int $opportunityId,
        public readonly float $amount,
        public readonly string $status,
        public readonly ?float $expectedReturn,
        public readonly ?float $totalEarned,
        public readonly ?float $interestEarned,
        public readonly ?float $roi,
        public readonly ?float $platformFee,
        public readonly ?string $tier,
        public readonly ?\DateTimeImmutable $investmentDate,
        public readonly ?\DateTimeImmutable $maturityDate,
        public readonly ?\DateTimeImmutable $lockInPeriodEnd,
        public readonly ?\DateTimeImmutable $lastPayoutDate,
        public readonly ?string $paymentProof,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            userId: (int) $data['user_id'],
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            opportunityId: isset($data['opportunity_id']) ? (int) $data['opportunity_id'] : null,
            amount: (float) $data['amount'],
            status: $data['status'] ?? 'pending',
            expectedReturn: isset($data['expected_return']) ? (float) $data['expected_return'] : null,
            totalEarned: isset($data['total_earned']) ? (float) $data['total_earned'] : null,
            interestEarned: isset($data['interest_earned']) ? (float) $data['interest_earned'] : null,
            roi: isset($data['roi']) ? (float) $data['roi'] : null,
            platformFee: isset($data['platform_fee']) ? (float) $data['platform_fee'] : null,
            tier: $data['tier'] ?? null,
            investmentDate: isset($data['investment_date']) ? new \DateTimeImmutable($data['investment_date']) : null,
            maturityDate: isset($data['maturity_date']) ? new \DateTimeImmutable($data['maturity_date']) : null,
            lockInPeriodEnd: isset($data['lock_in_period_end']) ? new \DateTimeImmutable($data['lock_in_period_end']) : null,
            lastPayoutDate: isset($data['last_payout_date']) ? new \DateTimeImmutable($data['last_payout_date']) : null,
            paymentProof: $data['payment_proof'] ?? null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'category_id' => $this->categoryId,
            'opportunity_id' => $this->opportunityId,
            'amount' => $this->amount,
            'status' => $this->status,
            'expected_return' => $this->expectedReturn,
            'total_earned' => $this->totalEarned,
            'interest_earned' => $this->interestEarned,
            'roi' => $this->roi,
            'platform_fee' => $this->platformFee,
            'tier' => $this->tier,
            'investment_date' => $this->investmentDate?->format('Y-m-d H:i:s'),
            'maturity_date' => $this->maturityDate?->format('Y-m-d'),
            'lock_in_period_end' => $this->lockInPeriodEnd?->format('Y-m-d H:i:s'),
            'last_payout_date' => $this->lastPayoutDate?->format('Y-m-d H:i:s'),
            'payment_proof' => $this->paymentProof,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
