<?php

namespace App\Domain\Core\Entities;

use DateTimeImmutable;

class OrganizationMember
{
    public function __construct(
        public readonly string $id,
        public readonly string $organizationId,
        public readonly string $userId,
        public readonly string $role,
        public readonly string $status,
        public readonly array $permissions,
        public readonly DateTimeImmutable $joinedAt,
        public readonly DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}
}
