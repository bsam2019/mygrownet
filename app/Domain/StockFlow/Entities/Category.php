<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use App\Domain\StockFlow\ValueObjects\CategoryId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use DateTimeImmutable;
use Illuminate\Contracts\Support\Arrayable;

class Category implements Arrayable
{
    private function __construct(
        private CategoryId $id, private CompanyId $companyId, private string $name, private string $slug,
        private ?string $description, private ?CategoryId $parentId, private int $sortOrder,
        private DateTimeImmutable $createdAt, private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(CompanyId $companyId, string $name, string $slug, ?string $description = null, ?CategoryId $parentId = null, int $sortOrder = 0): self
    {
        return new self(CategoryId::generate(), $companyId, $name, $slug, $description, $parentId, $sortOrder, new DateTimeImmutable(), new DateTimeImmutable());
    }

    public static function reconstitute(CategoryId $id, CompanyId $companyId, string $name, string $slug, ?string $description, ?CategoryId $parentId, int $sortOrder, DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt): self
    {
        return new self($id, $companyId, $name, $slug, $description, $parentId, $sortOrder, $createdAt, $updatedAt);
    }

    public function update(string $name, string $slug, ?string $description, ?CategoryId $parentId, int $sortOrder): void
    {
        $this->name = $name; $this->slug = $slug; $this->description = $description; $this->parentId = $parentId; $this->sortOrder = $sortOrder;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): CategoryId { return $this->id; }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getName(): string { return $this->name; }
    public function getSlug(): string { return $this->slug; }
    public function getParentId(): ?CategoryId { return $this->parentId; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(), 'sa_company_id' => $this->companyId->toInt(),
            'name' => $this->name, 'slug' => $this->slug, 'description' => $this->description,
            'parent_id' => $this->parentId?->toInt(), 'sort_order' => $this->sortOrder,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
