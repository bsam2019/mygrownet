<?php

namespace App\Notifications\CMS;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ApprovalActionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $approval
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $typeLabel = ucwords(str_replace('_', ' ', $this->approval['request_type']));
        $action = ucfirst($this->approval['action']);
        
        $mail = (new MailMessage)
            ->subject('Approval ' . $action . ' - ' . $typeLabel)
            ->greeting('Hello')
            ->line('Your ' . strtolower($typeLabel) . ' approval request has been ' . strtolower($action) . '.')
            ->line('Type: ' . $typeLabel)
            ->line('Amount: K' . number_format($this->approval['amount'], 2))
            ->line('Status: ' . $action);

        if ($this->approval['reason']) {
            $mail->line('Reason: ' . $this->approval['reason']);
        }

        return $mail->action('View Details', url('/cms/approvals/' . $this->approval['request_id']));
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'approval_action',
            'title' => 'Approval ' . ucfirst($this->approval['action']),
            'message' => 'Your ' . ucwords(str_replace('_', ' ', $this->approval['request_type'])) . ' request has been ' . $this->approval['action'],
            'request_id' => $this->approval['request_id'],
            'request_type' => $this->approval['request_type'],
            'amount' => $this->approval['amount'],
            'action' => $this->approval['action'],
            'reason' => $this->approval['reason'] ?? null,
            'url' => '/cms/approvals/' . $this->approval['request_id'],
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
