<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\Entities;

use App\Domain\GrowNet\ValueObjects\MemberId;
use DateTimeImmutable;

class LoyaltyPoints
{
    public function __construct(
        private int $id,
        private MemberId $memberId,
        private float $lpAmount,
        private float $bpAmount,
        private string $type,
        private string $description,
        private string $status,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $expiresAt = null,
    ) {}

    public function id(): int { return $this->id; }
    public function memberId(): MemberId { return $this->memberId; }
    public function lpAmount(): float { return $this->lpAmount; }
    public function bpAmount(): float { return $this->bpAmount; }
    public function type(): string { return $this->type; }
    public function status(): string { return $this->status; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'member_id' => $this->memberId->value(),
            'lp_amount' => $this->lpAmount,
            'bp_amount' => $this->bpAmount,
            'type' => $this->type,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
