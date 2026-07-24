<?php

namespace App\Infrastructure\Persistence\Repositories\GrowMart;

use App\Domain\GrowMart\Repositories\CouponRepositoryInterface;
use App\Models\GrowMart\GrowMartCoupon;

class EloquentCouponRepository implements CouponRepositoryInterface
{
    public function findById(int $id): ?array
    {
        $model = GrowMartCoupon::find($id);
        return $model?->toArray();
    }

    public function findByCode(string $code): ?array
    {
        $model = GrowMartCoupon::where('code', strtoupper($code))->first();
        return $model?->toArray();
    }

    public function findAll(array $filters = [], int $perPage = 20): array
    {
        $query = GrowMartCoupon::query();

        if (!empty($filters['search'])) {
            $query->where('code', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->latest()->paginate($perPage)->toArray();
    }

    public function save(array $data): array
    {
        $model = GrowMartCoupon::create($data);
        return $model->fresh()->toArray();
    }

    public function update(int $id, array $data): array
    {
        $model = GrowMartCoupon::findOrFail($id);
        $model->update($data);
        return $model->fresh()->toArray();
    }

    public function delete(int $id): bool
    {
        return GrowMartCoupon::destroy($id) > 0;
    }

    public function incrementUsage(int $id): void
    {
        GrowMartCoupon::where('id', $id)->increment('used_count');
    }
}
