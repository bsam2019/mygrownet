<?php

declare(strict_types=1);

namespace App\Application\UseCases\Employee;

use App\Domain\Employee\Entities\Employee;
use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\Exceptions\EmployeeNotFoundException;

final class GetEmployeeUseCase
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository
    ) {}

    public function execute(EmployeeId $employeeId): Employee
    {
        $employee = $this->employeeRepository->findById($employeeId);
        
        if (!$employee) {
            throw EmployeeNotFoundException::withId($employeeId->toString());
        }

        return $employee;
    }

    public function executeByEmployeeNumber(string $employeeNumber): Employee
    {
        $employee = $this->employeeRepository->findByEmployeeNumber($employeeNumber);
        
        if (!$employee) {
            throw EmployeeNotFoundException::withEmployeeNumber($employeeNumber);
        }

        return $employee;
    }

    public function executeByUserId(int $userId): Employee
    {
        $employee = $this->employeeRepository->findByUserId($userId);
        
        if (!$employee) {
            throw EmployeeNotFoundException::withUserId($userId);
        }

        return $employee;
    }
}