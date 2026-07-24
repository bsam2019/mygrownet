<?php

namespace App\Infrastructure\Persistence\Repositories\Marketplace;

use App\Domain\Marketplace\Repositories\EscrowRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Marketplace\MarketplaceEscrow;
use Illuminate\Support\Facades\DB;

class EloquentEscrowRepository implements EscrowRepositoryInterface
{
    public function findByOrderId(int $orderId): ?array
    {
        $model = MarketplaceEscrow::where('order_id', $orderId)->first();
        return $model ? $model->toArray() : null;
    }

    public function create(array $data): array
    {
        $model = MarketplaceEscrow::create($data);
        return $model->toArray();
    }

    public function updateByOrderId(int $orderId, array $data): void
    {
        MarketplaceEscrow::where('order_id', $orderId)->update($data);
    }

    public function getTotalHeldBalance(): int
    {
        return (int) MarketplaceEscrow::where('status', 'held')->sum('amount');
    }

    public function getSellerPendingBalance(int $sellerId): int
    {
        return (int) MarketplaceEscrow::whereHas('order', fn($q) => $q->where('seller_id', $sellerId))
            ->where('status', 'held')
            ->sum('amount');
    }
}
