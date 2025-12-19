<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DelegationRevokedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected array $permissions,
        protected string $revokedBy,
        protected ?string $reason = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Permissions Revoked - MyGrowNet Workspace')
            ->greeting('Hello ' . ($notifiable->first_name ?? $notifiable->name) . ',')
            ->line('Some of your delegated permissions have been revoked.')
            ->line('Permissions revoked:')
            ->lines(array_map(fn($p) => "• {$p}", $this->permissions));
        
        if ($this->reason) {
            $mail->line("Reason: {$this->reason}");
        }
        
        return $mail->line('If you have questions, please contact your manager.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'delegation_revoked',
            'title' => 'Permissions Revoked',
            'message' => count($this->permissions) . ' permission(s) have been revoked.',
            'permissions' => $this->permissions,
            'revoked_by' => $this->revokedBy,
            'reason' => $this->reason,
        ];
    }
}
