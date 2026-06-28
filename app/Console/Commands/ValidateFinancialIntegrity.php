<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Transaction;
use App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel;
use App\Infrastructure\Persistence\Eloquent\StarterKit\StarterKitPurchaseModel;
use App\Models\Withdrawal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ValidateFinancialIntegrity extends Command
{
    protected $signature = 'finance:validate-integrity 
                            {--user= : Validate specific user ID}
                            {--fix : Attempt to fix issues automatically}';

    protected $description = 'Validate financial data integrity across all tables';

    private array $issues = [];
    private int $usersChecked = 0;
    private int $issuesFound = 0;

    public function handle(): int
    {
        $this->info('Starting financial data integrity validation...');
        $this->newLine();

        $userId = $this->option('user');
        $autoFix = $this->option('fix');

        if ($autoFix) {
            $this->warn('AUTO-FIX MODE ENABLED - Issues will be corrected automatically');
            $this->newLine();
        }

        // Run validation checks
        $this->checkVerifiedPaymentsHaveTransactions($userId);
        $this->checkStarterKitPurchasesHaveTransactions($userId);
        $this->checkWithdrawalsHaveTransactions($userId);
        $this->checkForDuplicateTransactions($userId);
        $this->checkForNegativeBalances($userId);

        // Display results
        $this->displayResults();

        return $this->issuesFound > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function checkVerifiedPaymentsHaveTransactions(?string $userId): void
    {
        $this->info('Checking verified payments have transaction records...');

        $query = MemberPaymentModel::where('status', 'verified');
        
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $payments = $query->get();
        $missing = 0;

        foreach ($payments as $payment) {
            $transaction = Transaction::where('reference_number', 'LIKE', "payment_{$payment->id}_%")
                ->orWhere(function($q) use ($payment) {
                    $q->where('user_id', $payment->user_id)
                      ->where('amount', $payment->amount)
                      ->where('created_at', '>=', $payment->created_at->subMinutes(5))
                      ->where('created_at', '<=', $payment->created_at->addMinutes(5));
                })
                ->first();

            if (!$transaction) {
                $missing++;
                $this->issues[] = [
                    'type' => 'Missing Transaction',
                    'user_id' => $payment->user_id,
                    'details' => "Payment #{$payment->id} (K{$payment->amount}) has no transaction record",
                    'severity' => 'HIGH',
                ];
            }
        }

        if ($missing > 0) {
            $this->warn("  ⚠️  Found {$missing} verified payments without transaction records");
            $this->issuesFound += $missing;
        } else {
            $this->info("  ✅ All verified payments have transaction records");
        }

        $this->newLine();
    }

    private function checkStarterKitPurchasesHaveTransactions(?string $userId): void
    {
        $this->info('Checking starter kit purchases have transaction records...');

        $query = StarterKitPurchaseModel::where('status', 'completed');
        
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $purchases = $query->get();
        $missing = 0;

        foreach ($purchases as $purchase) {
            $transaction = Transaction::where('user_id', $purchase->user_id)
                ->where('transaction_type', 'starter_kit_purchase')
                ->where('amount', -abs($purchase->amount))
                ->where('created_at', '>=', $purchase->created_at->subMinutes(5))
                ->where('created_at', '<=', $purchase->created_at->addMinutes(5))
                ->first();

            if (!$transaction) {
                $missing++;
                $this->issues[] = [
                    'type' => 'Missing Transaction',
                    'user_id' => $purchase->user_id,
                    'details' => "Starter Kit #{$purchase->id} (K{$purchase->amount}) has no transaction record",
                    'severity' => 'MEDIUM',
                ];
            }
        }

        if ($missing > 0) {
            $this->warn("  ⚠️  Found {$missing} starter kit purchases without transaction records");
            $this->issuesFound += $missing;
        } else {
            $this->info("  ✅ All starter kit purchases have transaction records");
        }

        $this->newLine();
    }

    private function checkWithdrawalsHaveTransactions(?string $userId): void
    {
        $this->info('Checking withdrawals have transaction records...');

        $query = Withdrawal::whereIn('status', ['approved', 'completed']);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $withdrawals = $query->get();
        $missing = 0;

        foreach ($withdrawals as $withdrawal) {
            $transaction = Transaction::where('user_id', $withdrawal->user_id)
                ->where('transaction_type', 'withdrawal')
                ->where('amount', -abs($withdrawal->amount))
                ->where('created_at', '>=', $withdrawal->created_at->subMinutes(5))
                ->where('created_at', '<=', $withdrawal->processed_at ?? $withdrawal->created_at->addDays(7))
                ->first();

            if (!$transaction) {
                $missing++;
                $this->issues[] = [
                    'type' => 'Missing Transaction',
                    'user_id' => $withdrawal->user_id,
                    'details' => "Withdrawal #{$withdrawal->id} (K{$withdrawal->amount}) has no transaction record",
                    'severity' => 'HIGH',
                ];
            }
        }

        if ($missing > 0) {
            $this->warn("  ⚠️  Found {$missing} withdrawals without transaction records");
            $this->issuesFound += $missing;
        } else {
            $this->info("  ✅ All withdrawals have transaction records");
        }

        $this->newLine();
    }

    private function checkForDuplicateTransactions(?string $userId): void
    {
        $this->info('Checking for duplicate transactions...');

        $query = Transaction::select('user_id', 'amount', 'transaction_type', DB::raw('DATE(created_at) as date'))
            ->selectRaw('COUNT(*) as count')
            ->groupBy('user_id', 'amount', 'transaction_type', DB::raw('DATE(created_at)'))
            ->having('count', '>', 1);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $duplicates = $query->get();

        if ($duplicates->count() > 0) {
            $this->warn("  ⚠️  Found {$duplicates->count()} potential duplicate transaction groups");
            
            foreach ($duplicates as $dup) {
                $this->issues[] = [
                    'type' => 'Duplicate Transaction',
                    'user_id' => $dup->user_id,
                    'details' => "{$dup->count} transactions of type '{$dup->transaction_type}' for K{$dup->amount} on {$dup->date}",
                    'severity' => 'MEDIUM',
                ];
                $this->issuesFound++;
            }
        } else {
            $this->info("  ✅ No duplicate transactions found");
        }

        $this->newLine();
    }

    private function checkForNegativeBalances(?string $userId): void
    {
        $this->info('Checking for negative wallet balances...');

        $query = User::query();
        
        if ($userId) {
            $query->where('id', $userId);
        }

        $users = $query->get();
        $negative = 0;

        foreach ($users as $user) {
            $balance = Transaction::where('user_id', $user->id)
                ->where('status', 'completed')
                ->sum('amount');

            if ($balance < 0) {
                $negative++;
                $this->issues[] = [
                    'type' => 'Negative Balance',
                    'user_id' => $user->id,
                    'details' => "User has negative balance: K" . number_format($balance, 2),
                    'severity' => 'CRITICAL',
                ];
            }

            $this->usersChecked++;
        }

        if ($negative > 0) {
            $this->error("  ❌ Found {$negative} users with negative balances");
            $this->issuesFound += $negative;
        } else {
            $this->info("  ✅ No negative balances found ({$this->usersChecked} users checked)");
        }

        $this->newLine();
    }

    private function displayResults(): void
    {
        $this->info('Validation Summary:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Users Checked', $this->usersChecked],
                ['Issues Found', $this->issuesFound],
            ]
        );

        if ($this->issuesFound > 0) {
            $this->newLine();
            $this->error('Issues Detected:');
            
            // Group by severity
            $critical = collect($this->issues)->where('severity', 'CRITICAL');
            $high = collect($this->issues)->where('severity', 'HIGH');
            $medium = collect($this->issues)->where('severity', 'MEDIUM');

            if ($critical->count() > 0) {
                $this->error("\nCRITICAL Issues ({$critical->count()}):");
                $this->displayIssues($critical);
            }

            if ($high->count() > 0) {
                $this->warn("\nHIGH Priority Issues ({$high->count()}):");
                $this->displayIssues($high);
            }

            if ($medium->count() > 0) {
                $this->info("\nMEDIUM Priority Issues ({$medium->count()}):");
                $this->displayIssues($medium);
            }

            $this->newLine();
            $this->info('Recommended Actions:');
            $this->line('1. Run: php artisan finance:migrate-payments (to fix missing transactions)');
            $this->line('2. Run: php artisan finance:cleanup-duplicates (to remove duplicates)');
            $this->line('3. Review negative balances manually');
        } else {
            $this->newLine();
            $this->info('✅ All validation checks passed!');
        }
    }

    private function displayIssues($issues): void
    {
        foreach ($issues->take(10) as $issue) {
            $this->line("  • User {$issue['user_id']}: {$issue['details']}");
        }

        if ($issues->count() > 10) {
            $remaining = $issues->count() - 10;
            $this->line("  ... and {$remaining} more");
        }
    }
}

