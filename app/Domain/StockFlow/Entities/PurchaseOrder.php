<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use App\Domain\StockFlow\ValueObjects\PurchaseOrderId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\SupplierId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Domain\StockFlow\ValueObjects\PurchaseOrderStatus;
use DateTimeImmutable;

class PurchaseOrder
{
    private function __construct(
        private PurchaseOrderId $id,
        private CompanyId $companyId,
        private ?SupplierId $supplierId,
        private string $orderNumber,
        private DateTimeImmutable $orderDate,
        private PurchaseOrderStatus $status,
        private Money $subtotal,
        private Money $tax,
        private Money $total,
        private ?string $notes,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
        private array $items = [],
    ) {}

    public static function create(
        CompanyId $companyId, ?SupplierId $supplierId, string $orderNumber,
        DateTimeImmutable $orderDate, Money $subtotal, ?string $notes = null,
    ): self {
        return new self(
            PurchaseOrderId::generate(), $companyId, $supplierId, $orderNumber,
            $orderDate, PurchaseOrderStatus::draft(), $subtotal,
            Money::zero(), $subtotal, $notes,
            new DateTimeImmutable(), new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        PurchaseOrderId $id, CompanyId $companyId, ?SupplierId $supplierId,
        string $orderNumber, DateTimeImmutable $orderDate, PurchaseOrderStatus $status,
        Money $subtotal, Money $tax, Money $total, ?string $notes,
        DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $companyId, $supplierId, $orderNumber, $orderDate,
            $status, $subtotal, $tax, $total, $notes, $createdAt, $updatedAt);
    }

    public function addItem(PurchaseOrderItem $item): void { $this->items[] = $item; }
    public function receive(): void { $this->status = PurchaseOrderStatus::received(); $this->updatedAt = new DateTimeImmutable(); }
    public function markPartial(): void { $this->status = PurchaseOrderStatus::partial(); $this->updatedAt = new DateTimeImmutable(); }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): PurchaseOrderId { return $this->id; }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getSupplierId(): ?SupplierId { return $this->supplierId; }
    public function getOrderNumber(): string { return $this->orderNumber; }
    public function getOrderDate(): DateTimeImmutable { return $this->orderDate; }
    public function getStatus(): PurchaseOrderStatus { return $this->status; }
    public function getSubtotal(): Money { return $this->subtotal; }
    public function getTotal(): Money { return $this->total; }
    public function getNotes(): ?string { return $this->notes; }
    public function getItems(): array { return $this->items; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'sa_supplier_id' => $this->supplierId?->toInt(),
            'order_number' => $this->orderNumber,
            'order_date' => $this->orderDate->format('Y-m-d'),
            'status' => $this->status->value(),
            'subtotal' => $this->subtotal->toFloat(),
            'tax' => $this->tax->toFloat(),
            'total' => $this->total->toFloat(),
            'notes' => $this->notes,
            'items' => array_map(fn(PurchaseOrderItem $i) => $i->toArray(), $this->items),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
