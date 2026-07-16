<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use App\Domain\StockFlow\ValueObjects\LotId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use DateTimeImmutable;
use Illuminate\Contracts\Support\Arrayable;

class Lot implements Arrayable
{
    private function __construct(
        private LotId $id,
        private CompanyId $companyId,
        private ItemId $itemId,
        private string $lotNumber,
        private ?DateTimeImmutable $manufacturingDate,
        private ?DateTimeImmutable $expiryDate,
        private ?DateTimeImmutable $receivedDate,
        private float $initialQuantity,
        private float $currentQuantity,
        private string $status,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        CompanyId $companyId, ItemId $itemId, string $lotNumber, float $quantity,
        ?DateTimeImmutable $manufacturingDate = null, ?DateTimeImmutable $expiryDate = null, ?DateTimeImmutable $receivedDate = null
    ): self {
        return new self(LotId::generate(), $companyId, $itemId, $lotNumber, $manufacturingDate, $expiryDate, $receivedDate, $quantity, $quantity, 'active', new DateTimeImmutable(), new DateTimeImmutable());
    }

    public static function reconstitute(LotId $id, CompanyId $companyId, ItemId $itemId, string $lotNumber, ?DateTimeImmutable $manufacturingDate, ?DateTimeImmutable $expiryDate, ?DateTimeImmutable $receivedDate, float $initialQuantity, float $currentQuantity, string $status, DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt): self
    {
        return new self($id, $companyId, $itemId, $lotNumber, $manufacturingDate, $expiryDate, $receivedDate, $initialQuantity, $currentQuantity, $status, $createdAt, $updatedAt);
    }

    public function adjustQuantity(float $newQuantity): void
    {
        $this->currentQuantity = $newQuantity;
        if ($this->currentQuantity <= 0) $this->status = 'depleted';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): LotId { return $this->id; }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getItemId(): ItemId { return $this->itemId; }
    public function getLotNumber(): string { return $this->lotNumber; }
    public function getCurrentQuantity(): float { return $this->currentQuantity; }
    public function getStatus(): string { return $this->status; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'sa_item_id' => $this->itemId->toInt(),
            'lot_number' => $this->lotNumber,
            'manufacturing_date' => $this->manufacturingDate?->format('Y-m-d'),
            'expiry_date' => $this->expiryDate?->format('Y-m-d'),
            'received_date' => $this->receivedDate?->format('Y-m-d'),
            'initial_quantity' => $this->initialQuantity,
            'current_quantity' => $this->currentQuantity,
            'status' => $this->status,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
