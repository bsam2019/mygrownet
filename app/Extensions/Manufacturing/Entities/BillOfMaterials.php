<?php

declare(strict_types=1);

namespace App\Extensions\Manufacturing\Entities;

use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Extensions\Manufacturing\ValueObjects\BillOfMaterialsId;
use DateTimeImmutable;
use Illuminate\Contracts\Support\Arrayable;

class BillOfMaterials implements Arrayable
{
    /** @param BomMaterial[] $materials */
    private function __construct(
        private BillOfMaterialsId $id, private CompanyId $companyId, private ItemId $itemId,
        private string $name, private float $quantity, private string $uom, private string $status,
        private string $version, private ?string $notes, private array $materials,
        private DateTimeImmutable $createdAt, private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(CompanyId $companyId, ItemId $itemId, string $name, float $quantity, array $materials = [], string $uom = 'each', ?string $notes = null): self
    {
        return new self(BillOfMaterialsId::generate(), $companyId, $itemId, $name, $quantity, $uom, 'draft', '1.0', $notes, $materials, new DateTimeImmutable(), new DateTimeImmutable());
    }

    public static function reconstitute(BillOfMaterialsId $id, CompanyId $companyId, ItemId $itemId, string $name, float $quantity, string $uom, string $status, string $version, ?string $notes, array $materials, DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt): self
    {
        return new self($id, $companyId, $itemId, $name, $quantity, $uom, $status, $version, $notes, $materials, $createdAt, $updatedAt);
    }

    public function id(): int { return $this->id->toInt(); }
    public function getCompanyId(): int { return $this->companyId->toInt(); }
    public function getItemId(): int { return $this->itemId->toInt(); }
    public function getMaterials(): array { return $this->materials; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(), 'sa_company_id' => $this->companyId->toInt(),
            'sa_item_id' => $this->itemId->toInt(), 'name' => $this->name,
            'quantity' => $this->quantity, 'uom' => $this->uom, 'status' => $this->status,
            'version' => $this->version, 'notes' => $this->notes,
            'materials' => array_map(fn($m) => $m->toArray(), $this->materials),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}

class BomMaterial implements Arrayable
{
    public function __construct(
        public readonly int $itemId, public readonly float $quantity, public readonly string $uom = 'each',
        public readonly float $wasteFactor = 0, public readonly int $sortOrder = 0,
    ) {}

    public function toArray(): array
    {
        return ['sa_item_id' => $this->itemId, 'quantity' => $this->quantity, 'uom' => $this->uom, 'waste_factor' => $this->wasteFactor, 'sort_order' => $this->sortOrder];
    }
}
