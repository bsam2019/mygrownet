<?php

namespace App\Console\Commands\GrowFinance;

use App\Domain\GrowFinance\Services\NotificationService;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceInvoiceModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceExpenseModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceJournalEntryModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendDailySummary extends Command
{
    protected $signature = 'growfinance:send-daily-summary {--date= : Date to summarize (YYYY-MM-DD)}';
    protected $description = 'Send daily financial summary notifications to GrowFinance users';

    public function __construct(
        private NotificationService $notificationService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $date = $this->option('date') 
            ? Carbon::parse($this->option('date')) 
            : Carbon::yesterday();

        $this->info("Generating daily summaries for {$date->format('Y-m-d')}...");

        // Get all users who have GrowFinance activity
        $userIds = $this->getActiveGrowFinanceUsers($date);

        $summariesSent = 0;

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if (!$user) continue;

            $summary = $this->calculateDailySummary($userId, $date);

            // Only send if there was activity
            if ($summary['invoiceCount'] === 0 && $summary['expenseCount'] === 0) {
                continue;
            }

            try {
                $this->notificationService->notifyDailySummary(
                    $user,
                    $date->format('d M Y'),
                    $summary['totalSales'],
                    $summary['totalExpenses'],
                    $summary['netIncome'],
                    $summary['invoiceCount'],
                    $summary['expenseCount']
                );

                $summariesSent++;
                $this->line("  - Sent summary to: {$user->name}");
            } catch (\Throwable $e) {
                Log::error('Failed to send daily summary', [
                    'user_id' => $userId,
                    'date' => $date->format('Y-m-d'),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Sent {$summariesSent} daily summary notifications.");

        return Command::SUCCESS;
    }

    private function getActiveGrowFinanceUsers(Carbon $date): array
    {
        // Get users who had invoices or expenses on the given date
        $invoiceUsers = GrowFinanceInvoiceModel::whereDate('created_at', $date)
            ->pluck('business_id')
            ->toArray();

        $expenseUsers = GrowFinanceExpenseModel::whereDate('created_at', $date)
            ->pluck('business_id')
            ->toArray();

        return array_unique(array_merge($invoiceUsers, $expenseUsers));
    }

    private function calculateDailySummary(int $userId, Carbon $date): array
    {
        // Calculate total sales (invoices created)
        $invoices = GrowFinanceInvoiceModel::where('business_id', $userId)
            ->whereDate('invoice_date', $date)
            ->get();

        $totalSales = $invoices->sum('total_amount');
        $invoiceCount = $invoices->count();

        // Calculate total expenses
        $expenses = GrowFinanceExpenseModel::where('business_id', $userId)
            ->whereDate('expense_date', $date)
            ->get();

        $totalExpenses = $expenses->sum('amount');
        $expenseCount = $expenses->count();

        // Calculate net income
        $netIncome = $totalSales - $totalExpenses;

        return [
            'totalSales' => $totalSales,
            'totalExpenses' => $totalExpenses,
            'netIncome' => $netIncome,
            'invoiceCount' => $invoiceCount,
            'expenseCount' => $expenseCount,
        ];
    }
}
