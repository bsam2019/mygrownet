<?php

namespace App\Infrastructure\Persistence\Eloquent\LoyaltyReward;

use App\Domain\LoyaltyReward\Entities\LoyaltyActivity;
use App\Domain\LoyaltyReward\Repositories\LoyaltyActivityRepository;
use App\Domain\LoyaltyReward\ValueObjects\ActivityType;
use DateTimeImmutable;

class EloquentLoyaltyActivityRepository implements LoyaltyActivityRepository
{
    public function save(LoyaltyActivity $activity): void
    {
        $model = $activity->getId() > 0
            ? LoyaltyActivityModel::find($activity->getId())
            : new LoyaltyActivityModel();

        if (!$model) {
            $model = new LoyaltyActivityModel();
        }

        $model->fill([
            'user_id' => $activity->getUserId(),
            'cycle_id' => $activity->getCycleId(),
            'activity_type' => $activity->getType()->value,
            'description' => $activity->getDescription(),
            'performed_at' => $activity->getPerformedAt(),
            'verified' => $activity->isVerified(),
        ]);

        $model->save();
    }

    public function findByCycleId(int $cycleId): array
    {
        $models = LoyaltyActivityModel::where('cycle_id', $cycleId)
            ->orderBy('performed_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function findByUserIdAndDate(int $userId, DateTimeImmutable $date): array
    {
        $models = LoyaltyActivityModel::where('user_id', $userId)
            ->whereDate('performed_at', $date->format('Y-m-d'))
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function hasActivityForDate(int $cycleId, DateTimeImmutable $date): bool
    {
        return LoyaltyActivityModel::where('cycle_id', $cycleId)
            ->whereDate('performed_at', $date->format('Y-m-d'))
            ->exists();
    }

    private function toDomainEntity(LoyaltyActivityModel $model): LoyaltyActivity
    {
        return new LoyaltyActivity(
            $model->id,
            $model->user_id,
            $model->cycle_id,
            ActivityType::from($model->activity_type),
            $model->description,
            new DateTimeImmutable($model->performed_at->format('Y-m-d H:i:s')),
            $model->verified
        );
    }
}
