<?php

declare(strict_types=1);

namespace App\Infrastructure\GrowStart\Repositories;

use App\Domain\GrowStart\Entities\Task as TaskEntity;
use App\Domain\GrowStart\Entities\UserTask as UserTaskEntity;
use App\Domain\GrowStart\Repositories\TaskRepositoryInterface;
use App\Domain\GrowStart\ValueObjects\TaskStatus;
use App\Models\GrowStart\Task;
use App\Models\GrowStart\UserTask;
use App\Models\GrowStart\UserJourney;
use Illuminate\Support\Collection;
use DateTimeImmutable;

class EloquentTaskRepository implements TaskRepositoryInterface
{
    public function findById(int $id): ?TaskEntity
    {
        $model = Task::find($id);
        return $model ? $this->toTaskEntity($model) : null;
    }

    public function findByStage(int $stageId, ?int $industryId = null, ?int $countryId = null): Collection
    {
        return Task::forStage($stageId)
            ->forIndustry($industryId)
            ->forCountry($countryId)
            ->ordered()
            ->get()
            ->map(fn($model) => $this->toTaskEntity($model));
    }

    public function findForJourney(int $journeyId): Collection
    {
        $journey = UserJourney::find($journeyId);
        if (!$journey) {
            return collect();
        }

        return Task::forIndustry($journey->industry_id)
            ->forCountry($journey->country_id)
            ->ordered()
            ->get()
            ->map(fn($model) => $this->toTaskEntity($model));
    }

    public function findUserTask(int $journeyId, int $taskId): ?UserTaskEntity
    {
        $model = UserTask::where('user_journey_id', $journeyId)
            ->where('task_id', $taskId)
            ->first();

        return $model ? $this->toUserTaskEntity($model) : null;
    }

    public function findUserTasksByJourney(int $journeyId): Collection
    {
        return UserTask::where('user_journey_id', $journeyId)
            ->with('task')
            ->get()
            ->map(fn($model) => $this->toUserTaskEntity($model));
    }

    public function findUserTasksByStage(int $journeyId, int $stageId): Collection
    {
        return UserTask::where('user_journey_id', $journeyId)
            ->whereHas('task', fn($q) => $q->where('stage_id', $stageId))
            ->with('task')
            ->get()
            ->map(fn($model) => $this->toUserTaskEntity($model));
    }

    public function saveUserTask(UserTaskEntity $userTask): UserTaskEntity
    {
        $data = [
            'user_journey_id' => $userTask->getUserJourneyId(),
            'task_id' => $userTask->getTaskId(),
            'status' => $userTask->getStatus()->value(),
            'started_at' => $userTask->getStartedAt()?->format('Y-m-d H:i:s'),
            'completed_at' => $userTask->getCompletedAt()?->format('Y-m-d H:i:s'),
            'notes' => $userTask->getNotes(),
            'attachments' => $userTask->getAttachments(),
        ];

        if ($userTask->getId() > 0) {
            $model = UserTask::find($userTask->getId());
            $model->update($data);
        } else {
            $model = UserTask::updateOrCreate(
                [
                    'user_journey_id' => $userTask->getUserJourneyId(),
                    'task_id' => $userTask->getTaskId(),
                ],
                $data
            );
        }

        return $this->toUserTaskEntity($model->fresh());
    }

    public function countCompletedByJourney(int $journeyId): int
    {
        return UserTask::where('user_journey_id', $journeyId)
            ->whereIn('status', ['completed', 'skipped'])
            ->count();
    }

    public function countTotalByJourney(int $journeyId): int
    {
        $journey = UserJourney::find($journeyId);
        if (!$journey) {
            return 0;
        }

        return Task::forIndustry($journey->industry_id)
            ->forCountry($journey->country_id)
            ->count();
    }

    public function countCompletedByStage(int $journeyId, int $stageId): int
    {
        return UserTask::where('user_journey_id', $journeyId)
            ->whereHas('task', fn($q) => $q->where('stage_id', $stageId))
            ->whereIn('status', ['completed', 'skipped'])
            ->count();
    }

    public function countTotalByStage(int $journeyId, int $stageId): int
    {
        $journey = UserJourney::find($journeyId);
        if (!$journey) {
            return 0;
        }

        return Task::forStage($stageId)
            ->forIndustry($journey->industry_id)
            ->forCountry($journey->country_id)
            ->count();
    }

    private function toTaskEntity(Task $model): TaskEntity
    {
        return TaskEntity::reconstitute(
            id: $model->id,
            stageId: $model->stage_id,
            industryId: $model->industry_id,
            countryId: $model->country_id,
            title: $model->title,
            description: $model->description,
            instructions: $model->instructions,
            externalLink: $model->external_link,
            estimatedHours: $model->estimated_hours,
            order: $model->order,
            isRequired: $model->is_required,
            isPremium: $model->is_premium
        );
    }

    private function toUserTaskEntity(UserTask $model): UserTaskEntity
    {
        return UserTaskEntity::reconstitute(
            id: $model->id,
            userJourneyId: $model->user_journey_id,
            taskId: $model->task_id,
            status: TaskStatus::fromString($model->status),
            startedAt: $model->started_at 
                ? new DateTimeImmutable($model->started_at->format('Y-m-d H:i:s')) 
                : null,
            completedAt: $model->completed_at 
                ? new DateTimeImmutable($model->completed_at->format('Y-m-d H:i:s')) 
                : null,
            notes: $model->notes,
            attachments: $model->attachments ?? [],
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s'))
        );
    }
}
