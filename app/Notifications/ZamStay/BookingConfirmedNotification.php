<?php

namespace App\Notifications\ZamStay;

use App\Mail\GenericNotificationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected array $booking,
        protected string $recipientType = 'guest',
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): GenericNotificationMail
    {
        $propertyTitle = $this->booking['property']['title'] ?? 'Property';

        if ($this->recipientType === 'host') {
            return new GenericNotificationMail(
                subject: "Booking Confirmed: {$propertyTitle}",
                greeting: "Hello {$notifiable->name},",
                message: "The booking for {$propertyTitle} has been confirmed.",
                actionText: 'View Booking',
                actionUrl: url('/zamstay/host/bookings'),
                details: [
                    'Guest' => $this->booking['user']['name'] ?? 'Guest',
                    'Check-in' => $this->booking['check_in'] ?? '',
                    'Check-out' => $this->booking['check_out'] ?? '',
                    'Guests' => (string) ($this->booking['guests'] ?? ''),
                    'Total' => 'ZMW ' . number_format((float) ($this->booking['total_price'] ?? 0), 2),
                ],
            );
        }

        return new GenericNotificationMail(
            subject: "Booking Confirmed: {$propertyTitle}",
            greeting: "Hello {$notifiable->name},",
            message: "Your booking for {$propertyTitle} is confirmed!",
            actionText: 'View Booking Details',
            actionUrl: url("/zamstay/bookings/{$this->booking['id']}"),
            details: [
                'Check-in' => $this->booking['check_in'] ?? '',
                'Check-out' => $this->booking['check_out'] ?? '',
                'Guests' => (string) ($this->booking['guests'] ?? ''),
                'Total paid' => 'ZMW ' . number_format((float) ($this->booking['total_price'] ?? 0), 2),
                'Location' => $this->booking['property']['location'] ?? '',
            ],
            footerNote: 'Thank you for booking with ZamStay!',
        );
    }

    public function toArray(object $notifiable): array
    {
        $propertyTitle = $this->booking['property']['title'] ?? 'Property';

        return [
            'type' => 'booking_confirmed',
            'booking_id' => $this->booking['id'],
            'property_title' => $propertyTitle,
            'check_in' => $this->booking['check_in'],
            'check_out' => $this->booking['check_out'],
            'total_price' => $this->booking['total_price'],
            'message' => "Booking confirmed for {$propertyTitle}",
        ];
    }
}
