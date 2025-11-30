<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\Repositories\AttendanceRepositoryInterface;
use App\Models\EmployeeAttendance;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class EloquentAttendanceRepository implements AttendanceRepositoryInterface
{
    public function findTodayForEmployee(EmployeeId $employeeId): ?EmployeeAttendance
    {
        return EmployeeAttendance::where('employee_id', $employeeId->toInt())
            ->where('date', today())
            ->first();
    }

    public function findByDateRange(EmployeeId $employeeId, DateTimeImmutable $start, DateTimeImmutable $end): Collection
    {
        return EmployeeAttendance::where('employee_id', $employeeId->toInt())
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->orderBy('date', 'desc')
            ->get();
    }

    public function clockIn(EmployeeId $employeeId): EmployeeAttendance
    {
        return EmployeeAttendance::create([
            'employee_id' => $employeeId->toInt(),
            'date' => today(),
            'clock_in' => now()->format('H:i:s'),
            'status' => 'present',
        ]);
    }

    public function clockOut(EmployeeId $employeeId): ?EmployeeAttendance
    {
        $attendance = $this->findTodayForEmployee($employeeId);
        
        if ($attendance) {
            $attendance->clockOut();
        }

        return $attendance;
    }

    public function startBreak(EmployeeId $employeeId): ?EmployeeAttendance
    {
        $attendance = $this->findTodayForEmployee($employeeId);
        
        if ($attendance) {
            $attendance->update(['break_start' => now()->format('H:i:s')]);
        }

        return $attendance;
    }

    public function endBreak(EmployeeId $employeeId): ?EmployeeAttendance
    {
        $attendance = $this->findTodayForEmployee($employeeId);
        
        if ($attendance) {
            $attendance->update(['break_end' => now()->format('H:i:s')]);
        }

        return $attendance;
    }

    public function getMonthlyStats(EmployeeId $employeeId, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $records = EmployeeAttendance::where('employee_id', $employeeId->toInt())
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $totalDays = $records->count();
        $totalHours = (float) $records->sum('hours_worked');
        $totalOvertime = (float) $records->sum('overtime_hours');
        $presentDays = $records->where('status', 'present')->count();
        $lateDays = $records->where('status', 'late')->count();
        $absentDays = $records->where('status', 'absent')->count();

        $workingDays = $this->getWorkingDaysInMonth($year, $month);

        return [
            'total_days' => $totalDays,
            'working_days' => $workingDays,
            'present_days' => $presentDays,
            'late_days' => $lateDays,
            'absent_days' => $absentDays,
            'total_hours' => round($totalHours, 2),
            'total_overtime' => round($totalOvertime, 2),
            'average_hours_per_day' => $totalDays > 0 ? round($totalHours / $totalDays, 2) : 0,
            'attendance_rate' => $workingDays > 0 ? round(($presentDays / $workingDays) * 100, 2) : 0,
        ];
    }

    public function getWeeklyHours(EmployeeId $employeeId): float
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        return (float) EmployeeAttendance::where('employee_id', $employeeId->toInt())
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->sum('hours_worked');
    }

    public function getOvertimeHours(EmployeeId $employeeId, int $year, int $month): float
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        return (float) EmployeeAttendance::where('employee_id', $employeeId->toInt())
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('overtime_hours');
    }

    private function getWorkingDaysInMonth(int $year, int $month): int
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $workingDays = 0;

        while ($startDate <= $endDate) {
            if (!$startDate->isWeekend()) {
                $workingDays++;
            }
            $startDate->addDay();
        }

        return $workingDays;
    }
}
