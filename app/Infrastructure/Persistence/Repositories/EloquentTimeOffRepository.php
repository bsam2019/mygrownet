<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Employee\Entities\TimeOffRequest;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\TimeOffType;
use App\Domain\Employee\Repositories\TimeOffRepositoryInterface;
use App\Models\EmployeeTimeOffRequest as TimeOffModel;
use App\Models\Employee;
use DateTimeImmutable;
use Illuminate\Support\Collection;

class EloquentTimeOffRepository implements TimeOffRepositoryInterface
{
    public function findById(int $id): ?TimeOffRequest
    {
        $model = TimeOffModel::find($id);
        
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByEmployee(EmployeeId $employeeId, array $filters = []): Collection
    {
        $query = TimeOffModel::forEmployee($employeeId->toInt())
            ->with(['reviewer']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('start_date', $filters['year']);
        }

        return $query->orderBy('start_date', 'desc')->get();
    }

    public function findPendingByEmployee(EmployeeId $employeeId): Collection
    {
        return TimeOffModel::forEmployee($employeeId->toInt())
            ->pending()
            ->orderBy('start_date')
            ->get();
    }

    public function findApprovedByEmployee(EmployeeId $employeeId): Collection
    {
        return TimeOffModel::forEmployee($employeeId->toInt())
            ->where('status', 'approved')
            ->orderBy('start_date', 'desc')
            ->get();
    }

    public function findUpcoming(EmployeeId $employeeId): Collection
    {
        return TimeOffModel::forEmployee($employeeId->toInt())
            ->where('status', 'approved')
            ->where('start_date', '>=', today())
            ->orderBy('start_date')
            ->get();
    }

    public function save(TimeOffRequest $request): bool
    {
        $data = [
            'employee_id' => $request->getEmployeeId()->toInt(),
            'type' => $request->getType()->getValue(),
            'start_date' => $request->getStartDate()->format('Y-m-d'),
            'end_date' => $request->getEndDate()->format('Y-m-d'),
            'days_requested' => $request->getDaysRequested(),
            'reason' => $request->getReason(),
            'status' => $request->getStatus(),
            'reviewed_by' => $request->getReviewedBy()?->toInt(),
            'reviewed_at' => $request->getReviewedAt()?->format('Y-m-d H:i:s'),
            'review_notes' => $request->getReviewNotes(),
        ];

        if ($request->getId()) {
            return TimeOffModel::where('id', $request->getId())->update($data) > 0;
        }

        $model = TimeOffModel::create($data);
        $request->setId($model->id);
        return $model->exists;
    }

    public function delete(int $id): bool
    {
        return TimeOffModel::destroy($id) > 0;
    }

    public function getUsedDays(EmployeeId $employeeId, TimeOffType $type, int $year): float
    {
        return TimeOffModel::forEmployee($employeeId->toInt())
            ->where('type', $type->getValue())
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->sum('days_requested');
    }

    public function getBalance(EmployeeId $employeeId, TimeOffType $type, int $year): array
    {
        $allowance = $type->getDefaultAllowance();
        $used = $this->getUsedDays($employeeId, $type, $year);
        $pending = TimeOffModel::forEmployee($employeeId->toInt())
            ->where('type', $type->getValue())
            ->pending()
            ->whereYear('start_date', $year)
            ->sum('days_requested');

        return [
            'type' => $type->getValue(),
            'label' => $type->getLabel(),
            'allowance' => $allowance,
            'used' => $used,
            'pending' => $pending,
            'remaining' => max(0, $allowance - $used),
            'available' => max(0, $allowance - $used - $pending),
        ];
    }

    public function findPendingForManager(EmployeeId $managerId): Collection
    {
        $subordinateIds = Employee::where('manager_id', $managerId->toInt())
            ->pluck('id');

        return TimeOffModel::whereIn('employee_id', $subordinateIds)
            ->pending()
            ->with(['employee'])
            ->orderBy('created_at')
            ->get();
    }

    public function hasOverlappingRequest(EmployeeId $employeeId, DateTimeImmutable $start, DateTimeImmutable $end, ?int $excludeId = null): bool
    {
        $query = TimeOffModel::forEmployee($employeeId->toInt())
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                    ->orWhereBetween('end_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('start_date', '<=', $start->format('Y-m-d'))
                            ->where('end_date', '>=', $end->format('Y-m-d'));
                    });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    private function toDomainEntity(TimeOffModel $model): TimeOffRequest
    {
        $request = TimeOffRequest::create(
            EmployeeId::fromInt($model->employee_id),
            TimeOffType::fromString($model->type),
            new DateTimeImmutable($model->start_date->format('Y-m-d')),
            new DateTimeImmutable($model->end_date->format('Y-m-d')),
            $model->days_requested,
            $model->reason
        );

        $request->setId($model->id);

        return $request;
    }
}
