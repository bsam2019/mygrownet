<?php

declare(strict_types=1);

namespace App\Domain\Employee\Repositories;

use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Models\EmployeeAttendance;
use Illuminate\Support\Collection;

interface AttendanceRepositoryInterface
{
    public function findTodayForEmployee(EmployeeId $employeeId): ?EmployeeAttendance;
    
    public function findByDateRange(EmployeeId $employeeId, \DateTimeImmutable $start, \DateTimeImmutable $end): Collection;
    
    public function clockIn(EmployeeId $employeeId): EmployeeAttendance;
    
    public function clockOut(EmployeeId $employeeId): ?EmployeeAttendance;
    
    public function startBreak(EmployeeId $employeeId): ?EmployeeAttendance;
    
    public function endBreak(EmployeeId $employeeId): ?EmployeeAttendance;
    
    public function getMonthlyStats(EmployeeId $employeeId, int $year, int $month): array;
    
    public function getWeeklyHours(EmployeeId $employeeId): float;
    
    public function getOvertimeHours(EmployeeId $employeeId, int $year, int $month): float;
}
