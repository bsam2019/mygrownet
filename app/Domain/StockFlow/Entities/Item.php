<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use Illuminate\Contracts\Support\Arrayable;

use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\DepartmentId;
use App\Domain\StockFlow\ValueObjects\BinId;
use App\Domain\StockFlow\ValueObjects\Money;
use DateTimeImmutable;

class Item implements Arrayable
{
    private function __construct(
        private ItemId $id,
        private CompanyId $companyId,
        private ?DepartmentId $departmentId,
        private ?BinId $binId,
        private string $name,
        private ?string $sku,
        private ?string $description,
        private Money $unitPrice,
        private ?string $unit,
        private float $systemQuantity,
        private ?float $reorderLevel,
        private ?string $category,
        private bool $isExpirable,
        private ?DateTimeImmutable $expiryDate,
        private ?string $notes,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        CompanyId $companyId,
        ?DepartmentId $departmentId = null,
        ?BinId $binId = null,
        string $name = '',
        ?string $sku = null,
        ?string $description = null,
        Money $unitPrice = null,
        ?string $unit = null,
        float $systemQuantity = 0,
        ?float $reorderLevel = null,
        ?string $category = null,
        bool $isExpirable = false,
        ?DateTimeImmutable $expiryDate = null,
        ?string $notes = null,
    ): self {
        return new self(
            id: ItemId::generate(),
            companyId: $companyId,
            departmentId: $departmentId,
            binId: $binId,
            name: $name,
            sku: $sku,
            description: $description,
            unitPrice: $unitPrice ?? Money::zero(),
            unit: $unit,
            systemQuantity: max(0, $systemQuantity),
            reorderLevel: $reorderLevel,
            category: $category,
            isExpirable: $isExpirable,
            expiryDate: $expiryDate,
            notes: $notes,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        ItemId $id,
        CompanyId $companyId,
        ?DepartmentId $departmentId,
        ?BinId $binId,
        string $name,
        ?string $sku,
        ?string $description,
        Money $unitPrice,
        ?string $unit,
        float $systemQuantity,
        ?float $reorderLevel,
        ?string $category,
        bool $isExpirable,
        ?DateTimeImmutable $expiryDate,
        ?string $notes,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $companyId, $departmentId, $binId, $name, $sku, $description, $unitPrice, $unit, $systemQuantity, $reorderLevel, $category, $isExpirable, $expiryDate, $notes, $createdAt, $updatedAt);
    }

    public function adjustStock(float $quantity): void
    {
        $this->systemQuantity = max(0, $this->systemQuantity + $quantity);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setStock(float $quantity): void
    {
        $this->systemQuantity = max(0, $quantity);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateDetails(
        string $name,
        ?DepartmentId $departmentId,
        ?BinId $binId,
        ?string $sku,
        ?string $description,
        Money $unitPrice,
        ?string $unit,
        ?float $reorderLevel,
        ?string $category,
        ?string $notes,
    ): void {
        $this->name = $name;
        $this->departmentId = $departmentId;
        $this->binId = $binId;
        $this->sku = $sku;
        $this->description = $description;
        $this->unitPrice = $unitPrice;
        $this->unit = $unit;
        $this->reorderLevel = $reorderLevel;
        $this->category = $category;
        $this->notes = $notes;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isLowStock(): bool
    {
        return $this->reorderLevel !== null && $this->systemQuantity <= $this->reorderLevel;
    }

    public function isOutOfStock(): bool
    {
        return $this->systemQuantity <= 0;
    }

    public function getStockValue(): Money
    {
        return $this->unitPrice->multiply($this->systemQuantity);
    }

    public function hasSufficientStock(float $quantity): bool
    {
        return $this->systemQuantity >= $quantity;
    }

    // Getters
    public function id(): int { return $this->id->toInt(); }
    public function getId(): ItemId { return $this->id; }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getDepartmentId(): ?DepartmentId { return $this->departmentId; }
    public function getDepartmentIdValue(): ?int { return $this->departmentId?->toInt(); }
    public function getBinId(): ?BinId { return $this->binId; }
    public function getBinIdValue(): ?int { return $this->binId?->toInt(); }
    public function getName(): string { return $this->name; }
    public function getSku(): ?string { return $this->sku; }
    public function getDescription(): ?string { return $this->description; }
    public function getUnitPrice(): Money { return $this->unitPrice; }
    public function getUnit(): ?string { return $this->unit; }
    public function getSystemQuantity(): float { return $this->systemQuantity; }
    public function getReorderLevel(): ?float { return $this->reorderLevel; }
    public function getCategory(): ?string { return $this->category; }
    public function isExpirable(): bool { return $this->isExpirable; }
    public function getExpiryDate(): ?DateTimeImmutable { return $this->expiryDate; }
    public function getNotes(): ?string { return $this->notes; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'sa_department_id' => $this->departmentId?->toInt(),
            'sa_bin_id' => $this->binId?->toInt(),
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'unit_price' => $this->unitPrice->toFloat(),
            'unit' => $this->unit,
            'system_quantity' => $this->systemQuantity,
            'reorder_level' => $this->reorderLevel,
            'category' => $this->category,
            'is_expirable' => $this->isExpirable,
            'expiry_date' => $this->expiryDate?->format('Y-m-d'),
            'notes' => $this->notes,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
