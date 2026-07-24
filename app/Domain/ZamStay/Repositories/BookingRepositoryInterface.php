<?php

declare(strict_types=1);

namespace App\Domain\ZamStay\Repositories;

use App\Domain\ZamStay\Entities\Booking;

interface BookingRepositoryInterface
{
    public function findById(int $id): ?Booking;

    public function save(Booking $booking): Booking;

    public function delete(int $id): bool;

    public function findByUser(int $userId): array;

    public function findByProperty(int $propertyId): array;

    public function findByPropertyIds(array $propertyIds): array;

    public function findOverlapping(int $propertyId, string $checkIn, string $checkOut, ?int $excludeId = null): array;

    public function findForHost(int $ownerId): array;

    public function findByAgent(int $agentId): array;

    public function countByPropertyIds(array $propertyIds, ?string $status = null): int;

    public function sumByPropertyIds(array $propertyIds, string $status = 'confirmed'): float;
}
