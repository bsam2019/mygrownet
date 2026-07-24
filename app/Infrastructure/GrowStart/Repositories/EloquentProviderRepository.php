<?php

declare(strict_types=1);

namespace App\Infrastructure\GrowStart\Repositories;

use App\Domain\GrowStart\Repositories\ProviderRepositoryInterface;
use App\Models\GrowStart\PartnerProvider;
use Illuminate\Support\Collection;

class EloquentProviderRepository implements ProviderRepositoryInterface
{
    public function findById(int $id): ?array
    {
        $provider = PartnerProvider::with('country')->find($id);
        return $provider ? $provider->toArray() : null;
    }

    public function findAll(?int $countryId = null, ?string $category = null): Collection
    {
        $query = PartnerProvider::query();

        if ($countryId !== null) {
            $query->forCountry($countryId);
        }

        if ($category !== null) {
            $query->byCategory($category);
        }

        return $query->orderByDesc('is_featured')
            ->orderByDesc('rating')
            ->get();
    }
}