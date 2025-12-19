<?php

namespace App\Notifications;

use App\Models\DelegationApprovalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DelegationApprovalNeededNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected DelegationApprovalRequest $approvalRequest,
        protected string $employeeName,
        protected string $actionDescription
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Approval Required - Delegated Action')
            ->greeting('Hello ' . ($notifiable->first_name ?? $notifiable->name) . ',')
            ->line("{$this->employeeName} has requested approval for a delegated action.")
            ->line("Action: {$this->actionDescription}")
            ->when(
                $this->approvalRequest->action_data['amount'] ?? null,
                fn($mail) => $mail->line("Amount: K" . number_format($this->approvalRequest->action_data['amount'], 2))
            )
            ->action('Review Request', url('/workspace/delegated/approvals'))
            ->line('Please review and approve or reject this request.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'delegation_approval_needed',
            'title' => 'Approval Required',
            'message' => "{$this->employeeName} needs approval for: {$this->actionDescription}",
            'approval_request_id' => $this->approvalRequest->id,
            'employee_name' => $this->employeeName,
            'action_type' => $this->approvalRequest->action_type,
            'action_url' => '/workspace/delegated/approvals',
        ];
    }
}
