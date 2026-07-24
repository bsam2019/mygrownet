<?php

namespace App\Domain\Marketplace\Repositories;

interface EscrowRepositoryInterface
{
    public function findByOrderId(int $orderId): ?array;
    public function create(array $data): array;
    public function updateByOrderId(int $orderId, array $data): void;
    public function getTotalHeldBalance(): int;
    public function getSellerPendingBalance(int $sellerId): int;
}
