<?php

namespace App\Infrastructure\Persistence\Repositories\GrowMart;

use App\Domain\GrowMart\Repositories\ProductRepositoryInterface;
use App\Models\GrowMart\GrowMartProduct;
use App\Models\GrowMart\GrowMartInventory;
use Illuminate\Support\Facades\DB;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function findById(int $id): ?array
    {
        $model = GrowMartProduct::with(['category', 'images', 'inventory'])->find($id);
        return $model?->toArray();
    }

    public function findBySlug(string $slug): ?array
    {
        $model = GrowMartProduct::with(['category', 'images', 'inventory'])
            ->where('slug', $slug)
            ->first();
        return $model?->toArray();
    }

    public function findAll(array $filters = []): array
    {
        $query = GrowMartProduct::with(['category', 'images'])
            ->withSum('inventory', 'quantity');

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            });
        }

        if (!empty($filters['category'])) {
            $query->whereHas('category', function ($q) use ($filters) {
                $q->where('slug', $filters['category'])
                  ->orWhere('parent_id', function ($q) use ($filters) {
                      $q->from('growmart_categories')->where('slug', $filters['category'])->value('id');
                  });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $sort = $filters['sort'] ?? 'latest';
        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name' => $query->orderBy('name'),
            default => $query->latest(),
        };

        return $query->paginate($filters['per_page'] ?? 20)->withQueryString()->toArray();
    }

    public function findActive(array $filters = []): array
    {
        $filters['status'] = 'active';
        return $this->findAll($filters);
    }

    public function findFeatured(int $limit = 12): array
    {
        $models = GrowMartProduct::with(['category', 'images'])
            ->where('status', 'active')
            ->latest()
            ->take($limit)
            ->get();
        return $models->toArray();
    }

    public function findRelated(int $productId, int $categoryId, int $limit = 6): array
    {
        $models = GrowMartProduct::with(['images'])
            ->where('category_id', $categoryId)
            ->where('id', '!=', $productId)
            ->where('status', 'active')
            ->take($limit)
            ->get();
        return $models->toArray();
    }

    public function findTopSelling(int $limit = 10): array
    {
        return GrowMartProduct::select('growmart_products.id', 'growmart_products.name', 'growmart_products.price')
            ->selectSub(function ($q) {
                $q->from('growmart_order_items')
                    ->whereColumn('product_id', 'growmart_products.id')
                    ->select(DB::raw('COALESCE(SUM(quantity), 0)'));
            }, 'total_sold')
            ->selectSub(function ($q) {
                $q->from('growmart_order_items')
                    ->join('growmart_orders', 'growmart_order_items.order_id', '=', 'growmart_orders.id')
                    ->whereColumn('product_id', 'growmart_products.id')
                    ->where('growmart_orders.status', 'delivered')
                    ->select(DB::raw('COALESCE(SUM(growmart_order_items.subtotal), 0)'));
            }, 'total_revenue')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function findWithLowStock(): array
    {
        return GrowMartInventory::with('product')
            ->whereColumn('quantity', '<=', 'low_stock_threshold')
            ->where(function ($q) {
                $q->whereNull('alert_sent_at')
                    ->orWhere('alert_sent_at', '<', now()->subHours(24));
            })
            ->get()
            ->toArray();
    }

    public function countActive(): int
    {
        return GrowMartProduct::where('status', 'active')->count();
    }

    public function countAll(): int
    {
        return GrowMartProduct::count();
    }

    public function save(array $data): array
    {
        $model = GrowMartProduct::create($data);
        return $model->fresh()->toArray();
    }

    public function update(int $id, array $data): array
    {
        $model = GrowMartProduct::findOrFail($id);
        $model->update($data);
        return $model->fresh()->toArray();
    }

    public function delete(int $id): bool
    {
        return GrowMartProduct::destroy($id) > 0;
    }

    public function findInventoryWithFilters(array $filters = []): array
    {
        $query = GrowMartInventory::with(['product.category', 'warehouse']);

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $query->whereHas('product', fn($qry) => $qry->where('name', 'like', "%{$q}%"));
        }

        if (!empty($filters['low_stock'])) {
            $query->whereColumn('quantity', '<=', 'low_stock_threshold');
        }

        return $query->orderBy('quantity')
            ->paginate($filters['per_page'] ?? 30)
            ->withQueryString()
            ->toArray();
    }

    public function updateInventory(int $inventoryId, array $data): void
    {
        GrowMartInventory::where('id', $inventoryId)->update($data);
    }

    public function findAllWarehouses(): array
    {
        return \App\Models\GrowMart\GrowMartWarehouse::orderBy('name')->get()->toArray();
    }
}
