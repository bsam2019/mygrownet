<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\ZamStay;

use App\Domain\ZamStay\Entities\Property;
use App\Domain\ZamStay\Repositories\PropertyRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\ZamStay\ZamStayPropertyModel;

class EloquentPropertyRepository implements PropertyRepositoryInterface
{
    public function findById(int $id): ?Property
    {
        $model = ZamStayPropertyModel::find($id);
        return $model ? Property::reconstitute($model->toArray()) : null;
    }

    public function save(Property $entity): Property
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            ZamStayPropertyModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = ZamStayPropertyModel::create($data);
        return Property::reconstitute($model->toArray());
    }

    public function delete(int $id): bool
    {
        return ZamStayPropertyModel::where('id', $id)->delete() > 0;
    }

    public function findAllActive(array $filters = []): array
    {
        $query = ZamStayPropertyModel::active();

        if (!empty($filters['location'])) {
            $query->where('location', 'like', '%' . $filters['location'] . '%');
        }

        if (!empty($filters['property_type'])) {
            $query->where('property_type', $filters['property_type']);
        }

        if (isset($filters['min_price'])) {
            $query->where('price_per_night', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price_per_night', '<=', $filters['max_price']);
        }

        if (isset($filters['guests'])) {
            $query->where('max_guests', '>=', $filters['guests']);
        }

        return $query->get()->map(fn($m) => Property::reconstitute($m->toArray()))->toArray();
    }

    public function findByOwner(int $ownerId): array
    {
        return ZamStayPropertyModel::where('owner_id', $ownerId)
            ->get()
            ->map(fn($m) => Property::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findFeatured(int $limit = 6): array
    {
        return ZamStayPropertyModel::active()
            ->inRandomOrder()
            ->take($limit)
            ->get()
            ->map(fn($m) => Property::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findLatest(int $limit = 8): array
    {
        return ZamStayPropertyModel::active()
            ->latest()
            ->take($limit)
            ->get()
            ->map(fn($m) => Property::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findDistinctLocations(): array
    {
        return ZamStayPropertyModel::active()
            ->select('location')
            ->distinct()
            ->pluck('location')
            ->toArray();
    }
}
