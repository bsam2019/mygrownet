<?php

declare(strict_types=1);

namespace App\Domain\ZamStay\Repositories;

use App\Domain\ZamStay\Entities\Review;

interface ReviewRepositoryInterface
{
    public function findById(int $id): ?Review;

    public function save(Review $review): Review;

    public function findByBooking(int $bookingId): ?Review;

    public function findByProperty(int $propertyId): array;

    public function existsByBooking(int $bookingId): bool;
}
