<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\FixedAsset;

interface FixedAssetRepositoryInterface
{
    public function findById(int $id): ?FixedAsset;
    public function save(FixedAsset $asset): FixedAsset;
    public function findByBusiness(int $businessId): array;
    public function findActive(int $businessId): array;
    public function findByCategory(int $businessId, string $category): array;
    public function findFullyDepreciated(int $businessId): array;
    public function delete(FixedAsset $asset): void;
}
