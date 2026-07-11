<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use App\Domain\StockFlow\ValueObjects\StockMovementId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\BinId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Domain\StockFlow\ValueObjects\MovementType;
use DateTimeImmutable;

class StockMovement
{
    private function __construct(
        private StockMovementId $id,
        private CompanyId $companyId,
        private ItemId $itemId,
        private ?BinId $binId,
        private MovementType $type,
        private float $quantity,
        private Money $unitPrice,
        private Money $totalValue,
        private float $quantityBefore,
        private float $quantityAfter,
        private ?string $reason,
        private ?string $referenceType,
        private ?int $referenceId,
        private int $createdBy,
        private DateTimeImmutable $createdAt,
    ) {}

    public static function create(
        CompanyId $companyId, ItemId $itemId, ?BinId $binId, MovementType $type,
        float $quantity, Money $unitPrice, float $quantityBefore, float $quantityAfter,
        ?string $reason = null, ?string $referenceType = null, ?int $referenceId = null,
        int $createdBy = 0,
    ): self {
        return new self(
            StockMovementId::generate(), $companyId, $itemId, $binId, $type, $quantity,
            $unitPrice, $unitPrice->multiply(abs($quantity)), $quantityBefore, $quantityAfter,
            $reason, $referenceType, $referenceId, $createdBy, new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        StockMovementId $id, CompanyId $companyId, ItemId $itemId, ?BinId $binId,
        MovementType $type, float $quantity, Money $unitPrice, Money $totalValue,
        float $quantityBefore, float $quantityAfter, ?string $reason,
        ?string $referenceType, ?int $referenceId, int $createdBy, DateTimeImmutable $createdAt,
    ): self {
        return new self($id, $companyId, $itemId, $binId, $type, $quantity, $unitPrice, $totalValue, $quantityBefore, $quantityAfter, $reason, $referenceType, $referenceId, $createdBy, $createdAt);
    }

    public function id(): int { return $this->id->toInt(); }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getItemId(): ItemId { return $this->itemId; }
    public function getBinId(): ?BinId { return $this->binId; }
    public function getType(): MovementType { return $this->type; }
    public function getQuantity(): float { return $this->quantity; }
    public function getUnitPrice(): Money { return $this->unitPrice; }
    public function getTotalValue(): Money { return $this->totalValue; }
    public function getQuantityBefore(): float { return $this->quantityBefore; }
    public function getQuantityAfter(): float { return $this->quantityAfter; }
    public function getReason(): ?string { return $this->reason; }
    public function getReferenceType(): ?string { return $this->referenceType; }
    public function getReferenceId(): ?int { return $this->referenceId; }
    public function getCreatedBy(): int { return $this->createdBy; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'sa_item_id' => $this->itemId->toInt(),
            'sa_bin_id' => $this->binId?->toInt(),
            'type' => $this->type->value(),
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice->toFloat(),
            'total_value' => $this->totalValue->toFloat(),
            'quantity_before' => $this->quantityBefore,
            'quantity_after' => $this->quantityAfter,
            'reason' => $this->reason,
            'reference_type' => $this->referenceType,
            'reference_id' => $this->referenceId,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
