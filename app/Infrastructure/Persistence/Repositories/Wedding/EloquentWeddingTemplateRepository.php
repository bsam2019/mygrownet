<?php

namespace App\Infrastructure\Persistence\Repositories\Wedding;

use App\Domain\Wedding\Entities\WeddingTemplate;
use App\Domain\Wedding\Repositories\WeddingTemplateRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Wedding\WeddingTemplateModel;

class EloquentWeddingTemplateRepository implements WeddingTemplateRepositoryInterface
{
    public function findById(int $id): ?WeddingTemplate
    {
        $model = WeddingTemplateModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findBySlug(string $slug): ?WeddingTemplate
    {
        $model = WeddingTemplateModel::where('slug', $slug)->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findAll(): array
    {
        return WeddingTemplateModel::orderBy('name')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findActive(): array
    {
        return WeddingTemplateModel::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function save(WeddingTemplate $template): WeddingTemplate
    {
        $data = [
            'name' => $template->getName(),
            'slug' => $template->getSlug(),
            'description' => $template->getDescription(),
            'preview_image' => $template->getPreviewImage(),
            'settings' => $template->getSettings(),
            'is_active' => $template->isActive(),
            'is_premium' => $template->isPremium(),
        ];

        if ($template->getId()) {
            $model = WeddingTemplateModel::findOrFail($template->getId());
            $model->update($data);
        } else {
            $model = WeddingTemplateModel::create($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function delete(int $id): bool
    {
        return WeddingTemplateModel::destroy($id) > 0;
    }

    private function toDomainEntity(WeddingTemplateModel $model): WeddingTemplate
    {
        return WeddingTemplate::fromArray($model->toArray());
    }
}
