<?php

declare(strict_types=1);

namespace App\Infrastructure\GrowStart\Repositories;

use App\Domain\GrowStart\Repositories\CountryRepositoryInterface;
use App\Models\GrowStart\Country;
use Illuminate\Support\Collection;

class EloquentCountryRepository implements CountryRepositoryInterface
{
    public function findById(int $id): ?array
    {
        $country = Country::find($id);
        return $country ? $country->toArray() : null;
    }

    public function findAllActive(): Collection
    {
        return Country::active()->get();
    }
}