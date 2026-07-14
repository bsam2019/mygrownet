<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use Illuminate\Contracts\Support\Arrayable;

use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\CompanyRoleId;
use App\Domain\StockFlow\ValueObjects\CompanyUserId;
use DateTimeImmutable;

class CompanyUser implements Arrayable
{
    private function __construct(
        private CompanyUserId $id,
        private CompanyId $companyId,
        private int $userId,
        private ?CompanyRoleId $roleId,
        private string $status, // pending, active, suspended, removed
        private ?DateTimeImmutable $invitedAt,
        private ?DateTimeImmutable $joinedAt,
        private ?DateTimeImmutable $removedAt,
        private ?string $removalReason,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function invite(
        CompanyId $companyId,
        int $userId,
        ?CompanyRoleId $roleId = null,
    ): self {
        return new self(
            id: CompanyUserId::generate(),
            companyId: $companyId,
            userId: $userId,
            roleId: $roleId,
            status: 'pending',
            invitedAt: new DateTimeImmutable(),
            joinedAt: null,
            removedAt: null,
            removalReason: null,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        CompanyUserId $id,
        CompanyId $companyId,
        int $userId,
        ?CompanyRoleId $roleId,
        string $status,
        ?DateTimeImmutable $invitedAt,
        ?DateTimeImmutable $joinedAt,
        ?DateTimeImmutable $removedAt,
        ?string $removalReason,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $companyId, $userId, $roleId, $status, $invitedAt, $joinedAt, $removedAt, $removalReason, $createdAt, $updatedAt);
    }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): CompanyUserId { return $this->id; }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getUserId(): int { return $this->userId; }
    public function getRoleId(): ?CompanyRoleId { return $this->roleId; }
    public function getStatus(): string { return $this->status; }
    public function getInvitedAt(): ?DateTimeImmutable { return $this->invitedAt; }
    public function getJoinedAt(): ?DateTimeImmutable { return $this->joinedAt; }
    public function getRemovedAt(): ?DateTimeImmutable { return $this->removedAt; }
    public function getRemovalReason(): ?string { return $this->removalReason; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function isPending(): bool { return $this->status === 'pending'; }
    public function isActive(): bool { return $this->status === 'active'; }
    public function isSuspended(): bool { return $this->status === 'suspended'; }
    public function isRemoved(): bool { return $this->status === 'removed'; }

    public function accept(?CompanyRoleId $roleId = null): void
    {
        $this->status = 'active';
        $this->joinedAt = new DateTimeImmutable();
        if ($roleId) {
            $this->roleId = $roleId;
        }
        $this->updatedAt = new DateTimeImmutable();
    }

    public function suspend(): void
    {
        $this->status = 'suspended';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function activate(): void
    {
        $this->status = 'active';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function remove(?string $reason = null): void
    {
        $this->status = 'removed';
        $this->removedAt = new DateTimeImmutable();
        $this->removalReason = $reason;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function assignRole(?CompanyRoleId $roleId): void
    {
        $this->roleId = $roleId;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'user_id' => $this->userId,
            'sa_company_role_id' => $this->roleId?->toInt(),
            'status' => $this->status,
            'invited_at' => $this->invitedAt?->format('Y-m-d H:i:s'),
            'joined_at' => $this->joinedAt?->format('Y-m-d H:i:s'),
            'removed_at' => $this->removedAt?->format('Y-m-d H:i:s'),
            'removal_reason' => $this->removalReason,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}