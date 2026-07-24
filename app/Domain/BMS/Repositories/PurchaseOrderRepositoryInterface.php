<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\PurchaseOrder;

interface PurchaseOrderRepositoryInterface
{
    public function findById(int $id): ?PurchaseOrder;

    public function save(PurchaseOrder $order): PurchaseOrder;

    public function findByCompany(int $companyId): array;

    public function findByVendor(int $vendorId): array;

    public function findByStatus(int $companyId, string $status): array;
}
