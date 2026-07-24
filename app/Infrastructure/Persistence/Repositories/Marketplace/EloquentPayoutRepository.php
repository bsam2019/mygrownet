<?php

namespace App\Infrastructure\Persistence\Repositories\Marketplace;

use App\Domain\Marketplace\Repositories\PayoutRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Marketplace\MarketplacePayout;

class EloquentPayoutRepository implements PayoutRepositoryInterface
{
    public function findById(int $id): ?array
    {
        $model = MarketplacePayout::with(['seller.user', 'approvedBy', 'processedBy'])->find($id);
        return $model ? $model->toArray() : null;
    }

    public function findBySeller(int $sellerId, int $perPage = 20): array
    {
        return MarketplacePayout::with(['approvedBy', 'processedBy'])
            ->where('seller_id', $sellerId)
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->toArray();
    }

    public function findAll(array $filters = [], int $perPage = 20): array
    {
        $query = MarketplacePayout::with(['seller.user', 'approvedBy', 'processedBy']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['payout_method'])) {
            $query->where('payout_method', $filters['payout_method']);
        }
        if (!empty($filters['seller_id'])) {
            $query->where('seller_id', $filters['seller_id']);
        }

        return $query->orderByDesc('created_at')
            ->paginate($perPage)
            ->toArray();
    }

    public function hasPendingPayout(int $sellerId): bool
    {
        return MarketplacePayout::where('seller_id', $sellerId)
            ->whereIn('status', ['pending', 'approved', 'processing'])
            ->exists();
    }

    public function create(array $data): array
    {
        $model = MarketplacePayout::create($data);
        return $model->toArray();
    }

    public function update(int $payoutId, array $data): array
    {
        $model = MarketplacePayout::findOrFail($payoutId);
        $model->update($data);
        return $model->fresh()->toArray();
    }

    public function getStats(): array
    {
        return [
            'pending_count' => MarketplacePayout::where('status', 'pending')->count(),
            'pending_amount' => MarketplacePayout::where('status', 'pending')->sum('net_amount'),
            'approved_count' => MarketplacePayout::where('status', 'approved')->count(),
            'approved_amount' => MarketplacePayout::where('status', 'approved')->sum('net_amount'),
            'processing_count' => MarketplacePayout::where('status', 'processing')->count(),
            'processing_amount' => MarketplacePayout::where('status', 'processing')->sum('net_amount'),
            'completed_today' => MarketplacePayout::where('status', 'completed')
                ->whereDate('processed_at', today())->count(),
            'completed_today_amount' => MarketplacePayout::where('status', 'completed')
                ->whereDate('processed_at', today())->sum('net_amount'),
            'completed_this_month' => MarketplacePayout::where('status', 'completed')
                ->whereMonth('processed_at', now()->month)->count(),
            'completed_this_month_amount' => MarketplacePayout::where('status', 'completed')
                ->whereMonth('processed_at', now()->month)->sum('net_amount'),
        ];
    }
}
