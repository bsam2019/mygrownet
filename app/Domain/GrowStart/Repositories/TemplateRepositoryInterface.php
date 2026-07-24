<?php

declare(strict_types=1);

namespace App\Domain\GrowStart\Repositories;

use Illuminate\Support\Collection;

interface TemplateRepositoryInterface
{
    public function findById(int $id): ?array;

    public function findAll(?int $countryId = null, ?string $category = null): Collection;

    public function findByCategory(string $category, ?int $countryId = null): Collection;

    public function incrementDownloads(int $id): void;
}