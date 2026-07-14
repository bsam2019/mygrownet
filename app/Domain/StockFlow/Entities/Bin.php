<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use Illuminate\Contracts\Support\Arrayable;

use App\Domain\StockFlow\ValueObjects\BinId;
use App\Domain\StockFlow\ValueObjects\DepartmentId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use DateTimeImmutable;

class Bin implements Arrayable
{
    private function __construct(
        private BinId $id,
        private CompanyId $companyId,
        private DepartmentId $departmentId,
        private string $name,
        private ?string $label,
        private ?string $description,
        private int $sortOrder,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(CompanyId $companyId, DepartmentId $departmentId, string $name, ?string $label = null, ?string $description = null, int $sortOrder = 0): self
    {
        return new self(BinId::generate(), $companyId, $departmentId, $name, $label, $description, $sortOrder, new DateTimeImmutable(), new DateTimeImmutable());
    }

    public static function reconstitute(BinId $id, CompanyId $companyId, DepartmentId $departmentId, string $name, ?string $label, ?string $description, int $sortOrder, DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt): self
    {
        return new self($id, $companyId, $departmentId, $name, $label, $description, $sortOrder, $createdAt, $updatedAt);
    }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): BinId { return $this->id; }
    public function getCompanyIdValue(): int { return $this->companyId->toInt(); }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getDepartmentIdValue(): int { return $this->departmentId->toInt(); }
    public function getDepartmentId(): DepartmentId { return $this->departmentId; }
    public function getName(): string { return $this->name; }
    public function getLabel(): ?string { return $this->label; }
    public function getDescription(): ?string { return $this->description; }
    public function getSortOrder(): int { return $this->sortOrder; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'sa_department_id' => $this->departmentId->toInt(),
            'name' => $this->name,
            'label' => $this->label,
            'description' => $this->description,
            'sort_order' => $this->sortOrder,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
