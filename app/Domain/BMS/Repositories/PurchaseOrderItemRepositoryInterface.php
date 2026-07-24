<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\PurchaseOrderItem;

interface PurchaseOrderItemRepositoryInterface
{
    public function findById(int $id): ?PurchaseOrderItem;

    public function save(PurchaseOrderItem $item): PurchaseOrderItem;

    public function findByPurchaseOrder(int $purchaseOrderId): array;

    public function deleteByPurchaseOrder(int $purchaseOrderId): void;
}
