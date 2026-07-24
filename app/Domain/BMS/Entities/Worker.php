<?php

declare(strict_types=1);

namespace App\Domain\BMS\Entities;

use DateTimeImmutable;

class Worker
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $companyId,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly ?string $phone,
        public readonly ?string $email,
        public readonly ?string $address,
        public readonly ?string $nationalId,
        public readonly ?string $position,
        public readonly ?string $department,
        public readonly string $employmentType,
        public readonly ?float $basicPay,
        public readonly ?float $ratePerHour,
        public readonly ?string $bankName,
        public readonly ?string $bankAccount,
        public readonly ?DateTimeImmutable $dateHired,
        public readonly ?DateTimeImmutable $dateTerminated,
        public readonly string $status,
        public readonly ?string $notes,
        public readonly ?int $branchId,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            companyId: (int) $data['company_id'],
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            address: $data['address'] ?? null,
            nationalId: $data['national_id'] ?? null,
            position: $data['position'] ?? null,
            department: $data['department'] ?? null,
            employmentType: $data['employment_type'] ?? 'permanent',
            basicPay: array_key_exists('basic_pay', $data) ? (float) $data['basic_pay'] : null,
            ratePerHour: array_key_exists('rate_per_hour', $data) ? (float) $data['rate_per_hour'] : null,
            bankName: $data['bank_name'] ?? null,
            bankAccount: $data['bank_account'] ?? null,
            dateHired: isset($data['date_hired']) ? new DateTimeImmutable($data['date_hired']) : null,
            dateTerminated: isset($data['date_terminated']) ? new DateTimeImmutable($data['date_terminated']) : null,
            status: $data['status'] ?? 'active',
            notes: $data['notes'] ?? null,
            branchId: $data['branch_id'] ?? null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->companyId,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'national_id' => $this->nationalId,
            'position' => $this->position,
            'department' => $this->department,
            'employment_type' => $this->employmentType,
            'basic_pay' => $this->basicPay,
            'rate_per_hour' => $this->ratePerHour,
            'bank_name' => $this->bankName,
            'bank_account' => $this->bankAccount,
            'date_hired' => $this->dateHired?->format('Y-m-d'),
            'date_terminated' => $this->dateTerminated?->format('Y-m-d'),
            'status' => $this->status,
            'notes' => $this->notes,
            'branch_id' => $this->branchId,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
