<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Employee\Entities\Goal;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\GoalStatus;
use App\Domain\Employee\Repositories\GoalRepositoryInterface;
use App\Models\EmployeeGoal;
use DateTimeImmutable;
use Illuminate\Support\Collection;

class EloquentGoalRepository implements GoalRepositoryInterface
{
    public function findById(int $id): ?Goal
    {
        $model = EmployeeGoal::find($id);
        
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByEmployee(EmployeeId $employeeId, array $filters = []): Collection
    {
        $query = EmployeeGoal::where('employee_id', $employeeId->toInt())
            ->with(['approver']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        return $query->orderBy('due_date')->get();
    }

    public function findActiveByEmployee(EmployeeId $employeeId): Collection
    {
        return EmployeeGoal::where('employee_id', $employeeId->toInt())
            ->active()
            ->orderBy('due_date')
            ->get();
    }

    public function findOverdue(EmployeeId $employeeId): Collection
    {
        return EmployeeGoal::where('employee_id', $employeeId->toInt())
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('due_date', '<', today())
            ->orderBy('due_date')
            ->get();
    }

    public function save(Goal $goal): bool
    {
        $data = [
            'employee_id' => $goal->getEmployeeId()->toInt(),
            'title' => $goal->getTitle(),
            'description' => $goal->getDescription(),
            'category' => $goal->getCategory(),
            'progress' => $goal->getProgress(),
            'status' => $goal->getStatus()->getValue(),
            'start_date' => $goal->getStartDate()->format('Y-m-d'),
            'due_date' => $goal->getDueDate()->format('Y-m-d'),
            'completed_at' => $goal->getCompletedAt()?->format('Y-m-d H:i:s'),
            'milestones' => $goal->getMilestones(),
            'approved_by' => $goal->getApprovedBy()?->toInt(),
        ];

        if ($goal->getId()) {
            return EmployeeGoal::where('id', $goal->getId())->update($data) > 0;
        }

        $model = EmployeeGoal::create($data);
        $goal->setId($model->id);
        return $model->exists;
    }

    public function delete(int $id): bool
    {
        return EmployeeGoal::destroy($id) > 0;
    }

    public function getProgressStats(EmployeeId $employeeId): array
    {
        $empId = $employeeId->toInt();
        
        $total = EmployeeGoal::where('employee_id', $empId)->count();
        $completed = EmployeeGoal::where('employee_id', $empId)->where('status', 'completed')->count();
        $inProgress = EmployeeGoal::where('employee_id', $empId)->where('status', 'in_progress')->count();
        $pending = EmployeeGoal::where('employee_id', $empId)->where('status', 'pending')->count();
        
        $avgProgress = (float) (EmployeeGoal::where('employee_id', $empId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->avg('progress') ?? 0);

        return [
            'total' => $total,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'pending' => $pending,
            'average_progress' => round($avgProgress, 2),
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
        ];
    }

    public function findByCategory(EmployeeId $employeeId, string $category): Collection
    {
        return EmployeeGoal::where('employee_id', $employeeId->toInt())
            ->where('category', $category)
            ->orderBy('due_date')
            ->get();
    }

    public function getCompletionRate(EmployeeId $employeeId, ?DateTimeImmutable $from = null, ?DateTimeImmutable $to = null): float
    {
        $query = EmployeeGoal::where('employee_id', $employeeId->toInt());

        if ($from) {
            $query->where('created_at', '>=', $from->format('Y-m-d'));
        }

        if ($to) {
            $query->where('created_at', '<=', $to->format('Y-m-d'));
        }

        $total = $query->count();
        $completed = (clone $query)->where('status', 'completed')->count();

        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }

    private function toDomainEntity(EmployeeGoal $model): Goal
    {
        $goal = Goal::create(
            EmployeeId::fromInt($model->employee_id),
            $model->title,
            $model->category,
            new DateTimeImmutable($model->start_date->format('Y-m-d')),
            new DateTimeImmutable($model->due_date->format('Y-m-d')),
            $model->description,
            $model->milestones ?? []
        );

        $goal->setId($model->id);

        return $goal;
    }
}
