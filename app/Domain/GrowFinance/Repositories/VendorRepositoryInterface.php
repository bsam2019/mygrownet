<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\Vendor;

interface VendorRepositoryInterface
{
    public function findById(int $id): ?Vendor;

    public function save(Vendor $vendor): Vendor;

    public function findByBusiness(int $businessId): array;

    public function findActive(int $businessId): array;

    public function findWithOutstanding(int $businessId): array;
}
