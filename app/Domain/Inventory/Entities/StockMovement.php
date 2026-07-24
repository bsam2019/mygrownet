<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Entities;

use DateTimeImmutable;

class StockMovement
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $itemId,
        public readonly int $userId,
        public readonly string $type,
        public readonly int $quantity,
        public readonly int $stockBefore,
        public readonly int $stockAfter,
        public readonly ?float $unitCost,
        public readonly ?float $totalValue,
        public readonly ?string $referenceType,
        public readonly ?int $referenceId,
        public readonly ?string $notes,
        public readonly DateTimeImmutable $movementDate,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            itemId: (int) $data['item_id'],
            userId: (int) ($data['user_id'] ?? auth()->id()),
            type: $data['type'],
            quantity: (int) $data['quantity'],
            stockBefore: (int) ($data['stock_before'] ?? 0),
            stockAfter: (int) ($data['stock_after'] ?? 0),
            unitCost: isset($data['unit_cost']) ? (float) $data['unit_cost'] : null,
            totalValue: isset($data['total_value']) ? (float) $data['total_value'] : null,
            referenceType: $data['reference_type'] ?? null,
            referenceId: $data['reference_id'] ?? null,
            notes: $data['notes'] ?? null,
            movementDate: isset($data['movement_date'])
                ? new DateTimeImmutable($data['movement_date'])
                : new DateTimeImmutable(),
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'item_id' => $this->itemId,
            'user_id' => $this->userId,
            'type' => $this->type,
            'quantity' => $this->quantity,
            'stock_before' => $this->stockBefore,
            'stock_after' => $this->stockAfter,
            'unit_cost' => $this->unitCost,
            'total_value' => $this->totalValue,
            'reference_type' => $this->referenceType,
            'reference_id' => $this->referenceId,
            'notes' => $this->notes,
            'movement_date' => $this->movementDate->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    public function isIncoming(): bool
    {
        return $this->quantity > 0;
    }

    public function isOutgoing(): bool
    {
        return $this->quantity < 0;
    }
}
