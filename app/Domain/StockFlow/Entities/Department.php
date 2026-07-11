<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use App\Domain\StockFlow\ValueObjects\DepartmentId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use DateTimeImmutable;

class Department
{
    private function __construct(
        private DepartmentId $id,
        private CompanyId $companyId,
        private string $name,
        private ?string $slug,
        private ?string $description,
        private int $sortOrder,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(CompanyId $companyId, string $name, ?string $description = null, int $sortOrder = 0): self
    {
        return new self(
            id: DepartmentId::generate(),
            companyId: $companyId,
            name: $name,
            slug: null,
            description: $description,
            sortOrder: $sortOrder,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public static function reconstitute(DepartmentId $id, CompanyId $companyId, string $name, ?string $slug, ?string $description, int $sortOrder, DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt): self
    {
        return new self($id, $companyId, $name, $slug, $description, $sortOrder, $createdAt, $updatedAt);
    }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): DepartmentId { return $this->id; }
    public function getCompanyIdValue(): int { return $this->companyId->toInt(); }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getName(): string { return $this->name; }
    public function getSlug(): ?string { return $this->slug; }
    public function getDescription(): ?string { return $this->description; }
    public function getSortOrder(): int { return $this->sortOrder; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sort_order' => $this->sortOrder,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
