<?php

declare(strict_types=1);

namespace App\Domain\Employee\Services;

use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\Repositories\AttendanceRepositoryInterface;
use App\Models\EmployeeAttendance;
use DateTimeImmutable;
use Illuminate\Support\Collection;

class AttendanceService
{
    public function __construct(
        private AttendanceRepositoryInterface $attendanceRepository
    ) {}

    public function getTodayAttendance(EmployeeId $employeeId): ?EmployeeAttendance
    {
        return $this->attendanceRepository->findTodayForEmployee($employeeId);
    }

    public function clockIn(EmployeeId $employeeId): EmployeeAttendance
    {
        $today = $this->getTodayAttendance($employeeId);

        if ($today && $today->clock_in) {
            throw new \RuntimeException('Already clocked in today');
        }

        return $this->attendanceRepository->clockIn($employeeId);
    }

    public function clockOut(EmployeeId $employeeId): EmployeeAttendance
    {
        $today = $this->getTodayAttendance($employeeId);

        if (!$today || !$today->clock_in) {
            throw new \RuntimeException('Must clock in before clocking out');
        }

        if ($today->clock_out) {
            throw new \RuntimeException('Already clocked out today');
        }

        return $this->attendanceRepository->clockOut($employeeId);
    }

    public function startBreak(EmployeeId $employeeId): EmployeeAttendance
    {
        $today = $this->getTodayAttendance($employeeId);

        if (!$today || !$today->clock_in) {
            throw new \RuntimeException('Must clock in before starting break');
        }

        if ($today->break_start && !$today->break_end) {
            throw new \RuntimeException('Already on break');
        }

        return $this->attendanceRepository->startBreak($employeeId);
    }

    public function endBreak(EmployeeId $employeeId): EmployeeAttendance
    {
        $today = $this->getTodayAttendance($employeeId);

        if (!$today || !$today->break_start) {
            throw new \RuntimeException('Must start break before ending it');
        }

        if ($today->break_end) {
            throw new \RuntimeException('Break already ended');
        }

        return $this->attendanceRepository->endBreak($employeeId);
    }

    public function getAttendanceHistory(EmployeeId $employeeId, DateTimeImmutable $start, DateTimeImmutable $end): Collection
    {
        return $this->attendanceRepository->findByDateRange($employeeId, $start, $end);
    }

    public function getMonthlyStats(EmployeeId $employeeId, int $year, int $month): array
    {
        return $this->attendanceRepository->getMonthlyStats($employeeId, $year, $month);
    }

    public function getWeeklyHours(EmployeeId $employeeId): float
    {
        return $this->attendanceRepository->getWeeklyHours($employeeId);
    }

    public function getOvertimeHours(EmployeeId $employeeId, int $year, int $month): float
    {
        return $this->attendanceRepository->getOvertimeHours($employeeId, $year, $month);
    }

    public function getAttendanceSummary(EmployeeId $employeeId): array
    {
        $today = $this->getTodayAttendance($employeeId);
        $now = new DateTimeImmutable();
        $monthlyStats = $this->getMonthlyStats($employeeId, (int) $now->format('Y'), (int) $now->format('m'));
        $weeklyHours = $this->getWeeklyHours($employeeId);

        return [
            'today' => [
                'clocked_in' => $today?->clock_in !== null,
                'clocked_out' => $today?->clock_out !== null,
                'on_break' => $today?->break_start !== null && $today?->break_end === null,
                'clock_in_time' => $today?->clock_in,
                'clock_out_time' => $today?->clock_out,
                'hours_worked' => $today?->hours_worked ?? 0,
            ],
            'weekly_hours' => $weeklyHours,
            'monthly_stats' => $monthlyStats,
            'status' => $this->determineStatus($today),
        ];
    }

    private function determineStatus(?EmployeeAttendance $attendance): string
    {
        if (!$attendance || !$attendance->clock_in) {
            return 'not_clocked_in';
        }

        if ($attendance->clock_out) {
            return 'clocked_out';
        }

        if ($attendance->break_start && !$attendance->break_end) {
            return 'on_break';
        }

        return 'working';
    }
}
