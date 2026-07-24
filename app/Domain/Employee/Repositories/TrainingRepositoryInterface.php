<?php

declare(strict_types=1);

namespace App\Domain\Employee\Repositories;

use App\Domain\Employee\ValueObjects\EmployeeId;
use Illuminate\Support\Collection;

interface TrainingRepositoryInterface
{
    public function findEnrollmentsByEmployee(EmployeeId $employeeId, array $filters = []): Collection;

    public function findActiveEnrollments(EmployeeId $employeeId): Collection;

    public function findCertifications(EmployeeId $employeeId): Collection;

    public function findAvailableCourses(EmployeeId $employeeId): Collection;

    public function findEnrollmentById(int $id): ?object;

    public function updateEnrollment(int $id, array $data): ?object;

    public function findMandatoryEnrollments(EmployeeId $employeeId): Collection;

    public function findRecommendedCourses(EmployeeId $employeeId, int $limit = 5): Collection;
}