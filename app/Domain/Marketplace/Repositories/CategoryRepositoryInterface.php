<?php

namespace App\Domain\Marketplace\Repositories;

interface CategoryRepositoryInterface
{
    public function getActiveCategories(): array;
    public function findById(int $id): ?array;
    public function findBySlug(string $slug): ?array;
    public function getParentCategories(): array;
}
