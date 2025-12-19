<?php

namespace App\Notifications;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DelegationGrantedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected array $permissions,
        protected string $grantedBy
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count = count($this->permissions);
        
        return (new MailMessage)
            ->subject('New Permissions Granted - MyGrowNet Workspace')
            ->greeting('Hello ' . ($notifiable->first_name ?? $notifiable->name) . '!')
            ->line("You have been granted {$count} new delegated permission(s) in the Workspace.")
            ->line('Permissions granted:')
            ->lines(array_map(fn($p) => "• {$p}", array_slice($this->permissions, 0, 5)))
            ->when($count > 5, fn($mail) => $mail->line("...and " . ($count - 5) . " more"))
            ->action('View Workspace', url('/workspace/delegated'))
            ->line('These permissions allow you to perform specific administrative tasks.')
            ->line('If you have questions, please contact your manager.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'delegation_granted',
            'title' => 'New Permissions Granted',
            'message' => count($this->permissions) . ' new permission(s) have been delegated to you.',
            'permissions' => $this->permissions,
            'granted_by' => $this->grantedBy,
            'action_url' => '/workspace/delegated',
        ];
    }
}
