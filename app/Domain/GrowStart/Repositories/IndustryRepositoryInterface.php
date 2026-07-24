<?php

declare(strict_types=1);

namespace App\Domain\GrowStart\Repositories;

use Illuminate\Support\Collection;

interface IndustryRepositoryInterface
{
    public function findById(int $id): ?array;

    public function findAllActive(): Collection;
}