<?php

namespace App\Domain\Employee\Services;

use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Models\EmployeeTrainingCourse;
use App\Models\EmployeeCourseEnrollment;
use App\Models\EmployeeCertification;
use Illuminate\Support\Collection;

class TrainingService
{
    public function getEnrollmentsForEmployee(EmployeeId $employeeId, array $filters = []): Collection
    {
        $query = EmployeeCourseEnrollment::forEmployee($employeeId->value())
            ->with('course');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('assigned_date', 'desc')->get();
    }

    public function getActiveEnrollments(EmployeeId $employeeId): Collection
    {
        return EmployeeCourseEnrollment::forEmployee($employeeId->value())
            ->inProgress()
            ->with('course')
            ->orderBy('due_date')
            ->get();
    }

    public function getCertifications(EmployeeId $employeeId): Collection
    {
        return EmployeeCertification::forEmployee($employeeId->value())
            ->orderBy('issue_date', 'desc')
            ->get();
    }

    public function getTrainingStats(EmployeeId $employeeId): array
    {
        $enrollments = EmployeeCourseEnrollment::forEmployee($employeeId->value())->get();
        $certifications = EmployeeCertification::forEmployee($employeeId->value())->get();

        return [
            'total_courses' => $enrollments->count(),
            'completed_courses' => $enrollments->where('status', 'completed')->count(),
            'in_progress' => $enrollments->whereIn('status', ['assigned', 'in_progress'])->count(),
            'overdue' => $enrollments->filter(fn($e) => 
                $e->due_date && $e->due_date < now() && in_array($e->status, ['assigned', 'in_progress'])
            )->count(),
            'total_certifications' => $certifications->count(),
            'valid_certifications' => $certifications->filter(fn($c) => 
                !$c->expiry_date || $c->expiry_date >= now()
            )->count(),
            'expiring_soon' => $certifications->filter(fn($c) => 
                $c->expiry_date && $c->expiry_date >= now() && $c->expiry_date <= now()->addDays(30)
            )->count(),
            'average_score' => $enrollments->where('status', 'completed')->avg('score'),
        ];
    }

    public function updateEnrollmentProgress(int $enrollmentId, int $progress): EmployeeCourseEnrollment
    {
        $enrollment = EmployeeCourseEnrollment::findOrFail($enrollmentId);

        $data = ['progress' => $progress];

        if ($enrollment->status === 'assigned' && $progress > 0) {
            $data['status'] = 'in_progress';
            $data['started_at'] = now();
        }

        if ($progress >= 100) {
            $data['status'] = 'completed';
            $data['completed_at'] = now();
        }

        $enrollment->update($data);

        return $enrollment;
    }

    public function getAvailableCourses(EmployeeId $employeeId): Collection
    {
        $enrolledCourseIds = EmployeeCourseEnrollment::forEmployee($employeeId->value())
            ->pluck('course_id');

        return EmployeeTrainingCourse::active()
            ->whereNotIn('id', $enrolledCourseIds)
            ->orderBy('title')
            ->get();
    }

    public function getLearningPath(EmployeeId $employeeId): array
    {
        $mandatory = EmployeeCourseEnrollment::forEmployee($employeeId->value())
            ->whereHas('course', fn($q) => $q->where('is_mandatory', true))
            ->inProgress()
            ->with('course')
            ->get();

        $recommended = EmployeeTrainingCourse::active()
            ->where('is_mandatory', false)
            ->whereNotIn('id', EmployeeCourseEnrollment::forEmployee($employeeId->value())->pluck('course_id'))
            ->limit(5)
            ->get();

        return [
            'mandatory' => $mandatory,
            'recommended' => $recommended,
        ];
    }
}
