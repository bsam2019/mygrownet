<?php

namespace App\Notifications\ZamStay;

use App\Mail\GenericNotificationMail;
use App\Models\ZamStayBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BookingCancelledNotification extends Notification implements ShouldQueue
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
                subject: "Booking Cancelled: {$property->title}",
                greeting: "Hello {$notifiable->name},",
                message: "A booking for {$property->title} has been cancelled. These dates are now available for new bookings.",
                actionText: 'View Properties',
                actionUrl: url('/zamstay/host/properties'),
                details: [
                    'Guest' => $this->booking->user->name,
                    'Check-in' => $this->booking->check_in,
                    'Check-out' => $this->booking->check_out,
                ],
            );
        }

        return new GenericNotificationMail(
            subject: "Booking Cancelled: {$property->title}",
            greeting: "Hello {$notifiable->name},",
            message: "Your booking for {$property->title} has been cancelled. If you paid, the refund will be processed within 5-7 business days.",
            actionText: 'Browse Properties',
            actionUrl: url('/zamstay/search'),
            details: [
                'Check-in' => $this->booking->check_in,
                'Check-out' => $this->booking->check_out,
            ],
            footerNote: 'We hope to see you again soon!',
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'booking_cancelled',
            'booking_id' => $this->booking->id,
            'property_title' => $this->booking->property->title,
            'check_in' => $this->booking->check_in,
            'check_out' => $this->booking->check_out,
            'message' => "Booking cancelled for {$this->booking->property->title}",
        ];
    }
}
