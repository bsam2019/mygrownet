<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use Illuminate\Contracts\Support\Arrayable;

use App\Domain\StockFlow\ValueObjects\TransferId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\WarehouseId;
use App\Domain\StockFlow\ValueObjects\UserId;
use DateTimeImmutable;

class Transfer implements Arrayable
{
    private function __construct(
        private TransferId $id,
        private CompanyId $companyId,
        private string $transferNumber,
        private WarehouseId $fromWarehouseId,
        private WarehouseId $toWarehouseId,
        private string $status,
        private ?int $transferredBy,
        private ?int $receivedBy,
        private ?string $notes,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
        private array $items = [],
    ) {}

    public static function create(
        CompanyId $companyId,
        string $transferNumber,
        WarehouseId $fromWarehouseId,
        WarehouseId $toWarehouseId,
        ?int $transferredBy = null,
        ?string $notes = null,
    ): self {
        return new self(
            TransferId::generate(),
            $companyId,
            $transferNumber,
            $fromWarehouseId,
            $toWarehouseId,
            'draft',
            $transferredBy,
            null,
            $notes,
            new DateTimeImmutable(),
            new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        TransferId $id,
        CompanyId $companyId,
        string $transferNumber,
        WarehouseId $fromWarehouseId,
        WarehouseId $toWarehouseId,
        string $status,
        ?int $transferredBy,
        ?int $receivedBy,
        ?string $notes,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $companyId, $transferNumber, $fromWarehouseId, $toWarehouseId, $status, $transferredBy, $receivedBy, $notes, $createdAt, $updatedAt);
    }

    public function addItem(TransferItem $item): void
    {
        $this->items[] = $item;
    }

    public function send(): void
    {
        $this->status = 'in_transit';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function receive(?int $receivedBy = null): void
    {
        $this->status = 'received';
        $this->receivedBy = $receivedBy;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function cancel(): void
    {
        $this->status = 'cancelled';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isDraft(): bool { return $this->status === 'draft'; }
    public function isInTransit(): bool { return $this->status === 'in_transit'; }
    public function isReceived(): bool { return $this->status === 'received'; }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): TransferId { return $this->id; }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getTransferNumber(): string { return $this->transferNumber; }
    public function getFromWarehouseId(): WarehouseId { return $this->fromWarehouseId; }
    public function getToWarehouseId(): WarehouseId { return $this->toWarehouseId; }
    public function getStatus(): string { return $this->status; }
    public function getTransferredBy(): ?int { return $this->transferredBy; }
    public function getReceivedBy(): ?int { return $this->receivedBy; }
    public function getNotes(): ?string { return $this->notes; }
    public function getItems(): array { return $this->items; }
    public function setTransferredBy(?int $userId): void { $this->transferredBy = $userId; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'transfer_number' => $this->transferNumber,
            'from_warehouse_id' => $this->fromWarehouseId->toInt(),
            'to_warehouse_id' => $this->toWarehouseId->toInt(),
            'status' => $this->status,
            'transferred_by' => $this->transferredBy,
            'received_by' => $this->receivedBy,
            'notes' => $this->notes,
            'items' => array_map(fn(TransferItem $i) => $i->toArray(), $this->items),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
