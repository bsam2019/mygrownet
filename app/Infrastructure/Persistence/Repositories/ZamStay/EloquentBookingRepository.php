<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\ZamStay;

use App\Domain\ZamStay\Entities\Booking;
use App\Domain\ZamStay\Repositories\BookingRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\ZamStay\ZamStayBookingModel;

class EloquentBookingRepository implements BookingRepositoryInterface
{
    public function findById(int $id): ?Booking
    {
        $model = ZamStayBookingModel::find($id);
        return $model ? Booking::reconstitute($model->toArray()) : null;
    }

    public function save(Booking $entity): Booking
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            ZamStayBookingModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = ZamStayBookingModel::create($data);
        return Booking::reconstitute($model->toArray());
    }

    public function delete(int $id): bool
    {
        return ZamStayBookingModel::where('id', $id)->delete() > 0;
    }

    public function findByUser(int $userId): array
    {
        return ZamStayBookingModel::forUser($userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => Booking::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByProperty(int $propertyId): array
    {
        return ZamStayBookingModel::where('property_id', $propertyId)
            ->get()
            ->map(fn($m) => Booking::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByPropertyIds(array $propertyIds): array
    {
        return ZamStayBookingModel::whereIn('property_id', $propertyIds)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => Booking::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findOverlapping(int $propertyId, string $checkIn, string $checkOut, ?int $excludeId = null): array
    {
        $query = ZamStayBookingModel::where('property_id', $propertyId)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereBetween('check_in', [$checkIn, $checkOut])
                  ->orWhereBetween('check_out', [$checkIn, $checkOut])
                  ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                      $q2->where('check_in', '<=', $checkIn)
                         ->where('check_out', '>=', $checkOut);
                  });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->get()
            ->map(fn($m) => Booking::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findForHost(int $ownerId): array
    {
        return ZamStayBookingModel::whereHas('property', fn($q) => $q->where('owner_id', $ownerId))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => Booking::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByAgent(int $agentId): array
    {
        return ZamStayBookingModel::where('agent_id', $agentId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => Booking::reconstitute($m->toArray()))
            ->toArray();
    }

    public function countByPropertyIds(array $propertyIds, ?string $status = null): int
    {
        $query = ZamStayBookingModel::whereIn('property_id', $propertyIds);
        if ($status) {
            $query->where('status', $status);
        }
        return $query->count();
    }

    public function sumByPropertyIds(array $propertyIds, string $status = 'confirmed'): float
    {
        return (float) ZamStayBookingModel::whereIn('property_id', $propertyIds)
            ->where('status', $status)
            ->sum('total_price');
    }
}
