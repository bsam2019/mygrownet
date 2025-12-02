<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\GrowBiz\Entities\Employee;
use App\Domain\GrowBiz\Repositories\EmployeeRepositoryInterface;
use App\Domain\GrowBiz\ValueObjects\EmployeeId;
use App\Domain\GrowBiz\ValueObjects\EmployeeStatus;
use App\Infrastructure\Persistence\Eloquent\GrowBizEmployeeModel;
use DateTimeImmutable;

class GrowBizEmployeeRepository implements EmployeeRepositoryInterface
{
    public function findById(EmployeeId $id): ?Employee
    {
        $model = GrowBizEmployeeModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByManagerId(int $managerId): array
    {
        return GrowBizEmployeeModel::forManager($managerId)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findActiveByManagerId(int $managerId): array
    {
        return GrowBizEmployeeModel::forManager($managerId)
            ->active()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findByManagerIdAndStatus(int $managerId, EmployeeStatus $status): array
    {
        return GrowBizEmployeeModel::forManager($managerId)
            ->where('status', $status->getValue())
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }


    public function save(Employee $employee): Employee
    {
        $data = [
            'manager_id' => $employee->getManagerId(),
            'user_id' => $employee->getUserId(),
            'first_name' => $employee->getFirstName(),
            'last_name' => $employee->getLastName(),
            'email' => $employee->getEmail(),
            'phone' => $employee->getPhone(),
            'position' => $employee->getPosition(),
            'department' => $employee->getDepartment(),
            'status' => $employee->getStatus()->getValue(),
            'hire_date' => $employee->getHireDate()?->format('Y-m-d'),
            'hourly_rate' => $employee->getHourlyRate(),
            'notes' => $employee->getNotes(),
        ];

        if ($employee->getId()->toInt() === 0) {
            $model = GrowBizEmployeeModel::create($data);
        } else {
            $model = GrowBizEmployeeModel::find($employee->getId()->toInt());
            $model->update($data);
        }

        return $this->toDomainEntity($model);
    }

    public function findByOwnerWithFilters(int $ownerId, array $filters): array
    {
        $query = GrowBizEmployeeModel::forManager($ownerId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['department'])) {
            $query->where('department', $filters['department']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('first_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('last_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('phone', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function getDistinctDepartments(int $ownerId): array
    {
        return GrowBizEmployeeModel::forManager($ownerId)
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->pluck('department')
            ->toArray();
    }

    public function findByEmail(int $managerId, string $email): ?Employee
    {
        $model = GrowBizEmployeeModel::forManager($managerId)
            ->where('email', $email)
            ->first();
            
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function delete(EmployeeId $id): void
    {
        GrowBizEmployeeModel::destroy($id->toInt());
    }

    private function toDomainEntity(GrowBizEmployeeModel $model): Employee
    {
        return Employee::reconstitute(
            id: EmployeeId::fromInt($model->id),
            managerId: $model->manager_id,
            userId: $model->user_id,
            firstName: $model->first_name,
            lastName: $model->last_name,
            email: $model->email,
            phone: $model->phone,
            position: $model->position,
            department: $model->department,
            status: EmployeeStatus::fromString($model->status),
            hireDate: $model->hire_date ? new DateTimeImmutable($model->hire_date->toDateTimeString()) : null,
            hourlyRate: $model->hourly_rate ? (float) $model->hourly_rate : null,
            notes: $model->notes,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new DateTimeImmutable($model->updated_at->toDateTimeString())
        );
    }
}
