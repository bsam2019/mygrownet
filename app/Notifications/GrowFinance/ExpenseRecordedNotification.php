<?php

namespace App\Notifications\GrowFinance;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpenseRecordedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private int $expenseId,
        private string $category,
        private string $description,
        private float $amount,
        private string $paymentMethod
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Expense Recorded: K' . number_format($this->amount, 2))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new expense has been recorded.')
            ->line('**Category:** ' . $this->category)
            ->line('**Description:** ' . $this->description)
            ->line('**Amount:** K' . number_format($this->amount, 2))
            ->line('**Payment Method:** ' . ucfirst(str_replace('_', ' ', $this->paymentMethod)))
            ->action('View Expense', url('/growfinance/expenses/' . $this->expenseId))
            ->line('Thank you for using GrowFinance!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Expense Recorded',
            'message' => "{$this->description} - K" . number_format($this->amount, 2),
            'type' => 'info',
            'module' => 'growfinance',
            'category' => 'expenses',
            'action_url' => '/growfinance/expenses/' . $this->expenseId,
            'action_text' => 'View Expense',
            'expense_id' => $this->expenseId,
            'expense_category' => $this->category,
            'description' => $this->description,
            'amount' => $this->amount,
            'payment_method' => $this->paymentMethod,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
