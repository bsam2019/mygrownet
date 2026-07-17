<?php

declare(strict_types=1);

namespace App\Extensions\Restaurant\Entities;

use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Extensions\Restaurant\ValueObjects\WastageRecordId;
use DateTimeImmutable;
use Illuminate\Contracts\Support\Arrayable;

class WastageRecord implements Arrayable
{
    private function __construct(
        private WastageRecordId $id, private CompanyId $companyId, private ItemId $itemId,
        private float $quantity, private float $unitCost, private string $reason,
        private ?string $referenceType, private ?int $referenceId, private ?string $notes,
        private string $occurredAt,
        private DateTimeImmutable $createdAt, private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(CompanyId $companyId, ItemId $itemId, float $quantity, string $reason, float $unitCost = 0, ?string $referenceType = null, ?int $referenceId = null, ?string $notes = null, ?string $occurredAt = null): self
    {
        return new self(WastageRecordId::generate(), $companyId, $itemId, $quantity, $unitCost, $reason, $referenceType, $referenceId, $notes, $occurredAt ?? date('Y-m-d'), new DateTimeImmutable(), new DateTimeImmutable());
    }

    public static function reconstitute(WastageRecordId $id, CompanyId $companyId, ItemId $itemId, float $quantity, float $unitCost, string $reason, ?string $referenceType, ?int $referenceId, ?string $notes, string $occurredAt, DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt): self
    {
        return new self($id, $companyId, $itemId, $quantity, $unitCost, $reason, $referenceType, $referenceId, $notes, $occurredAt, $createdAt, $updatedAt);
    }

    public function id(): int { return $this->id->toInt(); }
    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(), 'sa_company_id' => $this->companyId->toInt(),
            'sa_item_id' => $this->itemId->toInt(), 'quantity' => $this->quantity,
            'unit_cost' => $this->unitCost, 'reason' => $this->reason,
            'reference_type' => $this->referenceType, 'reference_id' => $this->referenceId,
            'notes' => $this->notes, 'occurred_at' => $this->occurredAt,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
