<?php

namespace App\Domain\ZamStay\Services;

use App\Domain\ZamStay\Entities\Review;
use App\Domain\ZamStay\Exceptions\ZamStayException;
use App\Domain\ZamStay\Repositories\BookingRepositoryInterface;
use App\Domain\ZamStay\Repositories\ReviewRepositoryInterface;

class ReviewService
{
    public function __construct(
        private readonly ReviewRepositoryInterface $reviewRepo,
        private readonly BookingRepositoryInterface $bookingRepo,
    ) {}

    public function create(int $userId, array $data): Review
    {
        $booking = $this->bookingRepo->findById((int) $data['booking_id']);
        if (!$booking) {
            throw ZamStayException::notFound('Booking');
        }

        if ($booking->userId !== $userId) {
            throw ZamStayException::unauthorized();
        }

        if ($booking->status !== 'confirmed') {
            throw ZamStayException::invalidOperation('You can only review confirmed bookings.');
        }

        if ($this->reviewRepo->existsByBooking($booking->id)) {
            throw ZamStayException::invalidOperation('You have already reviewed this booking.');
        }

        $review = Review::reconstitute([
            'booking_id' => $booking->id,
            'user_id' => $userId,
            'property_id' => $booking->propertyId,
            'rating' => (int) $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return $this->reviewRepo->save($review);
    }
}
