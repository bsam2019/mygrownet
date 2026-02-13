<?php

namespace App\Notifications\CMS;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ExpenseApprovalRequiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $expense
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Expense Approval Required - ' . $this->expense['expense_number'])
            ->greeting('Hello')
            ->line('A new expense requires your approval.')
            ->line('Expense Number: ' . $this->expense['expense_number'])
            ->line('Description: ' . $this->expense['description'])
            ->line('Amount: K' . number_format($this->expense['amount'], 2))
            ->line('Category: ' . $this->expense['category_name'])
            ->line('Submitted by: ' . $this->expense['submitted_by'])
            ->action('Review Expense', url('/cms/expenses?expense_id=' . $this->expense['id']))
            ->line('Please review and approve or reject this expense.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'expense_approval_required',
            'title' => 'Expense Approval Required',
            'message' => 'Expense of K' . number_format($this->expense['amount'], 2) . ' requires approval',
            'expense_id' => $this->expense['id'],
            'expense_number' => $this->expense['expense_number'],
            'amount' => $this->expense['amount'],
            'description' => $this->expense['description'],
            'category_name' => $this->expense['category_name'],
            'submitted_by' => $this->expense['submitted_by'],
            'url' => '/cms/expenses?expense_id=' . $this->expense['id'],
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
