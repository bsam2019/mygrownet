<?php

namespace App\Infrastructure\Persistence\Repositories\GrowMart;

use App\Domain\GrowMart\Repositories\ReviewRepositoryInterface;
use App\Models\GrowMart\GrowMartReview;

class EloquentReviewRepository implements ReviewRepositoryInterface
{
    public function findById(int $id): ?array
    {
        $model = GrowMartReview::with(['user', 'product'])->find($id);
        return $model?->toArray();
    }

    public function findByProduct(int $productId, bool $approvedOnly = true): array
    {
        $query = GrowMartReview::with('user')->where('product_id', $productId);
        if ($approvedOnly) {
            $query->where('is_approved', true);
        }
        return $query->latest()->get()->toArray();
    }

    public function findByUser(int $userId): array
    {
        return GrowMartReview::where('user_id', $userId)->get()->toArray();
    }

    public function findAll(array $filters = [], int $perPage = 20): array
    {
        $query = GrowMartReview::with(['user', 'product']);

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'pending') {
                $query->where('is_approved', false);
            } elseif ($filters['status'] === 'approved') {
                $query->where('is_approved', true);
            }
        }

        if (!empty($filters['search'])) {
            $q = $filters['search'];
            $query->where(function ($qry) use ($q) {
                $qry->where('review_text', 'like', "%{$q}%")
                    ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$q}%"))
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$q}%"));
            });
        }

        return $query->latest()->paginate($perPage)->withQueryString()->toArray();
    }

    public function findUserReview(int $userId, int $productId): ?array
    {
        $model = GrowMartReview::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();
        return $model?->toArray();
    }

    public function save(array $data): array
    {
        $model = GrowMartReview::create($data);
        return $model->fresh()->toArray();
    }

    public function update(int $id, array $data): array
    {
        $model = GrowMartReview::findOrFail($id);
        $model->update($data);
        return $model->fresh()->toArray();
    }

    public function delete(int $id): bool
    {
        return GrowMartReview::destroy($id) > 0;
    }

    public function getAverageRating(int $productId): float
    {
        $avg = GrowMartReview::where('product_id', $productId)
            ->where('is_approved', true)
            ->avg('rating');
        return $avg ? round($avg, 1) : 0.0;
    }

    public function countPending(): int
    {
        return GrowMartReview::where('is_approved', false)->count();
    }
}
