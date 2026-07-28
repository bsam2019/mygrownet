<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowBuilder;

use App\Domain\GrowBuilder\Entities\Template;
use App\Domain\GrowBuilder\Repositories\TemplateRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\TemplateCategory;
use App\Domain\GrowBuilder\ValueObjects\TemplateId;
use App\Models\GrowBuilder\SiteTemplate;
use DateTimeImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentTemplateRepository implements TemplateRepositoryInterface
{
    public function findById(TemplateId $id): ?Template
    {
        $model = SiteTemplate::find($id->value());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findActive(): array
    {
        return SiteTemplate::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function findByIndustry(string $industry): array
    {
        return SiteTemplate::where('is_active', true)
            ->where('industry', $industry)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function findFree(): array
    {
        return SiteTemplate::where('is_active', true)
            ->where('is_premium', false)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function findPremium(): array
    {
        return SiteTemplate::where('is_active', true)
            ->where('is_premium', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function findAllPaginated(int $perPage = 20): LengthAwarePaginator
    {
        $paginator = SiteTemplate::where('is_active', true)
            ->orderBy('sort_order')
            ->paginate($perPage);

        $paginator->getCollection()->transform(fn($model) => $this->toDomainEntity($model));

        return $paginator;
    }

    public function findBySlug(string $slug): ?Template
    {
        $model = SiteTemplate::where('slug', $slug)->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function getIndustries(): array
    {
        return SiteTemplate::select('industry')
            ->distinct()
            ->whereNotNull('industry')
            ->where('is_active', true)
            ->orderBy('industry')
            ->pluck('industry')
            ->toArray();
    }

    public function save(Template $template): Template
    {
        $data = [
            'name' => $template->getName(),
            'slug' => $template->getSlug(),
            'industry' => $template->getCategory()->value(),
            'description' => $template->getDescription() ?? '',
            'thumbnail' => $template->getThumbnail(),
            'theme' => array_merge($template->getDefaultStyles(), ['structure' => $template->getStructureJson()]),
            'is_premium' => $template->isPremium(),
            'is_active' => $template->isActive(),
            'usage_count' => $template->getUsageCount(),
        ];

        if ($template->getId()) {
            $model = SiteTemplate::findOrFail($template->getId()->value());
            $model->update($data);
        } else {
            $model = SiteTemplate::create($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function delete(TemplateId $id): void
    {
        SiteTemplate::destroy($id->value());
    }

    private function toDomainEntity(SiteTemplate $model): Template
    {
        $theme = $model->theme ?? [];
        $structure = $theme['structure'] ?? [];
        unset($theme['structure']);

        return Template::reconstitute(
            id: TemplateId::fromInt($model->id),
            name: $model->name,
            slug: $model->slug,
            category: TemplateCategory::fromString($model->industry ?? 'general'),
            description: $model->description,
            previewImage: null,
            thumbnail: $model->thumbnail,
            structureJson: $structure,
            defaultStyles: $theme,
            isPremium: $model->is_premium ?? false,
            price: 0,
            isActive: $model->is_active ?? true,
            usageCount: $model->usage_count ?? 0,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }
}
