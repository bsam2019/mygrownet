<?php

declare(strict_types=1);

namespace App\Notifications\GrowBiz;

use DateTimeImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $employeeName,
        private string $businessName,
        private ?string $position,
        private string $invitationToken,
        private DateTimeImmutable $expiresAt
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $acceptUrl = url("/growbiz/invitation/accept/{$this->invitationToken}");
        $positionText = $this->position ? " as {$this->position}" : '';

        return (new MailMessage)
            ->subject("You're invited to join {$this->businessName} on GrowBiz")
            ->greeting("Hello {$this->employeeName}!")
            ->line("{$this->businessName} has invited you to join their team{$positionText}.")
            ->line('GrowBiz helps businesses manage tasks and collaborate with their team efficiently.')
            ->action('Accept Invitation', $acceptUrl)
            ->line("This invitation expires on {$this->expiresAt->format('F j, Y')}.")
            ->line('If you did not expect this invitation, you can safely ignore this email.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'employee_name' => $this->employeeName,
            'business_name' => $this->businessName,
            'position' => $this->position,
            'expires_at' => $this->expiresAt->format('Y-m-d H:i:s'),
        ];
    }
}
