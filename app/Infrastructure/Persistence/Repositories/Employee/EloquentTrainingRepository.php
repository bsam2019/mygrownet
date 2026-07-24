<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\Employee;

use App\Domain\Employee\Repositories\TrainingRepositoryInterface;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Models\Employee\EmployeeTrainingCourse;
use App\Models\Employee\EmployeeCourseEnrollment;
use App\Models\Employee\EmployeeCertification;
use Illuminate\Support\Collection;

class EloquentTrainingRepository implements TrainingRepositoryInterface
{
    public function findEnrollmentsByEmployee(EmployeeId $employeeId, array $filters = []): Collection
    {
        $query = EmployeeCourseEnrollment::forEmployee($employeeId->value())
            ->with('course');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('assigned_date', 'desc')->get();
    }

    public function findActiveEnrollments(EmployeeId $employeeId): Collection
    {
        return EmployeeCourseEnrollment::forEmployee($employeeId->value())
            ->inProgress()
            ->with('course')
            ->orderBy('due_date')
            ->get();
    }

    public function findCertifications(EmployeeId $employeeId): Collection
    {
        return EmployeeCertification::forEmployee($employeeId->value())
            ->orderBy('issue_date', 'desc')
            ->get();
    }

    public function findAvailableCourses(EmployeeId $employeeId): Collection
    {
        $enrolledCourseIds = EmployeeCourseEnrollment::forEmployee($employeeId->value())
            ->pluck('course_id');

        return EmployeeTrainingCourse::active()
            ->whereNotIn('id', $enrolledCourseIds)
            ->orderBy('title')
            ->get();
    }

    public function findEnrollmentById(int $id): ?EmployeeCourseEnrollment
    {
        return EmployeeCourseEnrollment::find($id);
    }

    public function updateEnrollment(int $id, array $data): ?EmployeeCourseEnrollment
    {
        $enrollment = EmployeeCourseEnrollment::find($id);
        if (!$enrollment) {
            return null;
        }
        $enrollment->update($data);
        return $enrollment;
    }

    public function findMandatoryEnrollments(EmployeeId $employeeId): Collection
    {
        return EmployeeCourseEnrollment::forEmployee($employeeId->value())
            ->whereHas('course', fn($q) => $q->where('is_mandatory', true))
            ->inProgress()
            ->with('course')
            ->get();
    }

    public function findRecommendedCourses(EmployeeId $employeeId, int $limit = 5): Collection
    {
        $enrolledCourseIds = EmployeeCourseEnrollment::forEmployee($employeeId->value())
            ->pluck('course_id');

        return EmployeeTrainingCourse::active()
            ->where('is_mandatory', false)
            ->whereNotIn('id', $enrolledCourseIds)
            ->limit($limit)
            ->get();
    }
}