<?php

namespace App\Domain\Employee\Services;

use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\Repositories\TrainingRepositoryInterface;
use Illuminate\Support\Collection;

class TrainingService
{
    public function __construct(
        private TrainingRepositoryInterface $trainingRepo
    ) {}

    public function getEnrollmentsForEmployee(EmployeeId $employeeId, array $filters = []): Collection
    {
        return $this->trainingRepo->findEnrollmentsByEmployee($employeeId, $filters);
    }

    public function getActiveEnrollments(EmployeeId $employeeId): Collection
    {
        return $this->trainingRepo->findActiveEnrollments($employeeId);
    }

    public function getCertifications(EmployeeId $employeeId): Collection
    {
        return $this->trainingRepo->findCertifications($employeeId);
    }

    public function getTrainingStats(EmployeeId $employeeId): array
    {
        $enrollments = $this->trainingRepo->findEnrollmentsByEmployee($employeeId);
        $certifications = $this->trainingRepo->findCertifications($employeeId);

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

    public function updateEnrollmentProgress(int $enrollmentId, int $progress): object
    {
        $enrollment = $this->trainingRepo->findEnrollmentById($enrollmentId);

        if (!$enrollment) {
            throw new \RuntimeException('Enrollment not found');
        }

        $data = ['progress' => $progress];

        if ($enrollment->status === 'assigned' && $progress > 0) {
            $data['status'] = 'in_progress';
            $data['started_at'] = now();
        }

        if ($progress >= 100) {
            $data['status'] = 'completed';
            $data['completed_at'] = now();
        }

        $this->trainingRepo->updateEnrollment($enrollmentId, $data);

        return $this->trainingRepo->findEnrollmentById($enrollmentId);
    }

    public function getAvailableCourses(EmployeeId $employeeId): Collection
    {
        return $this->trainingRepo->findAvailableCourses($employeeId);
    }

    public function getLearningPath(EmployeeId $employeeId): array
    {
        $mandatory = $this->trainingRepo->findMandatoryEnrollments($employeeId);
        $recommended = $this->trainingRepo->findRecommendedCourses($employeeId);

        return [
            'mandatory' => $mandatory,
            'recommended' => $recommended,
        ];
    }
}