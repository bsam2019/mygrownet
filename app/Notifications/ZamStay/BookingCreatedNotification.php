<?php

namespace App\Notifications\ZamStay;

use App\Mail\GenericNotificationMail;
use App\Models\ZamStayBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BookingCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected ZamStayBooking $booking,
        protected string $recipientType = 'guest',
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): GenericNotificationMail
    {
        $property = $this->booking->property;

        if ($this->recipientType === 'host') {
            return new GenericNotificationMail(
                subject: "New Booking Request: {$property->title}",
                greeting: "Hello {$notifiable->name},",
                message: "You have a new booking request for {$property->title}.",
                actionText: 'View Booking',
                actionUrl: url('/zamstay/host/bookings'),
                details: [
                    'Guest' => $this->booking->user->name,
                    'Check-in' => $this->booking->check_in,
                    'Check-out' => $this->booking->check_out,
                    'Guests' => (string) $this->booking->guests,
                    'Total' => 'ZMW ' . number_format($this->booking->total_price, 2),
                ],
            );
        }

        return new GenericNotificationMail(
            subject: "Booking Created: {$property->title}",
            greeting: "Hello {$notifiable->name},",
            message: "Your booking for {$property->title} has been created. Please complete payment to confirm your booking.",
            actionText: 'Complete Payment',
            actionUrl: url("/zamstay/bookings/{$this->booking->id}"),
            details: [
                'Check-in' => $this->booking->check_in,
                'Check-out' => $this->booking->check_out,
                'Guests' => (string) $this->booking->guests,
                'Total' => 'ZMW ' . number_format($this->booking->total_price, 2),
            ],
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'booking_created',
            'booking_id' => $this->booking->id,
            'property_title' => $this->booking->property->title,
            'check_in' => $this->booking->check_in,
            'check_out' => $this->booking->check_out,
            'total_price' => $this->booking->total_price,
            'recipient_type' => $this->recipientType,
            'message' => $this->recipientType === 'host'
                ? "New booking request for {$this->booking->property->title}"
                : "Booking created for {$this->booking->property->title}",
        ];
    }
}
