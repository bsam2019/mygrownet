<?php

namespace App\Notifications\GrowBuilder;

use App\Models\AgencyClientService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceRenewalReminder extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public AgencyClientService $service,
        public int $daysUntilRenewal
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('growbuilder.services.show', $this->service->id);

        return (new MailMessage)
            ->subject("Service Renewal Reminder: {$this->service->service_name}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("This is a reminder that the following service is due for renewal in {$this->daysUntilRenewal} days:")
            ->line("**Service:** {$this->service->service_name}")
            ->line("**Client:** {$this->service->client->client_name}")
            ->line("**Renewal Date:** {$this->service->renewal_date->format('M d, Y')}")
            ->line("**Amount:** " . ($this->service->client->agency->currency ?? 'ZMW') . " " . number_format($this->service->total_price, 2))
            ->action('View Service', $url)
            ->line('Please contact your client to confirm renewal or update the service status.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'service_renewal_reminder',
            'service_id' => $this->service->id,
            'service_name' => $this->service->service_name,
            'client_id' => $this->service->client_id,
            'client_name' => $this->service->client->client_name,
            'renewal_date' => $this->service->renewal_date->format('Y-m-d'),
            'days_until_renewal' => $this->daysUntilRenewal,
            'amount' => $this->service->total_price,
            'url' => route('growbuilder.services.show', $this->service->id),
        ];
    }
}
