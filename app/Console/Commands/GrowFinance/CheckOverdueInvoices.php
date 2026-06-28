<?php

namespace App\Console\Commands\GrowFinance;

use App\Domain\GrowFinance\Services\NotificationService;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceInvoiceModel;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckOverdueInvoices extends Command
{
    protected $signature = 'growfinance:check-overdue-invoices';
    protected $description = 'Check for overdue invoices and send notifications';

    public function __construct(
        private NotificationService $notificationService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Checking for overdue invoices...');

        $overdueInvoices = GrowFinanceInvoiceModel::with(['business', 'customer'])
            ->whereIn('status', ['sent', 'partial'])
            ->where('due_date', '<', Carbon::today())
            ->get();

        $notificationsSent = 0;

        foreach ($overdueInvoices as $invoice) {
            $daysOverdue = Carbon::parse($invoice->due_date)->diffInDays(Carbon::today());
            
            // Only notify on specific intervals: 1, 3, 7, 14, 30 days overdue
            if (!in_array($daysOverdue, [1, 3, 7, 14, 30])) {
                continue;
            }

            $balanceDue = $invoice->total_amount - $invoice->amount_paid;

            if ($balanceDue <= 0) {
                continue;
            }

            try {
                $this->notificationService->notifyInvoiceOverdue(
                    $invoice->business,
                    $invoice->id,
                    $invoice->invoice_number,
                    $invoice->customer->name ?? 'Unknown Customer',
                    $balanceDue,
                    $invoice->due_date->format('d M Y'),
                    $daysOverdue
                );

                $notificationsSent++;
                $this->line("  - Notified: Invoice #{$invoice->invoice_number} ({$daysOverdue} days overdue)");
            } catch (\Throwable $e) {
                Log::error('Failed to send overdue notification', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Sent {$notificationsSent} overdue invoice notifications.");

        return Command::SUCCESS;
    }
}
