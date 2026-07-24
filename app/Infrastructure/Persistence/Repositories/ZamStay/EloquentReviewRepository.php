<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\ZamStay;

use App\Domain\ZamStay\Entities\Review;
use App\Domain\ZamStay\Repositories\ReviewRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\ZamStay\ZamStayReviewModel;

class EloquentReviewRepository implements ReviewRepositoryInterface
{
    public function findById(int $id): ?Review
    {
        $model = ZamStayReviewModel::find($id);
        return $model ? Review::reconstitute($model->toArray()) : null;
    }

    public function save(Review $entity): Review
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            ZamStayReviewModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = ZamStayReviewModel::create($data);
        return Review::reconstitute($model->toArray());
    }

    public function findByBooking(int $bookingId): ?Review
    {
        $model = ZamStayReviewModel::where('booking_id', $bookingId)->first();
        return $model ? Review::reconstitute($model->toArray()) : null;
    }

    public function findByProperty(int $propertyId): array
    {
        return ZamStayReviewModel::where('property_id', $propertyId)
            ->get()
            ->map(fn($m) => Review::reconstitute($m->toArray()))
            ->toArray();
    }

    public function existsByBooking(int $bookingId): bool
    {
        return ZamStayReviewModel::where('booking_id', $bookingId)->exists();
    }
}
