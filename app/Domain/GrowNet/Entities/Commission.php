<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\Entities;

use App\Domain\GrowNet\ValueObjects\CommissionLevel;
use App\Domain\GrowNet\ValueObjects\MemberId;
use App\Domain\GrowNet\ValueObjects\Money;
use DateTimeImmutable;

class Commission
{
    public function __construct(
        private int $id,
        private MemberId $referrerId,
        private MemberId $referredMemberId,
        private string $referredName,
        private CommissionLevel $level,
        private Money $amount,
        private Money $originalAmount,
        private string $type,
        private string $status,
        private ?string $source,
        private ?string $description,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $paidAt = null,
    ) {}

    public static function create(
        MemberId $referrerId,
        MemberId $referredMemberId,
        string $referredName,
        CommissionLevel $level,
        Money $amount,
        Money $originalAmount,
        string $type = 'referral',
        string $source = null,
        string $description = null,
    ): self {
        return new self(
            id: 0,
            referrerId: $referrerId,
            referredMemberId: $referredMemberId,
            referredName: $referredName,
            level: $level,
            amount: $amount,
            originalAmount: $originalAmount,
            type: $type,
            status: 'pending',
            source: $source,
            description: $description,
            createdAt: new DateTimeImmutable(),
        );
    }

    public function id(): int { return $this->id; }
    public function referrerId(): MemberId { return $this->referrerId; }
    public function referredMemberId(): MemberId { return $this->referredMemberId; }
    public function referredName(): string { return $this->referredName; }
    public function level(): CommissionLevel { return $this->level; }
    public function amount(): Money { return $this->amount; }
    public function originalAmount(): Money { return $this->originalAmount; }
    public function type(): string { return $this->type; }
    public function status(): string { return $this->status; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }

    public function markAsPaid(): void
    {
        $this->status = 'paid';
        $this->paidAt = new DateTimeImmutable();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'referrer_id' => $this->referrerId->value(),
            'referred_member_id' => $this->referredMemberId->value(),
            'referred_name' => $this->referredName,
            'level' => $this->level->value,
            'amount' => $this->amount->amount(),
            'original_amount' => $this->originalAmount->amount(),
            'type' => $this->type,
            'status' => $this->status,
            'source' => $this->source,
            'description' => $this->description,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'paid_at' => $this->paidAt?->format('Y-m-d H:i:s'),
        ];
    }
}
