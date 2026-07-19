<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\ShiftModel;
use App\Infrastructure\Persistence\Eloquent\CMS\WorkerShiftModel;
use App\Infrastructure\Persistence\Eloquent\CMS\WorkerModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ShiftManagementService
{
    public function __construct(
        private AuditTrailService $auditTrail
    ) {}

    /**
     * Create a new shift
     */
    public function createShift(array $data): ShiftModel
    {
        $shift = ShiftModel::create($data);

        $this->auditTrail->log(
            companyId: $shift->company_id,
            userId: auth()->id(),
            entityType: 'shift',
            entityId: $shift->id,
            action: 'created',
            newValues: $shift->toArray()
        );

        return $shift;
    }

    /**
     * Update a shift
     */
    public function updateShift(ShiftModel $shift, array $data): ShiftModel
    {
        $oldValues = $shift->toArray();
        $shift->update($data);

        $this->auditTrail->log(
            companyId: $shift->company_id,
            userId: auth()->id(),
            entityType: 'shift',
            entityId: $shift->id,
            action: 'updated',
            oldValues: $oldValues,
            newValues: $shift->toArray()
        );

        return $shift;
    }

    /**
     * Delete a shift
     */
    public function deleteShift(ShiftModel $shift): bool
    {
        // Check if shift is assigned to any workers
        $assignedCount = WorkerShiftModel::where('shift_id', $shift->id)
            ->where('is_active', true)
            ->count();

        if ($assignedCount > 0) {
            throw new \Exception("Cannot delete shift. It is currently assigned to {$assignedCount} worker(s).");
        }

        $this->auditTrail->log(
            companyId: $shift->company_id,
            userId: auth()->id(),
            entityType: 'shift',
            entityId: $shift->id,
            action: 'deleted',
            oldValues: $shift->toArray()
        );

        return $shift->delete();
    }

    /**
     * Assign shift to a worker
     */
    public function assignShiftToWorker(array $data): WorkerShiftModel
    {
        return DB::transaction(function () use ($data) {
            $worker = WorkerModel::findOrFail($data['worker_id']);
            $shift = ShiftModel::findOrFail($data['shift_id']);

            // Deactivate any existing active assignments if new one starts today or in future
            if (!isset($data['effective_from']) || Carbon::parse($data['effective_from'])->isToday() || Carbon::parse($data['effective_from'])->isFuture()) {
                WorkerShiftModel::where('company_id', $data['company_id'])
                    ->where('worker_id', $data['worker_id'])
                    ->where('is_active', true)
                    ->update([
                        'is_active' => false,
                        'effective_to' => isset($data['effective_from']) 
                            ? Carbon::parse($data['effective_from'])->subDay() 
                            : now()->subDay()
                    ]);
            }

            $assignment = WorkerShiftModel::create([
                'company_id' => $data['company_id'],
                'worker_id' => $data['worker_id'],
                'shift_id' => $data['shift_id'],
                'effective_from' => $data['effective_from'] ?? now(),
                'effective_to' => $data['effective_to'] ?? null,
                'days_of_week' => $data['days_of_week'] ?? null,
                'is_active' => true,
                'notes' => $data['notes'] ?? null,
            ]);

            // Update worker's default shift if not set
            if (!$worker->default_shift_id) {
                $worker->update(['default_shift_id' => $shift->id]);
            }

            $this->auditTrail->log(
                companyId: $assignment->company_id,
                userId: auth()->id(),
                entityType: 'worker_shift',
                entityId: $assignment->id,
                action: 'assigned',
                newValues: $assignment->toArray()
            );

            return $assignment;
        });
    }

    /**
     * Get worker's current shift for a specific date
     */
    public function getWorkerCurrentShift(int $companyId, int $workerId, ?Carbon $date = null): ?ShiftModel
    {
        $date = $date ?? now();

        // Try to find an active assignment for the date
        $assignment = WorkerShiftModel::where('company_id', $companyId)
            ->where('worker_id', $workerId)
            ->where('is_active', true)
            ->forDate($date)
            ->with('shift')
            ->first();

        if ($assignment && $assignment->isActiveOnDate($date)) {
            return $assignment->shift;
        }

        // Fall back to worker's default shift
        $worker = WorkerModel::find($workerId);
        return $worker?->defaultShift;
    }

    /**
     * Get shift schedule for a worker
     */
    public function getShiftSchedule(int $companyId, int $workerId, Carbon $startDate, Carbon $endDate): array
    {
        $schedule = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lessThanOrEqualTo($endDate)) {
            $shift = $this->getWorkerCurrentShift($companyId, $workerId, $currentDate);
            
            $schedule[] = [
                'date' => $currentDate->format('Y-m-d'),
                'day_of_week' => $currentDate->format('l'),
                'shift' => $shift ? [
                    'id' => $shift->id,
                    'name' => $shift->shift_name,
                    'start_time' => $shift->start_time->format('H:i'),
                    'end_time' => $shift->end_time->format('H:i'),
                ] : null,
            ];

            $currentDate->addDay();
        }

        return $schedule;
    }

    /**
     * Get all shifts for a company
     */
    public function getShifts(int $companyId, bool $activeOnly = true): \Illuminate\Database\Eloquent\Collection
    {
        $query = ShiftModel::where('company_id', $companyId);

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->orderBy('shift_name')->get();
    }

    /**
     * Get workers assigned to a shift
     */
    public function getShiftWorkers(int $shiftId, ?Carbon $date = null): \Illuminate\Database\Eloquent\Collection
    {
        $date = $date ?? now();

        return WorkerShiftModel::where('shift_id', $shiftId)
            ->where('is_active', true)
            ->forDate($date)
            ->with('worker')
            ->get()
            ->filter(function ($assignment) use ($date) {
                return $assignment->isActiveOnDate($date);
            })
            ->pluck('worker');
    }

    /**
     * End a shift assignment
     */
    public function endShiftAssignment(WorkerShiftModel $assignment, Carbon $endDate): WorkerShiftModel
    {
        $oldValues = $assignment->toArray();

        $assignment->update([
            'effective_to' => $endDate,
            'is_active' => $endDate->isPast(),
        ]);

        $this->auditTrail->log(
            companyId: $assignment->company_id,
            userId: auth()->id(),
            entityType: 'worker_shift',
            entityId: $assignment->id,
            action: 'ended',
            oldValues: $oldValues,
            newValues: $assignment->toArray()
        );

        return $assignment;
    }

    /**
     * Get shift statistics
     */
    public function getShiftStatistics(int $companyId): array
    {
        $shifts = ShiftModel::where('company_id', $companyId)
            ->where('is_active', true)
            ->withCount(['workerShifts' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get();

        return [
            'total_shifts' => $shifts->count(),
            'total_workers_assigned' => WorkerShiftModel::where('company_id', $companyId)
                ->where('is_active', true)
                ->distinct('worker_id')
                ->count('worker_id'),
            'shifts' => $shifts->map(function ($shift) {
                return [
                    'id' => $shift->id,
                    'name' => $shift->shift_name,
                    'workers_count' => $shift->worker_shifts_count,
                    'duration_hours' => round($shift->duration_minutes / 60, 2),
                ];
            }),
        ];
    }
}
