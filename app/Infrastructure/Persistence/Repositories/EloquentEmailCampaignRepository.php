<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\EmailMarketing\Entities\EmailCampaign;
use App\Domain\EmailMarketing\Repositories\EmailCampaignRepository;
use App\Domain\EmailMarketing\ValueObjects\CampaignId;
use App\Domain\EmailMarketing\ValueObjects\CampaignType;
use App\Domain\EmailMarketing\ValueObjects\CampaignStatus;
use App\Domain\EmailMarketing\ValueObjects\TriggerType;
use App\Infrastructure\Persistence\Eloquent\EmailMarketing\EmailCampaignModel;

class EloquentEmailCampaignRepository implements EmailCampaignRepository
{
    public function save(EmailCampaign $campaign): void
    {
        $model = EmailCampaignModel::find($campaign->id()->value()) ?? new EmailCampaignModel();
        
        $model->fill([
            'name' => $campaign->name(),
            'type' => $campaign->type()->value,
            'status' => $campaign->status()->value,
            'trigger_type' => $campaign->triggerType()->value,
            'trigger_config' => $campaign->triggerConfig(),
            'start_date' => $campaign->startDate(),
            'end_date' => $campaign->endDate(),
            'created_by' => $campaign->createdBy(),
        ]);
        
        $model->save();
    }

    public function findById(CampaignId $id): ?EmailCampaign
    {
        $model = EmailCampaignModel::find($id->value());
        
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByType(CampaignType $type): array
    {
        $models = EmailCampaignModel::where('type', $type->value)->get();
        
        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function findByStatus(CampaignStatus $status): array
    {
        $models = EmailCampaignModel::where('status', $status->value)->get();
        
        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function findActiveByType(CampaignType $type): ?EmailCampaign
    {
        $model = EmailCampaignModel::where('type', $type->value)
            ->where('status', 'active')
            ->first();
        
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function delete(CampaignId $id): void
    {
        EmailCampaignModel::destroy($id->value());
    }

    public function all(): array
    {
        $models = EmailCampaignModel::all();
        
        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    private function toDomainEntity(EmailCampaignModel $model): EmailCampaign
    {
        return new EmailCampaign(
            CampaignId::fromInt($model->id),
            $model->name,
            CampaignType::from($model->type),
            CampaignStatus::from($model->status),
            TriggerType::from($model->trigger_type),
            $model->trigger_config,
            $model->start_date ? new \DateTimeImmutable($model->start_date->toDateTimeString()) : null,
            $model->end_date ? new \DateTimeImmutable($model->end_date->toDateTimeString()) : null,
            $model->created_by,
            new \DateTimeImmutable($model->created_at->toDateTimeString()),
            new \DateTimeImmutable($model->updated_at->toDateTimeString())
        );
    }
}
