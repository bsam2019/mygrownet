<?php

namespace App\Notifications\GrowFinance;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailySummaryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $date,
        private float $totalSales,
        private float $totalExpenses,
        private float $netIncome,
        private int $invoiceCount,
        private int $expenseCount
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $netClass = $this->netIncome >= 0 ? 'profit' : 'loss';
        
        return (new MailMessage)
            ->subject("📊 Daily Summary: {$this->date}")
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("Here's your financial summary for {$this->date}:")
            ->line('**Total Sales:** K' . number_format($this->totalSales, 2) . " ({$this->invoiceCount} transactions)")
            ->line('**Total Expenses:** K' . number_format($this->totalExpenses, 2) . " ({$this->expenseCount} transactions)")
            ->line('**Net Income:** K' . number_format(abs($this->netIncome), 2) . " ({$netClass})")
            ->action('View Dashboard', url('/growfinance'))
            ->line('Thank you for using GrowFinance!');
    }

    public function toDatabase(object $notifiable): array
    {
        $netStatus = $this->netIncome >= 0 ? 'profit' : 'loss';
        
        return [
            'title' => 'Daily Financial Summary',
            'message' => "{$this->date}: Sales K" . number_format($this->totalSales, 2) . ", Expenses K" . number_format($this->totalExpenses, 2) . " (Net {$netStatus}: K" . number_format(abs($this->netIncome), 2) . ")",
            'type' => $this->netIncome >= 0 ? 'success' : 'warning',
            'module' => 'growfinance',
            'category' => 'reports',
            'action_url' => '/growfinance',
            'action_text' => 'View Dashboard',
            'date' => $this->date,
            'total_sales' => $this->totalSales,
            'total_expenses' => $this->totalExpenses,
            'net_income' => $this->netIncome,
            'invoice_count' => $this->invoiceCount,
            'expense_count' => $this->expenseCount,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
