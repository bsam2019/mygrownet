<?php

declare(strict_types=1);

namespace App\Infrastructure\GrowStart\Repositories;

use App\Domain\GrowStart\Repositories\IndustryRepositoryInterface;
use App\Models\GrowStart\Industry;
use Illuminate\Support\Collection;

class EloquentIndustryRepository implements IndustryRepositoryInterface
{
    public function findById(int $id): ?array
    {
        $industry = Industry::find($id);
        return $industry ? $industry->toArray() : null;
    }

    public function findAllActive(): Collection
    {
        return Industry::active()->ordered()->get();
    }
}