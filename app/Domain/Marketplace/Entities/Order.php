<?php

namespace App\Domain\Marketplace\Entities;

use App\Domain\Marketplace\ValueObjects\OrderStatus;
use App\Domain\Marketplace\ValueObjects\DeliveryMethod;
use App\Domain\Marketplace\ValueObjects\Money;

class Order
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $orderNumber,
        public readonly int $buyerId,
        public readonly int $sellerId,
        public readonly OrderStatus $status,
        public readonly Money $subtotal,
        public readonly Money $deliveryFee,
        public readonly Money $total,
        public readonly DeliveryMethod $deliveryMethod,
        public readonly array $deliveryAddress,
        public readonly ?string $deliveryNotes,
        public readonly array $items,
        public readonly ?\DateTimeImmutable $deliveredAt = null,
        public readonly ?\DateTimeImmutable $confirmedAt = null,
        public readonly ?\DateTimeImmutable $createdAt = null,
    ) {}

    public function canBeShipped(): bool
    {
        return $this->status->isPaid();
    }

    public function canBeDelivered(): bool
    {
        return $this->status->isShipped();
    }

    public function canBeConfirmed(): bool
    {
        return $this->status->isDelivered();
    }

    public function canBeCancelled(): bool
    {
        return $this->status->isPending() || $this->status->isPaid();
    }

    public function canBeDisputed(): bool
    {
        return $this->status->isDelivered() && $this->confirmedAt === null;
    }

    public function shouldAutoRelease(): bool
    {
        if (!$this->status->isDelivered() || $this->deliveredAt === null) {
            return false;
        }

        $daysSinceDelivery = $this->deliveredAt->diff(new \DateTimeImmutable())->days;
        return $daysSinceDelivery >= 7;
    }
}
