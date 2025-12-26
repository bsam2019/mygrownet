<?php

declare(strict_types=1);

namespace App\Infrastructure\GrowBuilder\Repositories;

use App\Domain\GrowBuilder\Entities\Template;
use App\Domain\GrowBuilder\Repositories\TemplateRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\TemplateCategory;
use App\Domain\GrowBuilder\ValueObjects\TemplateId;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderTemplate;
use DateTimeImmutable;

class EloquentTemplateRepository implements TemplateRepositoryInterface
{
    public function findById(TemplateId $id): ?Template
    {
        $model = GrowBuilderTemplate::find($id->value());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findBySlug(string $slug): ?Template
    {
        $model = GrowBuilderTemplate::where('slug', $slug)->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findAll(): array
    {
        return GrowBuilderTemplate::orderBy('name')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function findActive(): array
    {
        return GrowBuilderTemplate::active()
            ->orderBy('name')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function findByCategory(TemplateCategory $category): array
    {
        return GrowBuilderTemplate::active()
            ->byCategory($category->value())
            ->orderBy('name')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function findFree(): array
    {
        return GrowBuilderTemplate::active()
            ->free()
            ->orderBy('name')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function findPremium(): array
    {
        return GrowBuilderTemplate::active()
            ->premium()
            ->orderBy('name')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function save(Template $template): Template
    {
        $data = [
            'name' => $template->getName(),
            'slug' => $template->getSlug(),
            'category' => $template->getCategory()->value(),
            'description' => $template->getDescription(),
            'preview_image' => $template->getPreviewImage(),
            'thumbnail' => $template->getThumbnail(),
            'structure_json' => $template->getStructureJson(),
            'default_styles' => $template->getDefaultStyles(),
            'is_premium' => $template->isPremium(),
            'price' => $template->getPrice(),
            'is_active' => $template->isActive(),
            'usage_count' => $template->getUsageCount(),
        ];

        if ($template->getId()) {
            $model = GrowBuilderTemplate::findOrFail($template->getId()->value());
            $model->update($data);
        } else {
            $model = GrowBuilderTemplate::create($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function delete(TemplateId $id): void
    {
        GrowBuilderTemplate::destroy($id->value());
    }

    private function toDomainEntity(GrowBuilderTemplate $model): Template
    {
        return Template::reconstitute(
            id: TemplateId::fromInt($model->id),
            name: $model->name,
            slug: $model->slug,
            category: TemplateCategory::fromString($model->category),
            description: $model->description,
            previewImage: $model->preview_image,
            thumbnail: $model->thumbnail,
            structureJson: $model->structure_json ?? [],
            defaultStyles: $model->default_styles ?? [],
            isPremium: $model->is_premium,
            price: $model->price,
            isActive: $model->is_active,
            usageCount: $model->usage_count,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }
}
