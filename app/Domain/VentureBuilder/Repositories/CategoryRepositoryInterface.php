<?php

declare(strict_types=1);

namespace App\Domain\VentureBuilder\Repositories;

interface CategoryRepositoryInterface
{
    public function findById(int $id): ?array;
    public function findBySlug(string $slug): ?array;
    public function getActive(): array;
}
