<?php

declare(strict_types=1);

namespace App\Application\DTOs\Employee;

use App\Domain\Employee\Entities\Department;
use App\Domain\Employee\Entities\Position;
use App\Domain\Employee\Entities\Employee;
use App\Domain\Employee\ValueObjects\EmploymentStatus;

final class UpdateEmployeeDTO
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly ?string $phone = null,
        public readonly ?string $address = null,
        public readonly ?float $salary = null,
        public readonly ?EmploymentStatus $employmentStatus = null,
        public readonly ?Department $department = null,
        public readonly ?Position $position = null,
        public readonly ?Employee $manager = null,
        public readonly bool $removeManager = false,
        public readonly ?string $notes = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            email: $data['email'],
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            salary: $data['salary'] ?? null,
            employmentStatus: isset($data['employment_status']) 
                ? EmploymentStatus::fromString($data['employment_status']) 
                : null,
            department: $data['department'] ?? null,
            position: $data['position'] ?? null,
            manager: $data['manager'] ?? null,
            removeManager: $data['remove_manager'] ?? false,
            notes: $data['notes'] ?? null
        );
    }
}