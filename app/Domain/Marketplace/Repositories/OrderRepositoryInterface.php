<?php

namespace App\Domain\Marketplace\Repositories;

use App\Domain\Marketplace\Entities\Order;

interface OrderRepositoryInterface
{
    public function findById(int $id): ?Order;
    public function findByOrderNumber(string $orderNumber): ?Order;
    public function findByBuyer(int $buyerId, array $filters = [], int $perPage = 20): array;
    public function findBySeller(int $sellerId, array $filters = [], int $perPage = 20): array;
    public function findPendingAutoRelease(): array;
    public function save(Order $order): Order;
    public function updateStatus(int $orderId, string $status): void;
    public function markAsDelivered(int $orderId): void;
    public function markAsConfirmed(int $orderId): void;
}
