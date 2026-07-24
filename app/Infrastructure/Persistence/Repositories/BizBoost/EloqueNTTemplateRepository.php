<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\BizBoost;

use App\Domain\BizBoost\Entities\Template;
use App\Domain\BizBoost\Repositories\TemplateRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BizBoostTemplateModel;

class EloquentTemplateRepository implements TemplateRepositoryInterface
{
    public function findById(int $id): ?Template
    {
        $model = BizBoostTemplateModel::find($id);
        return $model ? Template::reconstitute($model->toArray()) : null;
    }

    public function findActive(array $filters = []): array
    {
        $query = BizBoostTemplateModel::where('is_active', true);

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (!empty($filters['industry'])) {
            $query->where('industry', $filters['industry']);
        }
        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        return $query->orderBy('sort_order')->orderByDesc('usage_count')->get()
            ->map(fn($m) => Template::reconstitute($m->toArray()))
            ->toArray();
    }

    public function save(Template $entity): Template
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            BizBoostTemplateModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = BizBoostTemplateModel::create($data);
        return Template::reconstitute($model->toArray());
    }

    public function incrementUsage(int $id): void
    {
        BizBoostTemplateModel::where('id', $id)->increment('usage_count');
    }
}