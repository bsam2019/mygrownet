<?php

declare(strict_types=1);

namespace App\Domain\GrowStart\Repositories;

use Illuminate\Support\Collection;

interface ProviderRepositoryInterface
{
    public function findById(int $id): ?array;

    public function findAll(?int $countryId = null, ?string $category = null): Collection;
}