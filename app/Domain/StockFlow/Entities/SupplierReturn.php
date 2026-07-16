<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use App\Domain\StockFlow\ValueObjects\SupplierReturnId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\SupplierId;
use App\Domain\StockFlow\ValueObjects\PurchaseOrderId;
use App\Domain\StockFlow\ValueObjects\UserId;
use DateTimeImmutable;
use Illuminate\Contracts\Support\Arrayable;

class SupplierReturn implements Arrayable
{
    private function __construct(
        private SupplierReturnId $id, private CompanyId $companyId, private SupplierId $supplierId,
        private ?PurchaseOrderId $purchaseOrderId, private string $returnNumber, private DateTimeImmutable $returnDate,
        private string $reason, private float $totalRefund, private ?string $notes, private UserId $createdBy,
        private DateTimeImmutable $createdAt, private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(CompanyId $companyId, SupplierId $supplierId, string $returnNumber, DateTimeImmutable $returnDate, string $reason, float $totalRefund, UserId $createdBy, ?PurchaseOrderId $purchaseOrderId = null, ?string $notes = null): self
    {
        return new self(SupplierReturnId::generate(), $companyId, $supplierId, $purchaseOrderId, $returnNumber, $returnDate, $reason, $totalRefund, $notes, $createdBy, new DateTimeImmutable(), new DateTimeImmutable());
    }

    public static function reconstitute(SupplierReturnId $id, CompanyId $companyId, SupplierId $supplierId, ?PurchaseOrderId $purchaseOrderId, string $returnNumber, DateTimeImmutable $returnDate, string $reason, float $totalRefund, ?string $notes, UserId $createdBy, DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt): self
    {
        return new self($id, $companyId, $supplierId, $purchaseOrderId, $returnNumber, $returnDate, $reason, $totalRefund, $notes, $createdBy, $createdAt, $updatedAt);
    }

    public function id(): int { return $this->id->toInt(); }
    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(), 'sa_company_id' => $this->companyId->toInt(),
            'sa_supplier_id' => $this->supplierId->toInt(), 'sa_purchase_order_id' => $this->purchaseOrderId?->toInt(),
            'return_number' => $this->returnNumber, 'return_date' => $this->returnDate->format('Y-m-d'),
            'reason' => $this->reason, 'total_refund' => $this->totalRefund, 'notes' => $this->notes,
            'created_by' => $this->createdBy->toInt(),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
