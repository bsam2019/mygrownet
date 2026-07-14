<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use Illuminate\Contracts\Support\Arrayable;

use App\Domain\StockFlow\ValueObjects\AuditItemId;
use App\Domain\StockFlow\ValueObjects\AuditId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\BinId;
use App\Domain\StockFlow\ValueObjects\Money;
use DateTimeImmutable;

class AuditItem implements Arrayable
{
    private function __construct(
        private AuditItemId $id,
        private AuditId $auditId,
        private ItemId $itemId,
        private ?BinId $binId,
        private string $itemName,
        private Money $unitPrice,
        private float $systemQty,
        private float $physicalQty,
        private Money $systemValue,
        private Money $physicalValue,
        private float $gapQty,
        private Money $gapValue,
        private DateTimeImmutable $createdAt,
    ) {}

    public static function create(AuditId $auditId, ItemId $itemId, ?BinId $binId, string $itemName, Money $unitPrice, float $systemQty, float $physicalQty): self
    {
        $gapQty = $systemQty - $physicalQty;
        $systemValue = $unitPrice->multiply($systemQty);
        $physicalValue = $unitPrice->multiply($physicalQty);
        $gapValue = $systemValue->subtract($physicalValue);
        return new self(AuditItemId::generate(), $auditId, $itemId, $binId, $itemName, $unitPrice, $systemQty, $physicalQty, $systemValue, $physicalValue, $gapQty, $gapValue, new DateTimeImmutable());
    }

    public static function reconstitute(AuditItemId $id, AuditId $auditId, ItemId $itemId, ?BinId $binId, string $itemName, Money $unitPrice, float $systemQty, float $physicalQty, Money $systemValue, Money $physicalValue, float $gapQty, Money $gapValue, DateTimeImmutable $createdAt): self
    {
        return new self($id, $auditId, $itemId, $binId, $itemName, $unitPrice, $systemQty, $physicalQty, $systemValue, $physicalValue, $gapQty, $gapValue, $createdAt);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_audit_id' => $this->auditId->toInt(),
            'sa_item_id' => $this->itemId->toInt(),
            'sa_bin_id' => $this->binId?->toInt(),
            'item_name' => $this->itemName,
            'unit_price' => $this->unitPrice->toFloat(),
            'system_qty' => $this->systemQty,
            'physical_qty' => $this->physicalQty,
            'system_value' => $this->systemValue->toFloat(),
            'physical_value' => $this->physicalValue->toFloat(),
            'gap_qty' => $this->gapQty,
            'gap_value' => $this->gapValue->toFloat(),
        ];
    }
}
