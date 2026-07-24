<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\VentureBuilder;

use App\Domain\VentureBuilder\Entities\Venture;
use App\Domain\VentureBuilder\Repositories\VentureRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel;
use Illuminate\Support\Facades\DB;

class EloquentVentureRepository implements VentureRepositoryInterface
{
    public function findById(int $id): ?Venture
    {
        $model = VentureModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findBySlug(string $slug): ?Venture
    {
        $model = VentureModel::where('slug', $slug)->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findFunding(int $perPage): array
    {
        return VentureModel::funding()
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->items();
    }

    public function findFeatured(int $limit): array
    {
        return VentureModel::featured()
            ->active()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn(VentureModel $m) => $this->toDomainEntity($m))
            ->toArray();
    }

    public function findAll(array $filters = [], int $perPage = 20): array
    {
        $query = VentureModel::query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['is_featured'])) {
            $query->where('is_featured', true);
        }

        if (!empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        $query->orderByDesc('created_at');

        return $query->paginate($perPage)->items();
    }

    public function save(Venture $venture): Venture
    {
        $data = $venture->toArray();
        $id = $data['id'] ?? null;
        unset($data['id']);

        if ($id) {
            VentureModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = VentureModel::create($data);
        return $this->toDomainEntity($model);
    }

    public function updateStatus(int $id, string $status): void
    {
        VentureModel::where('id', $id)->update(['status' => $status]);
    }

    public function incrementTotalRaised(int $id, float $amount): void
    {
        VentureModel::where('id', $id)->increment('total_raised', $amount);
    }

    public function incrementInvestorCount(int $id): void
    {
        VentureModel::where('id', $id)->increment('investor_count');
    }

    public function decrementTotalRaised(int $id, float $amount): void
    {
        VentureModel::where('id', $id)->decrement('total_raised', $amount);
    }

    public function decrementInvestorCount(int $id): void
    {
        VentureModel::where('id', $id)->decrement('investor_count');
    }

    public function getStats(): array
    {
        return [
            'total_ventures' => VentureModel::count(),
            'total_funding_raised' => VentureModel::sum('total_raised'),
            'total_investors' => VentureModel::sum('investor_count'),
            'active_ventures' => VentureModel::active()->count(),
            'funding_ventures' => VentureModel::funding()->count(),
            'total_funding_target' => VentureModel::sum('funding_target'),
        ];
    }

    private function toDomainEntity(VentureModel $model): Venture
    {
        return Venture::reconstitute($model->toArray());
    }
}
