<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\Vendor;

interface VendorRepositoryInterface
{
    public function findById(int $id): ?Vendor;

    public function save(Vendor $vendor): Vendor;

    public function findByCompany(int $companyId): array;

    public function findActive(int $companyId): array;
}
