<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use Illuminate\Contracts\Support\Arrayable;

use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\CompanyRoleId;
use DateTimeImmutable;

class CompanyRole implements Arrayable
{
    private function __construct(
        private CompanyRoleId $id,
        private CompanyId $companyId,
        private string $name,
        private string $slug,
        private string $description,
        private array $permissions,
        private bool $isSystem,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        CompanyId $companyId,
        string $name,
        string $slug,
        string $description = '',
        array $permissions = [],
        bool $isSystem = false,
    ): self {
        return new self(
            id: CompanyRoleId::generate(),
            companyId: $companyId,
            name: $name,
            slug: $slug,
            description: $description,
            permissions: $permissions,
            isSystem: $isSystem,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public static function createSystemRole(
        CompanyId $companyId,
        string $name,
        string $slug,
        string $description,
        array $permissions,
    ): self {
        $role = self::create($companyId, $name, $slug, $description, $permissions, true);
        return $role;
    }

    public static function reconstitute(
        CompanyRoleId $id,
        CompanyId $companyId,
        string $name,
        string $slug,
        string $description,
        array $permissions,
        bool $isSystem,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $companyId, $name, $slug, $description, $permissions, $isSystem, $createdAt, $updatedAt);
    }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): CompanyRoleId { return $this->id; }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getName(): string { return $this->name; }
    public function getSlug(): string { return $this->slug; }
    public function getDescription(): string { return $this->description; }
    public function getPermissions(): array { return $this->permissions; }
    public function isSystem(): bool { return $this->isSystem; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }

    public function addPermission(string $permission): void
    {
        if (!$this->hasPermission($permission)) {
            $this->permissions[] = $permission;
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function removePermission(string $permission): void
    {
        $this->permissions = array_values(array_filter($this->permissions, fn($p) => $p !== $permission));
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateDetails(string $name, string $description): void
    {
        if (!$this->isSystem) {
            $this->name = $name;
            $this->description = $description;
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'permissions' => $this->permissions,
            'is_system' => $this->isSystem,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}