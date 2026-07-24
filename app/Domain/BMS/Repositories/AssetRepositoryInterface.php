<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\Asset;

interface AssetRepositoryInterface
{
    public function findById(int $id): ?Asset;

    public function save(Asset $asset): Asset;

    public function findByCompany(int $companyId): array;

    public function findByType(int $companyId, string $type): array;
}
