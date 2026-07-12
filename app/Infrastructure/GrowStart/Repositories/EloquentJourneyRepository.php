<?php

declare(strict_types=1);

namespace App\Infrastructure\GrowStart\Repositories;

use App\Domain\GrowStart\Entities\StartupJourney;
use App\Domain\GrowStart\Repositories\JourneyRepositoryInterface;
use App\Domain\GrowStart\ValueObjects\JourneyId;
use App\Domain\GrowStart\ValueObjects\JourneyStatus;
use App\Models\GrowStart\UserJourney;
use Illuminate\Support\Collection;
use DateTimeImmutable;

class EloquentJourneyRepository implements JourneyRepositoryInterface
{
    public function findById(int $id): ?StartupJourney
    {
        $model = UserJourney::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function findByUserId(int $userId): ?StartupJourney
    {
        $model = UserJourney::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->first();
        return $model ? $this->toEntity($model) : null;
    }

    public function findActiveByUserId(int $userId): ?StartupJourney
    {
        $model = UserJourney::where('user_id', $userId)
            ->where('status', 'active')
            ->first();
        return $model ? $this->toEntity($model) : null;
    }

    public function findAllByUserId(int $userId): Collection
    {
        return UserJourney::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($model) => $this->toEntity($model));
    }

    public function save(StartupJourney $journey): StartupJourney
    {
        $data = [
            'user_id' => $journey->getUserId(),
            'industry_id' => $journey->getIndustryId(),
            'country_id' => $journey->getCountryId(),
            'business_name' => $journey->getBusinessName(),
            'business_description' => $journey->getBusinessDescription(),
            'current_stage_id' => $journey->getCurrentStageId(),
            'started_at' => $journey->getStartedAt()->format('Y-m-d H:i:s'),
            'target_launch_date' => $journey->getTargetLaunchDate()?->format('Y-m-d'),
            'status' => $journey->getStatus()->value(),
            'is_premium' => $journey->isPremium(),
            'province' => $journey->getProvince(),
            'city' => $journey->getCity(),
        ];

        if ($journey->id() > 0) {
            $model = UserJourney::find($journey->id());
            $model->update($data);
        } else {
            $model = UserJourney::create($data);
        }

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): bool
    {
        return UserJourney::destroy($id) > 0;
    }

    public function countByStatus(string $status): int
    {
        return UserJourney::where('status', $status)->count();
    }

    public function countByIndustry(int $industryId): int
    {
        return UserJourney::where('industry_id', $industryId)->count();
    }

    public function countByCountry(int $countryId): int
    {
        return UserJourney::where('country_id', $countryId)->count();
    }

    private function toEntity(UserJourney $model): StartupJourney
    {
        return StartupJourney::reconstitute(
            id: JourneyId::fromInt($model->id),
            userId: $model->user_id,
            industryId: $model->industry_id,
            countryId: $model->country_id,
            businessName: $model->business_name,
            businessDescription: $model->business_description,
            currentStageId: $model->current_stage_id,
            startedAt: new DateTimeImmutable($model->started_at->format('Y-m-d H:i:s')),
            targetLaunchDate: $model->target_launch_date 
                ? new DateTimeImmutable($model->target_launch_date->format('Y-m-d')) 
                : null,
            status: JourneyStatus::fromString($model->status),
            isPremium: $model->is_premium,
            province: $model->province,
            city: $model->city,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s'))
        );
    }
}
