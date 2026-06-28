<?php

namespace App\Infrastructure\Persistence\Eloquent\LoyaltyReward;

use App\Domain\LoyaltyReward\Entities\LoyaltyGrowthCycle;
use App\Domain\LoyaltyReward\Repositories\LoyaltyGrowthCycleRepository;
use App\Domain\LoyaltyReward\ValueObjects\CycleId;
use App\Domain\LoyaltyReward\ValueObjects\CycleStatus;
use App\Domain\LoyaltyReward\ValueObjects\LoyaltyAmount;
use DateTimeImmutable;

class EloquentLoyaltyGrowthCycleRepository implements LoyaltyGrowthCycleRepository
{
    public function save(LoyaltyGrowthCycle $cycle): void
    {
        $model = LoyaltyGrowthCycleModel::where('cycle_id', $cycle->getId()->toString())->first()
            ?? new LoyaltyGrowthCycleModel();

        $model->fill([
            'cycle_id' => $cycle->getId()->toString(),
            'user_id' => $cycle->getUserId(),
            'start_date' => $cycle->getStartDate(),
            'end_date' => $cycle->getEndDate(),
            'status' => $cycle->getStatus()->value,
            'active_days' => $cycle->getActiveDays(),
            'earned_amount' => $cycle->getEarnedAmount()->toKwacha(),
            'completed_at' => $cycle->getCompletedAt(),
        ]);

        $model->save();
    }

    public function findById(CycleId $id): ?LoyaltyGrowthCycle
    {
        $model = LoyaltyGrowthCycleModel::where('cycle_id', $id->toString())->first();

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findActiveCycleByUserId(int $userId): ?LoyaltyGrowthCycle
    {
        $model = LoyaltyGrowthCycleModel::where('user_id', $userId)
            ->where('status', CycleStatus::ACTIVE->value)
            ->first();

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findCompletedCyclesByUserId(int $userId): array
    {
        $models = LoyaltyGrowthCycleModel::where('user_id', $userId)
            ->where('status', CycleStatus::COMPLETED->value)
            ->orderBy('completed_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function findExpiredActiveCycles(): array
    {
        $models = LoyaltyGrowthCycleModel::where('status', CycleStatus::ACTIVE->value)
            ->where('end_date', '<', now())
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function hasActivityForDate(CycleId $cycleId, DateTimeImmutable $date): bool
    {
        $model = LoyaltyGrowthCycleModel::where('cycle_id', $cycleId->toString())->first();

        if (!$model) {
            return false;
        }

        return LoyaltyActivityModel::where('cycle_id', $model->id)
            ->whereDate('performed_at', $date->format('Y-m-d'))
            ->exists();
    }

    public function findAllActiveCycles(): array
    {
        $models = LoyaltyGrowthCycleModel::where('status', CycleStatus::ACTIVE->value)->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    private function toDomainEntity(LoyaltyGrowthCycleModel $model): LoyaltyGrowthCycle
    {
        return new LoyaltyGrowthCycle(
            CycleId::fromString($model->cycle_id),
            $model->user_id,
            new DateTimeImmutable($model->start_date->format('Y-m-d H:i:s')),
            new DateTimeImmutable($model->end_date->format('Y-m-d H:i:s')),
            CycleStatus::from($model->status),
            $model->active_days,
            LoyaltyAmount::fromKwacha($model->earned_amount),
            $model->completed_at ? new DateTimeImmutable($model->completed_at->format('Y-m-d H:i:s')) : null
        );
    }
}
