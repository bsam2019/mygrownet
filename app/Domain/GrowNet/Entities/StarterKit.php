<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\Entities;

use App\Domain\GrowNet\ValueObjects\MemberId;
use App\Domain\GrowNet\ValueObjects\Money;
use DateTimeImmutable;

class StarterKit
{
    public function __construct(
        private int $id,
        private MemberId $memberId,
        private string $tier,
        private string $status,
        private Money $price,
        private ?DateTimeImmutable $purchasedAt,
        private ?DateTimeImmutable $activatedAt = null,
        private ?float $shopCredit = null,
        private ?DateTimeImmutable $creditExpiry = null,
    ) {}

    public function id(): int { return $this->id; }
    public function memberId(): MemberId { return $this->memberId; }
    public function tier(): string { return $this->tier; }
    public function status(): string { return $this->status; }
    public function price(): Money { return $this->price; }
    public function purchasedAt(): ?DateTimeImmutable { return $this->purchasedAt; }

    public function isActive(): bool
    {
        return $this->status === 'active' || $this->status === 'purchased';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'member_id' => $this->memberId->value(),
            'tier' => $this->tier,
            'status' => $this->status,
            'price' => $this->price->amount(),
            'purchased_at' => $this->purchasedAt?->format('M d, Y'),
            'has_kit' => $this->isActive(),
        ];
    }
}
