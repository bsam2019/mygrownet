<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Entities;

use DateTimeImmutable;

class InventoryAlert
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $itemId,
        public readonly int $userId,
        public readonly string $type,
        public readonly int $thresholdValue,
        public readonly int $currentValue,
        public readonly bool $isAcknowledged,
        public readonly ?DateTimeImmutable $acknowledgedAt,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            itemId: (int) $data['item_id'],
            userId: (int) $data['user_id'],
            type: $data['type'],
            thresholdValue: (int) $data['threshold_value'],
            currentValue: (int) $data['current_value'],
            isAcknowledged: (bool) ($data['is_acknowledged'] ?? false),
            acknowledgedAt: isset($data['acknowledged_at']) ? new DateTimeImmutable($data['acknowledged_at']) : null,
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
            'threshold_value' => $this->thresholdValue,
            'current_value' => $this->currentValue,
            'is_acknowledged' => $this->isAcknowledged,
            'acknowledged_at' => $this->acknowledgedAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
