<?php

declare(strict_types=1);

namespace App\Domain\ZamStay\Repositories;

use App\Domain\ZamStay\Entities\Property;

interface PropertyRepositoryInterface
{
    public function findById(int $id): ?Property;

    public function save(Property $property): Property;

    public function delete(int $id): bool;

    public function findAllActive(array $filters = []): array;

    public function findByOwner(int $ownerId): array;

    public function findFeatured(int $limit = 6): array;

    public function findLatest(int $limit = 8): array;

    public function findDistinctLocations(): array;
}
