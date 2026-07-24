<?php

namespace App\Domain\ZamStay\Services;

use App\Domain\ZamStay\Entities\Booking;
use App\Domain\ZamStay\Exceptions\ZamStayException;
use App\Domain\ZamStay\Repositories\BookingRepositoryInterface;
use App\Domain\ZamStay\Repositories\PropertyRepositoryInterface;

class BookingService
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepo,
        private readonly PropertyRepositoryInterface $propertyRepo,
    ) {}

    public function findById(int $id): ?Booking
    {
        return $this->bookingRepo->findById($id);
    }

    public function create(int $userId, array $data): Booking
    {
        $property = $this->propertyRepo->findById((int) $data['property_id']);
        if (!$property) {
            throw ZamStayException::notFound('Property');
        }

        if ($property->maxGuests < (int) ($data['guests'] ?? 1)) {
            throw ZamStayException::invalidOperation(
                'This property only accommodates up to ' . $property->maxGuests . ' guests.'
            );
        }

        $overlapping = $this->bookingRepo->findOverlapping(
            $property->id,
            $data['check_in'],
            $data['check_out']
        );

        if (!empty($overlapping)) {
            throw ZamStayException::invalidOperation('Property is not available for the selected dates.');
        }

        $checkIn = new \DateTimeImmutable($data['check_in']);
        $checkOut = new \DateTimeImmutable($data['check_out']);
        $nights = $checkIn->diff($checkOut)->days;
        $totalPrice = $nights * $property->pricePerNight;

        $bookingData = [
            'property_id' => $property->id,
            'user_id' => $userId,
            'check_in' => $data['check_in'],
            'check_out' => $data['check_out'],
            'total_price' => $totalPrice,
            'status' => 'pending',
            'guests' => (int) ($data['guests'] ?? 1),
            'special_requests' => $data['special_requests'] ?? null,
        ];

        $entity = Booking::reconstitute($bookingData);
        return $this->bookingRepo->save($entity);
    }

    public function updatePaymentInfo(int $bookingId, string $method, ?string $phone = null): Booking
    {
        $booking = $this->bookingRepo->findById($bookingId);
        if (!$booking) {
            throw ZamStayException::notFound('Booking');
        }

        $data = $booking->toArray();
        $data['payment_method'] = $method;
        $data['payment_phone'] = $phone;

        $entity = Booking::reconstitute($data);
        return $this->bookingRepo->save($entity);
    }

    public function markAsPaid(int $bookingId, string $transactionId, string $reference): Booking
    {
        $booking = $this->bookingRepo->findById($bookingId);
        if (!$booking) {
            throw ZamStayException::notFound('Booking');
        }

        $data = $booking->toArray();
        $data['status'] = 'confirmed';
        $data['paid_at'] = now()->format('Y-m-d H:i:s');
        $data['transaction_id'] = $transactionId;
        $data['payment_reference'] = $reference;

        $entity = Booking::reconstitute($data);
        return $this->bookingRepo->save($entity);
    }

    public function checkAvailability(int $propertyId, string $checkIn, string $checkOut): array
    {
        $property = $this->propertyRepo->findById($propertyId);
        if (!$property) {
            throw ZamStayException::notFound('Property');
        }

        $overlapping = $this->bookingRepo->findOverlapping($propertyId, $checkIn, $checkOut);
        $available = empty($overlapping);

        $checkInDt = new \DateTimeImmutable($checkIn);
        $checkOutDt = new \DateTimeImmutable($checkOut);
        $nights = $checkInDt->diff($checkOutDt)->days;
        $totalPrice = $nights * $property->pricePerNight;

        return [
            'available' => $available,
            'total_price' => $totalPrice,
            'nights' => $nights,
            'price_per_night' => $property->pricePerNight,
        ];
    }

    public function findByUser(int $userId): array
    {
        return $this->bookingRepo->findByUser($userId);
    }

    public function cancel(int $bookingId, int $userId): Booking
    {
        $booking = $this->bookingRepo->findById($bookingId);
        if (!$booking) {
            throw ZamStayException::notFound('Booking');
        }
        if ($booking->userId !== $userId) {
            throw ZamStayException::unauthorized();
        }

        $updated = Booking::reconstitute(array_merge($booking->toArray(), ['status' => 'cancelled']));
        return $this->bookingRepo->save($updated);
    }

    public function confirm(int $bookingId, int $ownerId): Booking
    {
        $booking = $this->bookingRepo->findById($bookingId);
        if (!$booking) {
            throw ZamStayException::notFound('Booking');
        }

        $property = $this->propertyRepo->findById($booking->propertyId);
        if (!$property || $property->ownerId !== $ownerId) {
            throw ZamStayException::unauthorized();
        }

        $updated = Booking::reconstitute(array_merge($booking->toArray(), ['status' => 'confirmed']));
        return $this->bookingRepo->save($updated);
    }

    public function getHostBookings(int $ownerId): array
    {
        return $this->bookingRepo->findForHost($ownerId);
    }

    public function getHostStats(int $ownerId): array
    {
        $properties = $this->propertyRepo->findByOwner($ownerId);
        $propertyIds = array_map(fn($p) => $p->id, $properties);

        return [
            'total_properties' => count($properties),
            'active_properties' => count(array_filter($properties, fn($p) => $p->isActive)),
            'total_bookings' => $this->bookingRepo->countByPropertyIds($propertyIds),
            'pending_bookings' => $this->bookingRepo->countByPropertyIds($propertyIds, 'pending'),
            'confirmed_bookings' => $this->bookingRepo->countByPropertyIds($propertyIds, 'confirmed'),
            'total_revenue' => $this->bookingRepo->sumByPropertyIds($propertyIds, 'confirmed'),
        ];
    }
}
