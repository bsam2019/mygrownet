<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\QuickInvoice;

use App\Domain\QuickInvoice\Entities\Template;
use App\Domain\QuickInvoice\Repositories\TemplateRepositoryInterface;
use App\Models\QuickInvoice\CustomTemplate;

class EloquentTemplateRepository implements TemplateRepositoryInterface
{
    public function findById(int $id): ?Template
    {
        $model = CustomTemplate::find($id);
        return $model ? Template::reconstitute($model->toArray()) : null;
    }

    public function findByUser(int $userId): array
    {
        return CustomTemplate::where('user_id', $userId)
            ->latest()
            ->get()
            ->map(fn($m) => Template::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findAll(): array
    {
        return CustomTemplate::all()
            ->map(fn($m) => Template::reconstitute($m->toArray()))
            ->toArray();
    }

    public function save(Template $template): Template
    {
        $data = $template->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at'], $data['last_used_at']);

        if ($id) {
            CustomTemplate::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = CustomTemplate::create($data);
        return Template::reconstitute($model->toArray());
    }

    public function delete(int $id): bool
    {
        return CustomTemplate::destroy($id) > 0;
    }

    public function replicate(int $id, int $newUserId, string $newName): ?Template
    {
        $original = CustomTemplate::find($id);
        if (!$original) {
            return null;
        }

        $new = $original->replicate();
        $new->name = $newName;
        $new->user_id = $newUserId;
        $new->usage_count = 0;
        $new->last_used_at = null;
        $new->save();

        return Template::reconstitute($new->toArray());
    }
}