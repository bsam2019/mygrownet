<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use App\Domain\StockFlow\ValueObjects\PurchaseRequisitionId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\UserId;
use DateTimeImmutable;
use Illuminate\Contracts\Support\Arrayable;

class PurchaseRequisition implements Arrayable
{
    private function __construct(
        private PurchaseRequisitionId $id,
        private CompanyId $companyId,
        private string $requisitionNumber,
        private UserId $requestedBy,
        private ?UserId $approvedBy,
        private ?DateTimeImmutable $dateRequired,
        private string $status,
        private ?string $notes,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(CompanyId $companyId, string $requisitionNumber, UserId $requestedBy, ?DateTimeImmutable $dateRequired = null, ?string $notes = null): self
    {
        return new self(PurchaseRequisitionId::generate(), $companyId, $requisitionNumber, $requestedBy, null, $dateRequired, 'pending', $notes, new DateTimeImmutable(), new DateTimeImmutable());
    }

    public static function reconstitute(PurchaseRequisitionId $id, CompanyId $companyId, string $requisitionNumber, UserId $requestedBy, ?UserId $approvedBy, ?DateTimeImmutable $dateRequired, string $status, ?string $notes, DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt): self
    {
        return new self($id, $companyId, $requisitionNumber, $requestedBy, $approvedBy, $dateRequired, $status, $notes, $createdAt, $updatedAt);
    }

    public function approve(UserId $approvedBy): void
    {
        $this->approvedBy = $approvedBy;
        $this->status = 'approved';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function reject(): void
    {
        $this->status = 'rejected';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): PurchaseRequisitionId { return $this->id; }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getRequisitionNumber(): string { return $this->requisitionNumber; }
    public function getStatus(): string { return $this->status; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'requisition_number' => $this->requisitionNumber,
            'requested_by' => $this->requestedBy->toInt(),
            'approved_by' => $this->approvedBy?->toInt(),
            'date_required' => $this->dateRequired?->format('Y-m-d'),
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
