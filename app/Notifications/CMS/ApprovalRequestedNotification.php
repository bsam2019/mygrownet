<?php

namespace App\Notifications\CMS;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ApprovalRequestedNotification extends Notification implements ShouldQueue
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
        
        return (new MailMessage)
            ->subject('Approval Required - ' . $typeLabel)
            ->greeting('Hello')
            ->line('A ' . strtolower($typeLabel) . ' requires your approval.')
            ->line('Type: ' . $typeLabel)
            ->line('Amount: K' . number_format($this->approval['amount'], 2))
            ->line('Requested by: ' . $this->approval['requested_by'])
            ->line('Approval Level: ' . $this->approval['step_level'])
            ->action('Review Request', url('/cms/approvals/' . $this->approval['request_id']))
            ->line('Please review and approve or reject this request.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'approval_requested',
            'title' => 'Approval Required',
            'message' => ucwords(str_replace('_', ' ', $this->approval['request_type'])) . ' of K' . number_format($this->approval['amount'], 2) . ' requires your approval',
            'request_id' => $this->approval['request_id'],
            'request_type' => $this->approval['request_type'],
            'amount' => $this->approval['amount'],
            'requested_by' => $this->approval['requested_by'],
            'step_level' => $this->approval['step_level'],
            'url' => '/cms/approvals/' . $this->approval['request_id'],
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
