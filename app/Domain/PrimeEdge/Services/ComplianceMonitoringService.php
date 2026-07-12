<?php

namespace App\Domain\PrimeEdge\Services;

use App\Domain\PrimeEdge\Entities\ComplianceTask;
use App\Domain\PrimeEdge\ValueObjects\ComplianceTaskId;
use App\Domain\PrimeEdge\ValueObjects\ComplianceType;
use App\Domain\PrimeEdge\ValueObjects\TaskRecurrence;
use App\Domain\PrimeEdge\ValueObjects\Deadline;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Domain\PrimeEdge\Repositories\ComplianceTaskRepositoryInterface;
use App\Domain\PrimeEdge\Events\ComplianceDeadlineApproaching;
use App\Domain\PrimeEdge\Events\ComplianceDeadlineMissed;
use DateTimeImmutable;

class ComplianceMonitoringService
{
    public function __construct(
        private ComplianceTaskRepositoryInterface $taskRepository,
    ) {}

    public function createTask(
        ClientId $clientId,
        ComplianceType $type,
        string $description,
        Deadline $dueDate,
        TaskRecurrence $recurrence = TaskRecurrence::ONE_OFF,
        int $reminderDays = 7,
        ?string $notes = null,
    ): ComplianceTask {
        $task = ComplianceTask::create(
            id: $this->taskRepository->nextId(),
            clientId: $clientId,
            type: $type,
            description: $description,
            dueDate: $dueDate,
            recurrence: $recurrence,
            reminderDays: $reminderDays,
            notes: $notes,
        );

        $this->taskRepository->save($task);

        return $task;
    }

    public function completeTask(ComplianceTaskId $taskId): ComplianceTask
    {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            throw new \App\Domain\PrimeEdge\Exceptions\ComplianceDeadlineExceededException($taskId->toString());
        }

        $task->markAsCompleted();
        $this->taskRepository->save($task);

        if ($task->recurrence()->isRecurring()) {
            $this->generateNextRecurrence($task);
        }

        return $task;
    }

    public function processReminders(): array
    {
        $events = [];
        $upcoming = $this->taskRepository->findUpcoming(7);

        foreach ($upcoming as $task) {
            if ($task->isUpcomingForReminder() && !$task->remindedAt()) {
                $task->markReminded();
                $this->taskRepository->save($task);

                $events[] = new ComplianceDeadlineApproaching(
                    taskId: $task->id(),
                    clientId: $task->clientId(),
                    complianceType: $task->type()->value,
                    dueDate: $task->dueDate()->toString(),
                    daysUntilDue: $task->dueDate()->daysUntil(),
                    occurredAt: new DateTimeImmutable(),
                );
            }
        }

        return $events;
    }

    public function processOverdue(): array
    {
        $events = [];
        $overdue = $this->taskRepository->findOverdue();

        foreach ($overdue as $task) {
            $task->markAsMissed();
            $this->taskRepository->save($task);

            $events[] = new ComplianceDeadlineMissed(
                taskId: $task->id(),
                clientId: $task->clientId(),
                complianceType: $task->type()->value,
                dueDate: $task->dueDate()->toString(),
                occurredAt: new DateTimeImmutable(),
            );
        }

        return $events;
    }

    private function generateNextRecurrence(ComplianceTask $completedTask): void
    {
        $dueDate = $completedTask->dueDate()->toDateTime();
        $nextDue = match ($completedTask->recurrence()) {
            TaskRecurrence::MONTHLY => $dueDate->modify('+1 month'),
            TaskRecurrence::QUARTERLY => $dueDate->modify('+3 months'),
            TaskRecurrence::ANNUALLY => $dueDate->modify('+1 year'),
            default => null,
        };

        if ($nextDue) {
            $nextTask = ComplianceTask::create(
                id: $this->taskRepository->nextId(),
                clientId: $completedTask->clientId(),
                type: $completedTask->type(),
                description: $completedTask->description(),
                dueDate: Deadline::fromDateTime($nextDue),
                recurrence: $completedTask->recurrence(),
                reminderDays: $completedTask->reminderDays(),
            );
            $this->taskRepository->save($nextTask);
        }
    }
}
