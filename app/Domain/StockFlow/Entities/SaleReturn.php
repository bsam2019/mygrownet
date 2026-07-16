<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use App\Domain\StockFlow\ValueObjects\SaleReturnId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\SaleId;
use App\Domain\StockFlow\ValueObjects\UserId;
use DateTimeImmutable;
use Illuminate\Contracts\Support\Arrayable;

class SaleReturn implements Arrayable
{
    private function __construct(
        private SaleReturnId $id, private CompanyId $companyId, private SaleId $saleId,
        private string $returnNumber, private DateTimeImmutable $returnDate, private string $reason,
        private float $totalRefund, private ?string $refundMethod, private ?string $notes,
        private UserId $createdBy, private DateTimeImmutable $createdAt, private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(CompanyId $companyId, SaleId $saleId, string $returnNumber, DateTimeImmutable $returnDate, string $reason, float $totalRefund, UserId $createdBy, ?string $refundMethod = null, ?string $notes = null): self
    {
        return new self(SaleReturnId::generate(), $companyId, $saleId, $returnNumber, $returnDate, $reason, $totalRefund, $refundMethod, $notes, $createdBy, new DateTimeImmutable(), new DateTimeImmutable());
    }

    public static function reconstitute(SaleReturnId $id, CompanyId $companyId, SaleId $saleId, string $returnNumber, DateTimeImmutable $returnDate, string $reason, float $totalRefund, ?string $refundMethod, ?string $notes, UserId $createdBy, DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt): self
    {
        return new self($id, $companyId, $saleId, $returnNumber, $returnDate, $reason, $totalRefund, $refundMethod, $notes, $createdBy, $createdAt, $updatedAt);
    }

    public function id(): int { return $this->id->toInt(); }
    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(), 'sa_company_id' => $this->companyId->toInt(),
            'sa_sale_id' => $this->saleId->toInt(), 'return_number' => $this->returnNumber,
            'return_date' => $this->returnDate->format('Y-m-d'), 'reason' => $this->reason,
            'total_refund' => $this->totalRefund, 'refund_method' => $this->refundMethod,
            'notes' => $this->notes, 'created_by' => $this->createdBy->toInt(),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
