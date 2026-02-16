<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\WorkerModel;
use App\Infrastructure\Persistence\Eloquent\CMS\AttendanceRecordModel;
use App\Infrastructure\Persistence\Eloquent\CMS\LeaveRequestModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PayrollRunModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PerformanceReviewModel;
use App\Infrastructure\Persistence\Eloquent\CMS\TrainingEnrollmentModel;
use Illuminate\Support\Facades\DB;

class HRReportsService
{
    /**
     * Generate headcount report
     */
    public function generateHeadcountReport(int $companyId, array $filters = []): array
    {
        $query = WorkerModel::where('company_id', $companyId);

        // Apply filters
        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }
        if (!empty($filters['employment_type'])) {
            $query->where('employment_type', $filters['employment_type']);
        }
        if (!empty($filters['employment_status'])) {
            $query->where('employment_status', $filters['employment_status']);
        }

        $workers = $query->with(['department'])->get();

        // Calculate statistics
        $stats = [
            'total_workers' => $workers->count(),
            'by_type' => $workers->groupBy('employment_type')->map->count(),
            'by_status' => $workers->groupBy('employment_status')->map->count(),
            'by_department' => $workers->groupBy('department.name')->map->count(),
            'by_gender' => $workers->groupBy('gender')->map->count(),
            'average_tenure_months' => $workers->avg(function ($worker) {
                return $worker->hire_date ? now()->diffInMonths($worker->hire_date) : 0;
            }),
        ];

        return [
            'stats' => $stats,
            'workers' => $workers->map(function ($worker) {
                return [
                    'id' => $worker->id,
                    'name' => $worker->first_name . ' ' . $worker->last_name,
                    'job_title' => $worker->job_title,
                    'department' => $worker->department?->name,
                    'employment_type' => $worker->employment_type,
                    'employment_status' => $worker->employment_status,
                    'hire_date' => $worker->hire_date?->format('Y-m-d'),
                    'tenure_months' => $worker->hire_date ? now()->diffInMonths($worker->hire_date) : 0,
                ];
            }),
        ];
    }

    /**
     * Generate attendance report
     */
    public function generateAttendanceReport(int $companyId, array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? now()->startOfMonth();
        $dateTo = $filters['date_to'] ?? now()->endOfMonth();

        $query = AttendanceRecordModel::whereHas('worker', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->whereBetween('date', [$dateFrom, $dateTo]);

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }
        if (!empty($filters['department_id'])) {
            $query->whereHas('worker', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }

        $records = $query->with(['worker.department'])->get();

        // Calculate statistics
        $stats = [
            'total_records' => $records->count(),
            'present_count' => $records->where('status', 'present')->count(),
            'late_count' => $records->where('status', 'late')->count(),
            'absent_count' => $records->where('status', 'absent')->count(),
            'half_day_count' => $records->where('status', 'half_day')->count(),
            'attendance_rate' => $records->count() > 0 
                ? round(($records->whereIn('status', ['present', 'late'])->count() / $records->count()) * 100, 2)
                : 0,
            'average_hours' => round($records->avg('total_hours'), 2),
        ];

        return [
            'stats' => $stats,
            'records' => $records->map(function ($record) {
                return [
                    'date' => $record->date->format('Y-m-d'),
                    'worker_name' => $record->worker->first_name . ' ' . $record->worker->last_name,
                    'department' => $record->worker->department?->name,
                    'clock_in' => $record->clock_in_time,
                    'clock_out' => $record->clock_out_time,
                    'total_hours' => $record->total_hours,
                    'status' => $record->status,
                ];
            }),
        ];
    }

    /**
     * Generate leave report
     */
    public function generateLeaveReport(int $companyId, array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? now()->startOfYear();
        $dateTo = $filters['date_to'] ?? now()->endOfYear();

        $query = LeaveRequestModel::whereHas('worker', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->whereBetween('start_date', [$dateFrom, $dateTo]);

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }
        if (!empty($filters['leave_type_id'])) {
            $query->where('leave_type_id', $filters['leave_type_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $requests = $query->with(['worker.department', 'leaveType'])->get();

        // Calculate statistics
        $stats = [
            'total_requests' => $requests->count(),
            'approved_count' => $requests->where('status', 'approved')->count(),
            'pending_count' => $requests->where('status', 'pending')->count(),
            'rejected_count' => $requests->where('status', 'rejected')->count(),
            'total_days' => $requests->where('status', 'approved')->sum('days_requested'),
            'by_leave_type' => $requests->groupBy('leaveType.name')->map->count(),
        ];

        return [
            'stats' => $stats,
            'requests' => $requests->map(function ($request) {
                return [
                    'worker_name' => $request->worker->first_name . ' ' . $request->worker->last_name,
                    'department' => $request->worker->department?->name,
                    'leave_type' => $request->leaveType->name,
                    'start_date' => $request->start_date->format('Y-m-d'),
                    'end_date' => $request->end_date->format('Y-m-d'),
                    'days_requested' => $request->days_requested,
                    'status' => $request->status,
                    'reason' => $request->reason,
                ];
            }),
        ];
    }

    /**
     * Generate payroll report
     */
    public function generatePayrollReport(int $companyId, array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? now()->startOfMonth();
        $dateTo = $filters['date_to'] ?? now()->endOfMonth();

        $query = PayrollRunModel::where('company_id', $companyId)
            ->whereBetween('period_start', [$dateFrom, $dateTo]);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $payrollRuns = $query->with(['items.worker.department'])->get();

        // Calculate statistics
        $totalGross = 0;
        $totalNet = 0;
        $totalDeductions = 0;
        $workerCount = 0;

        foreach ($payrollRuns as $run) {
            foreach ($run->items as $item) {
                $totalGross += $item->gross_pay;
                $totalNet += $item->net_pay;
                $totalDeductions += $item->total_deductions;
                $workerCount++;
            }
        }

        $stats = [
            'total_payroll_runs' => $payrollRuns->count(),
            'total_workers_paid' => $workerCount,
            'total_gross_pay' => round($totalGross, 2),
            'total_net_pay' => round($totalNet, 2),
            'total_deductions' => round($totalDeductions, 2),
            'average_gross_pay' => $workerCount > 0 ? round($totalGross / $workerCount, 2) : 0,
            'average_net_pay' => $workerCount > 0 ? round($totalNet / $workerCount, 2) : 0,
        ];

        return [
            'stats' => $stats,
            'payroll_runs' => $payrollRuns->map(function ($run) {
                return [
                    'period' => $run->period_start->format('Y-m-d') . ' to ' . $run->period_end->format('Y-m-d'),
                    'status' => $run->status,
                    'total_gross' => $run->total_gross_pay,
                    'total_net' => $run->total_net_pay,
                    'worker_count' => $run->items->count(),
                ];
            }),
        ];
    }

    /**
     * Generate performance report
     */
    public function generatePerformanceReport(int $companyId, array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? now()->startOfYear();
        $dateTo = $filters['date_to'] ?? now()->endOfYear();

        $query = PerformanceReviewModel::whereHas('worker', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->whereBetween('review_date', [$dateFrom, $dateTo]);

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }
        if (!empty($filters['department_id'])) {
            $query->whereHas('worker', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }

        $reviews = $query->with(['worker.department', 'cycle'])->get();

        // Calculate statistics
        $stats = [
            'total_reviews' => $reviews->count(),
            'completed_reviews' => $reviews->where('status', 'completed')->count(),
            'average_rating' => round($reviews->where('status', 'completed')->avg('overall_rating'), 2),
            'by_rating' => [
                'excellent' => $reviews->where('overall_rating', '>=', 4.5)->count(),
                'good' => $reviews->whereBetween('overall_rating', [3.5, 4.49])->count(),
                'satisfactory' => $reviews->whereBetween('overall_rating', [2.5, 3.49])->count(),
                'needs_improvement' => $reviews->where('overall_rating', '<', 2.5)->count(),
            ],
        ];

        return [
            'stats' => $stats,
            'reviews' => $reviews->map(function ($review) {
                return [
                    'worker_name' => $review->worker->first_name . ' ' . $review->worker->last_name,
                    'department' => $review->worker->department?->name,
                    'review_date' => $review->review_date->format('Y-m-d'),
                    'review_type' => $review->review_type,
                    'overall_rating' => $review->overall_rating,
                    'status' => $review->status,
                ];
            }),
        ];
    }

    /**
     * Generate training report
     */
    public function generateTrainingReport(int $companyId, array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? now()->startOfYear();
        $dateTo = $filters['date_to'] ?? now()->endOfYear();

        $query = TrainingEnrollmentModel::whereHas('session.program', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->whereBetween('enrollment_date', [$dateFrom, $dateTo]);

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }
        if (!empty($filters['program_id'])) {
            $query->whereHas('session', function ($q) use ($filters) {
                $q->where('program_id', $filters['program_id']);
            });
        }

        $enrollments = $query->with(['worker.department', 'session.program'])->get();

        // Calculate statistics
        $stats = [
            'total_enrollments' => $enrollments->count(),
            'completed_count' => $enrollments->where('status', 'completed')->count(),
            'in_progress_count' => $enrollments->where('status', 'in_progress')->count(),
            'completion_rate' => $enrollments->count() > 0 
                ? round(($enrollments->where('status', 'completed')->count() / $enrollments->count()) * 100, 2)
                : 0,
            'average_score' => round($enrollments->where('status', 'completed')->avg('assessment_score'), 2),
            'certificates_issued' => $enrollments->whereNotNull('certificate_number')->count(),
        ];

        return [
            'stats' => $stats,
            'enrollments' => $enrollments->map(function ($enrollment) {
                return [
                    'worker_name' => $enrollment->worker->first_name . ' ' . $enrollment->worker->last_name,
                    'department' => $enrollment->worker->department?->name,
                    'program_name' => $enrollment->session->program->name,
                    'enrollment_date' => $enrollment->enrollment_date->format('Y-m-d'),
                    'status' => $enrollment->status,
                    'assessment_score' => $enrollment->assessment_score,
                    'certificate_number' => $enrollment->certificate_number,
                ];
            }),
        ];
    }
}
