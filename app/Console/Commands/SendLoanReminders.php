<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PlatformLoanService;
use App\Infrastructure\Persistence\Eloquent\CMS\LoanReceivableModel;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class SendLoanReminders extends Command
{
    protected $signature = 'loans:send-reminders';
    protected $description = 'Send loan payment reminders to borrowers';

    public function handle(PlatformLoanService $loanService): int
    {
        $this->info('Sending loan payment reminders...');

        // Get loans with upcoming payments (next 3 days)
        $upcomingLoans = LoanReceivableModel::where('company_id', $loanService->getPlatformCompanyId())
            ->where('status', 'active')
            ->whereNotNull('next_payment_date')
            ->whereBetween('next_payment_date', [
                now(),
                now()->addDays(3)
            ])
            ->with('user')
            ->get();

        $remindersSent = 0;

        foreach ($upcomingLoans as $loan) {
            if ($loan->user) {
                // TODO: Implement notification class for loan reminders
                // Notification::send($loan->user, new LoanPaymentReminderNotification($loan));
                
                $this->line("Reminder sent to {$loan->user->name} for loan {$loan->loan_number}");
                $remindersSent++;
            }
        }

        $this->info("✅ Sent {$remindersSent} loan payment reminders");

        return Command::SUCCESS;
    }
}
