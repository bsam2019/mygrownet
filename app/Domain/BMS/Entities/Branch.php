<?php

namespace App\Domain\BMS\Entities;

use DateTimeImmutable;

class Branch
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $companyId,
        public readonly string $branchName,
        public readonly ?string $branchCode,
        public readonly ?string $address,
        public readonly ?string $phone,
        public readonly ?string $email,
        public readonly ?string $managerName,
        public readonly bool $isActive,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            companyId: (int) $data['company_id'],
            branchName: $data['branch_name'],
            branchCode: $data['branch_code'] ?? null,
            address: $data['address'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            managerName: $data['manager_name'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->companyId,
            'branch_name' => $this->branchName,
            'branch_code' => $this->branchCode,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'manager_name' => $this->managerName,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
