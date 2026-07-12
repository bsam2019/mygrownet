<?php

namespace App\Infrastructure\PrimeEdge\Repositories;

use App\Domain\PrimeEdge\Entities\ComplianceTask;
use App\Domain\PrimeEdge\Repositories\ComplianceTaskRepositoryInterface;
use App\Domain\PrimeEdge\ValueObjects\ComplianceTaskId;
use App\Domain\PrimeEdge\ValueObjects\ComplianceType;
use App\Domain\PrimeEdge\ValueObjects\TaskRecurrence;
use App\Domain\PrimeEdge\ValueObjects\Deadline;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Infrastructure\PrimeEdge\Persistence\ComplianceTaskModel;
use DateTimeImmutable;

class EloquentComplianceTaskRepository implements ComplianceTaskRepositoryInterface
{
    public function save(ComplianceTask $task): void
    {
        ComplianceTaskModel::updateOrCreate(
            ['id' => $task->id()->toString()],
            [
                'client_id' => $task->clientId()->toString(),
                'type' => $task->type()->value,
                'description' => $task->description(),
                'due_date' => $task->dueDate()->toString(),
                'recurrence' => $task->recurrence()->value,
                'status' => $task->status(),
                'reminder_days' => $task->reminderDays(),
                'notes' => $task->notes(),
                'completed_at' => $task->completedAt(),
                'reminded_at' => $task->remindedAt(),
            ]
        );
    }

    public function findById(ComplianceTaskId $id): ?ComplianceTask
    {
        $model = ComplianceTaskModel::find($id->toString());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByClientId(ClientId $clientId): array
    {
        return ComplianceTaskModel::where('client_id', $clientId->toString())
            ->orderBy('due_date')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findPending(): array
    {
        return ComplianceTaskModel::where('status', ComplianceTask::STATUS_PENDING)
            ->orderBy('due_date')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findOverdue(): array
    {
        return ComplianceTaskModel::where('status', ComplianceTask::STATUS_PENDING)
            ->where('due_date', '<', now())
            ->orderBy('due_date')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findUpcoming(int $withinDays): array
    {
        return ComplianceTaskModel::where('status', ComplianceTask::STATUS_PENDING)
            ->whereBetween('due_date', [now(), now()->addDays($withinDays)])
            ->orderBy('due_date')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findAll(): array
    {
        return ComplianceTaskModel::orderBy('due_date')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function nextId(): ComplianceTaskId
    {
        return ComplianceTaskId::generate();
    }

    private function toDomainEntity(ComplianceTaskModel $model): ComplianceTask
    {
        return ComplianceTask::reconstitute(
            id: ComplianceTaskId::fromString($model->id),
            clientId: ClientId::fromString($model->client_id),
            type: ComplianceType::from($model->type),
            description: $model->description,
            dueDate: Deadline::fromString($model->due_date->format('Y-m-d')),
            recurrence: TaskRecurrence::from($model->recurrence),
            status: $model->status,
            reminderDays: $model->reminder_days,
            notes: $model->notes,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            completedAt: $model->completed_at ? new DateTimeImmutable($model->completed_at->toDateTimeString()) : null,
            remindedAt: $model->reminded_at ? new DateTimeImmutable($model->reminded_at->toDateTimeString()) : null,
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );
    }
}
