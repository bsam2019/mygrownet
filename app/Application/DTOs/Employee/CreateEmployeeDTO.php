<?php

declare(strict_types=1);

namespace App\Application\DTOs\Employee;

use App\Domain\Employee\Entities\Department;
use App\Domain\Employee\Entities\Position;
use App\Domain\Employee\Entities\Employee;

final class CreateEmployeeDTO
{
    public function __construct(
        public readonly string $employeeNumber,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $hireDate,
        public readonly Department $department,
        public readonly Position $position,
        public readonly float $baseSalary,
        public readonly ?string $phone = null,
        public readonly ?string $address = null,
        public readonly ?Employee $manager = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            employeeNumber: $data['employee_number'],
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            email: $data['email'],
            hireDate: $data['hire_date'],
            department: $data['department'],
            position: $data['position'],
            baseSalary: $data['base_salary'],
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            manager: $data['manager'] ?? null
        );
    }
}