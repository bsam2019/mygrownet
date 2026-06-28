<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\AttendanceRecordModel;
use App\Infrastructure\Persistence\Eloquent\CMS\LeaveRequestModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PublicHolidayModel;
use App\Infrastructure\Persistence\Eloquent\CMS\WorkerModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(
        private AuditTrailService $auditTrail,
        private ShiftManagementService $shiftManagement,
        private OvertimeService $overtimeService
    ) {}

    /**
     * Clock in a worker
     */
    public function clockIn(array $data): AttendanceRecordModel
    {
        $worker = WorkerModel::findOrFail($data['worker_id']);
        $today = now()->startOfDay();

        // Check if already clocked in today
        $existing = AttendanceRecordModel::where('company_id', $data['company_id'])
            ->where('worker_id', $data['worker_id'])
            ->where('attendance_date', $today)
            ->first();

        if ($existing && $existing->isClockedIn()) {
            throw new \Exception('Worker is already clocked in for today.');
        }

        // Get worker's shift for today
        $shift = $this->shiftManagement->getWorkerCurrentShift(
            $data['company_id'],
            $data['worker_id']
        );

        $clockInTime = now();
        $isLate = false;
        $lateByMinutes = null;

        if ($shift) {
            $isLate = !$shift->isWithinGracePeriod($clockInTime);
            $lateByMinutes = $shift->calculateLateMinutes($clockInTime);
        }

        if ($existing) {
            // Update existing record
            $existing->update([
                'clock_in_time' => $clockInTime,
                'clock_in_location' => $data['location'] ?? null,
                'clock_in_photo_path' => $data['photo_path'] ?? null,
                'clock_in_device' => $data['device'] ?? 'web',
                'shift_id' => $shift?->id,
                'is_late' => $isLate,
                'late_by_minutes' => $lateByMinutes,
                'worker_notes' => $data['notes'] ?? null,
            ]);

            $record = $existing;
        } else {
            // Create new record
            $record = AttendanceRecordModel::create([
                'company_id' => $data['company_id'],
                'worker_id' => $data['worker_id'],
                'shift_id' => $shift?->id,
                'attendance_date' => $today,
                'clock_in_time' => $clockInTime,
                'clock_in_location' => $data['location'] ?? null,
                'clock_in_photo_path' => $data['photo_path'] ?? null,
                'clock_in_device' => $data['device'] ?? 'web',
                'is_late' => $isLate,
                'late_by_minutes' => $lateByMinutes,
                'worker_notes' => $data['notes'] ?? null,
                'status' => 'present',
            ]);
        }

        $this->auditTrail->log(
            companyId: $record->company_id,
            userId: auth()->id() ?? $data['worker_id'],
            entityType: 'attendance',
            entityId: $record->id,
            action: 'clock_in',
            newValues: ['clock_in_time' => $clockInTime, 'is_late' => $isLate]
        );

        return $record;
    }

    /**
     * Clock out a worker
     */
    public function clockOut(AttendanceRecordModel $record, array $data = []): AttendanceRecordModel
    {
        if (!$record->isClockedIn()) {
            throw new \Exception('Worker is not clocked in.');
        }

        $clockOutTime = now();
        $totalMinutes = $record->clock_in_time->diffInMinutes($clockOutTime);
        
        // Subtract break time
        $breakMinutes = $record->shift?->break_duration_minutes ?? 0;
        $workMinutes = max(0, $totalMinutes - $breakMinutes);

        // Calculate regular and overtime hours
        $overtimeThreshold = $record->shift?->overtime_threshold_minutes ?? 480; // 8 hours default
        $regularMinutes = min($workMinutes, $overtimeThreshold);
        $overtimeMinutes = max(0, $workMinutes - $overtimeThreshold);

        // Determine status
        $status = $this->calculateAttendanceStatus($record, $workMinutes);

        // Check for early departure
        $isEarlyDeparture = false;
        $earlyByMinutes = null;
        if ($record->shift) {
            $expectedEndTime = Carbon::parse($record->shift->end_time);
            if ($clockOutTime->lessThan($expectedEndTime)) {
                $isEarlyDeparture = true;
                $earlyByMinutes = $clockOutTime->diffInMinutes($expectedEndTime);
            }
        }

        $record->update([
            'clock_out_time' => $clockOutTime,
            'clock_out_location' => $data['location'] ?? null,
            'clock_out_photo_path' => $data['photo_path'] ?? null,
            'clock_out_device' => $data['device'] ?? 'web',
            'total_minutes' => $workMinutes,
            'regular_minutes' => $regularMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'break_minutes' => $breakMinutes,
            'status' => $status,
            'is_early_departure' => $isEarlyDeparture,
            'early_by_minutes' => $earlyByMinutes,
        ]);

        // Create overtime record if applicable
        if ($overtimeMinutes > 0) {
            $this->overtimeService->createFromAttendance($record);
        }

        $this->auditTrail->log(
            companyId: $record->company_id,
            userId: auth()->id() ?? $record->worker_id,
            entityType: 'attendance',
            entityId: $record->id,
            action: 'clock_out',
            newValues: [
                'clock_out_time' => $clockOutTime,
                'total_minutes' => $workMinutes,
                'status' => $status
            ]
        );

        return $record;
    }

    /**
     * Calculate attendance status based on hours worked
     */
    private function calculateAttendanceStatus(AttendanceRecordModel $record, int $workMinutes): string
    {
        $shift = $record->shift;
        
        if (!$shift) {
            // No shift defined, use default thresholds
            $fullDayMinutes = 7.5 * 60; // 7.5 hours
            $halfDayMinutes = 4 * 60;   // 4 hours
        } else {
            $fullDayMinutes = $shift->minimum_hours_full_day * 60;
            $halfDayMinutes = $shift->minimum_hours_half_day * 60;
        }

        if ($workMinutes >= $fullDayMinutes) {
            return $record->is_late ? 'late' : 'present';
        } elseif ($workMinutes >= $halfDayMinutes) {
            return 'half_day';
        } else {
            return 'absent';
        }
    }

    /**
     * Create manual attendance entry
     */
    public function createManualAttendance(array $data): AttendanceRecordModel
    {
        $worker = WorkerModel::findOrFail($data['worker_id']);
        $attendanceDate = Carbon::parse($data['attendance_date']);

        // Check if attendance already exists
        $existing = AttendanceRecordModel::where('company_id', $data['company_id'])
            ->where('worker_id', $data['worker_id'])
            ->where('attendance_date', $attendanceDate)
            ->first();

        if ($existing) {
            throw new \Exception('Attendance record already exists for this date.');
        }

        // Get shift
        $shift = $this->shiftManagement->getWorkerCurrentShift(
            $data['company_id'],
            $data['worker_id'],
            $attendanceDate
        );

        $clockInTime = Carbon::parse($data['clock_in_time']);
        $clockOutTime = isset($data['clock_out_time']) ? Carbon::parse($data['clock_out_time']) : null;

        $totalMinutes = null;
        $regularMinutes = null;
        $overtimeMinutes = null;
        $status = $data['status'] ?? 'present';

        if ($clockOutTime) {
            $totalMinutes = $clockInTime->diffInMinutes($clockOutTime);
            $breakMinutes = $shift?->break_duration_minutes ?? 0;
            $workMinutes = max(0, $totalMinutes - $breakMinutes);

            $overtimeThreshold = $shift?->overtime_threshold_minutes ?? 480;
            $regularMinutes = min($workMinutes, $overtimeThreshold);
            $overtimeMinutes = max(0, $workMinutes - $overtimeThreshold);
        }

        $record = AttendanceRecordModel::create([
            'company_id' => $data['company_id'],
            'worker_id' => $data['worker_id'],
            'shift_id' => $shift?->id,
            'attendance_date' => $attendanceDate,
            'clock_in_time' => $clockInTime,
            'clock_out_time' => $clockOutTime,
            'total_minutes' => $totalMinutes,
            'regular_minutes' => $regularMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'break_minutes' => $shift?->break_duration_minutes ?? 0,
            'status' => $status,
            'notes' => $data['notes'] ?? null,
            'is_manual_entry' => true,
            'created_by' => auth()->id(),
        ]);

        $this->auditTrail->log(
            companyId: $record->company_id,
            userId: auth()->id(),
            entityType: 'attendance',
            entityId: $record->id,
            action: 'manual_entry_created',
            newValues: $record->toArray()
        );

        return $record;
    }

    /**
     * Get attendance records
     */
    public function getAttendanceRecords(int $companyId, array $filters = [])
    {
        $query = AttendanceRecordModel::where('company_id', $companyId)
            ->with(['worker', 'shift']);

        if (!empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->dateRange($filters['start_date'], $filters['end_date']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('attendance_date', 'desc')->paginate(20);
    }

    /**
     * Get attendance summary
     */
    public function getAttendanceSummary(int $companyId, int $workerId, Carbon $startDate, Carbon $endDate): array
    {
        $records = AttendanceRecordModel::where('company_id', $companyId)
            ->where('worker_id', $workerId)
            ->dateRange($startDate, $endDate)
            ->get();

        $totalDays = $startDate->diffInDays($endDate) + 1;
        $presentDays = $records->present()->count();
        $absentDays = $records->absent()->count();
        $lateDays = $records->where('is_late', true)->count();
        $halfDays = $records->where('status', 'half_day')->count();

        $totalHours = $records->sum('total_minutes') / 60;
        $regularHours = $records->sum('regular_minutes') / 60;
        $overtimeHours = $records->sum('overtime_minutes') / 60;

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'total_days' => $totalDays,
            ],
            'attendance' => [
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'late_days' => $lateDays,
                'half_days' => $halfDays,
                'attendance_rate' => $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0,
            ],
            'hours' => [
                'total_hours' => round($totalHours, 2),
                'regular_hours' => round($regularHours, 2),
                'overtime_hours' => round($overtimeHours, 2),
            ],
        ];
    }

    /**
     * Auto clock out workers at end of day
     */
    public function autoClockOut(int $companyId): int
    {
        $records = AttendanceRecordModel::where('company_id', $companyId)
            ->where('attendance_date', now()->startOfDay())
            ->whereNotNull('clock_in_time')
            ->whereNull('clock_out_time')
            ->get();

        $count = 0;
        foreach ($records as $record) {
            try {
                $this->clockOut($record, ['device' => 'auto']);
                $count++;
            } catch (\Exception $e) {
                // Log error but continue
                \Log::error("Auto clock out failed for attendance {$record->id}: " . $e->getMessage());
            }
        }

        return $count;
    }
}
