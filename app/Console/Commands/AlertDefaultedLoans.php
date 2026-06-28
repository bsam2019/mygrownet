<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PlatformLoanService;
use App\Infrastructure\Persistence\Eloquent\CMS\LoanReceivableModel;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class AlertDefaultedLoans extends Command
{
    protected $signature = 'loans:alert-defaults';
    protected $description = 'Send alerts for defaulted loans to admins';

    public function handle(PlatformLoanService $loanService): int
    {
        $this->info('Checking for defaulted loans...');

        // Get newly defaulted loans (90+ days overdue)
        $defaultedLoans = LoanReceivableModel::where('company_id', $loanService->getPlatformCompanyId())
            ->where('status', 'active')
            ->where('days_overdue', '>=', 90)
            ->where('risk_category', '!=', 'default')
            ->with('user')
            ->get();

        if ($defaultedLoans->isEmpty()) {
            $this->info('No newly defaulted loans found');
            return Command::SUCCESS;
        }

        // Get admin users
        $admins = User::role('admin')->get();

        foreach ($defaultedLoans as $loan) {
            // Update loan status to defaulted
            $loan->update([
                'status' => 'defaulted',
                'risk_category' => 'default'
            ]);

            // TODO: Implement notification class for default alerts
            // Notification::send($admins, new LoanDefaultAlertNotification($loan));

            $this->warn("⚠️  Loan {$loan->loan_number} marked as defaulted (Borrower: {$loan->user->name})");
        }

        $this->info("✅ Processed {$defaultedLoans->count()} defaulted loans");

        return Command::SUCCESS;
    }
}
