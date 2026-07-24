<?php

namespace App\Infrastructure\Persistence\Repositories\GrowMart;

use App\Domain\GrowMart\Repositories\OrderRepositoryInterface;
use App\Models\GrowMart\GrowMartOrder;
use App\Models\GrowMart\GrowMartOrderItem;
use Illuminate\Support\Facades\DB;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function findById(int $id): ?array
    {
        $model = GrowMartOrder::with(['items', 'coupon', 'user'])->find($id);
        return $model?->toArray();
    }

    public function findByUser(int $userId, array $options = []): array
    {
        $perPage = $options['per_page'] ?? 20;
        $query = GrowMartOrder::where('user_id', $userId)
            ->with('items')
            ->latest();
        return $query->paginate($perPage)->toArray();
    }

    public function findAll(array $filters = [], int $perPage = 20): array
    {
        $query = GrowMartOrder::with(['items', 'user']);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('order_number', 'like', '%' . $filters['search'] . '%')
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', '%' . $filters['search'] . '%'));
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        $sort = $filters['sort'] ?? 'latest';
        $query->orderBy($sort === 'oldest' ? 'created_at' : 'created_at', $sort === 'oldest' ? 'asc' : 'desc');

        return $query->paginate($perPage)->withQueryString()->toArray();
    }

    public function save(array $data): array
    {
        $model = GrowMartOrder::create($data);
        return $model->fresh()->toArray();
    }

    public function update(int $id, array $data): array
    {
        $model = GrowMartOrder::findOrFail($id);
        $model->update($data);
        return $model->fresh()->toArray();
    }

    public function delete(int $id): bool
    {
        return GrowMartOrder::destroy($id) > 0;
    }

    public function findItems(int $orderId): array
    {
        return GrowMartOrderItem::where('order_id', $orderId)->get()->toArray();
    }

    public function addItem(int $orderId, array $data): array
    {
        $item = GrowMartOrderItem::create(array_merge(['order_id' => $orderId], $data));
        return $item->toArray();
    }

    public function countByStatus(string $status): int
    {
        return GrowMartOrder::where('status', $status)->count();
    }

    public function sumByStatus(string $status, string $column): int
    {
        return (int) GrowMartOrder::where('status', $status)->sum($column);
    }

    public function revenueOverTime(string $period, int $days): array
    {
        $format = match ($period) {
            'weekly' => '%Y-%u',
            'monthly' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $data = GrowMartOrder::where('status', 'delivered')
            ->where('created_at', '>=', now()->subDays($days))
            ->select(
                DB::raw(DB::connection()->getDriverName() === 'sqlite'
                    ? "strftime('{$format}', created_at) as period"
                    : "DATE_FORMAT(created_at, '{$format}') as period"),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'labels' => $data->pluck('period')->toArray(),
            'revenue' => $data->pluck('revenue')->map(fn($v) => (int) $v)->toArray(),
            'counts' => $data->pluck('count')->toArray(),
        ];
    }

    public function orderStatusBreakdown(): array
    {
        $statuses = ['pending', 'confirmed', 'processing', 'out_for_delivery', 'delivered', 'cancelled'];
        $data = [];
        foreach ($statuses as $s) {
            $data[] = [
                'status' => str_replace('_', ' ', ucfirst($s)),
                'count' => GrowMartOrder::where('status', $s)->count(),
            ];
        }
        return $data;
    }

    public function averageOrderValue(): array
    {
        $avg = GrowMartOrder::where('status', 'delivered')
            ->select(DB::raw('AVG(total) as aov'), DB::raw('COUNT(*) as total_orders'))
            ->first();

        return [
            'aov' => (int) ($avg?->aov ?? 0),
            'aov_formatted' => 'K' . number_format(($avg?->aov ?? 0) / 100, 2),
            'total_orders' => (int) ($avg?->total_orders ?? 0),
        ];
    }

    public function countToday(): int
    {
        return GrowMartOrder::whereDate('created_at', today())->count();
    }
}
