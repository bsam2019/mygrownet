<?php

declare(strict_types=1);

namespace App\Infrastructure\GrowStart\Repositories;

use App\Domain\GrowStart\Repositories\TemplateRepositoryInterface;
use App\Models\GrowStart\Template;
use Illuminate\Support\Collection;

class EloquentTemplateRepository implements TemplateRepositoryInterface
{
    public function findById(int $id): ?array
    {
        $template = Template::find($id);
        return $template ? $template->toArray() : null;
    }

    public function findAll(?int $countryId = null, ?string $category = null): Collection
    {
        $query = Template::query();

        if ($countryId !== null) {
            $query->forCountry($countryId);
        }

        if ($category !== null) {
            $query->byCategory($category);
        }

        return $query->orderBy('name')->get();
    }

    public function findByCategory(string $category, ?int $countryId = null): Collection
    {
        $query = Template::byCategory($category);

        if ($countryId !== null) {
            $query->forCountry($countryId);
        }

        return $query->orderBy('name')->get();
    }

    public function incrementDownloads(int $id): void
    {
        Template::where('id', $id)->increment('download_count');
    }
}