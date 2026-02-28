<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Domain\Wallet\Services\UnifiedWalletService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Monitor Wallet Balances
 * 
 * Daily health check for wallet system:
 * - Detects negative balances
 * - Identifies balance discrepancies
 * - Alerts administrators
 * 
 * Schedule: Daily at 2 AM
 */
class MonitorWalletBalances extends Command
{
    protected $signature = 'wallet:monitor
                          {--alert : Send email alerts for issues}
                          {--fix : Attempt to fix negative balances}';

    protected $description = 'Monitor wallet balances for issues and anomalies';

    private UnifiedWalletService $walletService;

    public function __construct(UnifiedWalletService $walletService)
    {
        parent::__construct();
        $this->walletService = $walletService;
    }

    public function handle(): int
    {
        $this->info('🔍 Starting wallet balance monitoring...');
        
        $issues = [
            'negative_balances' => [],
            'large_balances' => [],
            'zero_with_transactions' => [],
        ];
        
        $stats = [
            'total_users' => 0,
            'users_with_balance' => 0,
            'total_balance' => 0,
            'issues_found' => 0,
        ];
        
        // Check all users with transactions
        $users = User::whereHas('transactions')->get();
        $stats['total_users'] = $users->count();
        
        $this->info("Checking {$stats['total_users']} users...");
        $progressBar = $this->output->createProgressBar($stats['total_users']);
        
        foreach ($users as $user) {
            $balance = $this->walletService->calculateBalance($user);
            
            if ($balance > 0) {
                $stats['users_with_balance']++;
                $stats['total_balance'] += $balance;
            }
            
            // Check for negative balances
            if ($balance < 0) {
                $issues['negative_balances'][] = [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'balance' => $balance,
                ];
                $stats['issues_found']++;
            }
            
            // Check for suspiciously large balances (> K100,000)
            if ($balance > 100000) {
                $issues['large_balances'][] = [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'balance' => $balance,
                ];
            }
            
            // Check for zero balance with transactions
            $transactionCount = $user->transactions()->count();
            if ($balance == 0 && $transactionCount > 0) {
                $issues['zero_with_transactions'][] = [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'transaction_count' => $transactionCount,
                ];
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        // Display results
        $this->displayResults($stats, $issues);
        
        // Log results
        $this->logResults($stats, $issues);
        
        // Send alerts if requested
        if ($this->option('alert') && $stats['issues_found'] > 0) {
            $this->sendAlerts($stats, $issues);
        }
        
        // Attempt fixes if requested
        if ($this->option('fix') && count($issues['negative_balances']) > 0) {
            $this->attemptFixes($issues['negative_balances']);
        }
        
        return $stats['issues_found'] > 0 ? self::FAILURE : self::SUCCESS;
    }
    
    private function displayResults(array $stats, array $issues): void
    {
        $this->info('📊 Wallet Health Report');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Users Checked', number_format($stats['total_users'])],
                ['Users with Balance', number_format($stats['users_with_balance'])],
                ['Total System Balance', 'K' . number_format($stats['total_balance'], 2)],
                ['Issues Found', $stats['issues_found']],
            ]
        );
        
        if (count($issues['negative_balances']) > 0) {
            $this->error('❌ Negative Balances Found: ' . count($issues['negative_balances']));
            $this->table(
                ['User ID', 'Name', 'Email', 'Balance'],
                array_map(function($issue) {
                    return [
                        $issue['user_id'],
                        $issue['name'],
                        $issue['email'],
                        'K' . number_format($issue['balance'], 2),
                    ];
                }, array_slice($issues['negative_balances'], 0, 10))
            );
            
            if (count($issues['negative_balances']) > 10) {
                $this->warn('... and ' . (count($issues['negative_balances']) - 10) . ' more');
            }
        } else {
            $this->info('✅ No negative balances found');
        }
        
        if (count($issues['large_balances']) > 0) {
            $this->warn('⚠️  Large Balances (>K100,000): ' . count($issues['large_balances']));
        }
        
        if (count($issues['zero_with_transactions']) > 0) {
            $this->warn('⚠️  Zero Balance with Transactions: ' . count($issues['zero_with_transactions']));
        }
    }
    
    private function logResults(array $stats, array $issues): void
    {
        Log::channel('daily')->info('Wallet Balance Monitoring Report', [
            'stats' => $stats,
            'negative_balances_count' => count($issues['negative_balances']),
            'large_balances_count' => count($issues['large_balances']),
            'zero_with_transactions_count' => count($issues['zero_with_transactions']),
            'negative_balances' => $issues['negative_balances'],
        ]);
    }
    
    private function sendAlerts(array $stats, array $issues): void
    {
        $this->info('📧 Sending alert emails...');
        
        // TODO: Implement email alerts to administrators
        // Mail::to(config('app.admin_email'))->send(new WalletHealthAlert($stats, $issues));
        
        $this->warn('Email alerts not yet implemented');
    }
    
    private function attemptFixes(array $negativeBalances): void
    {
        $this->info('🔧 Attempting to fix negative balances...');
        
        foreach ($negativeBalances as $issue) {
            $this->warn("User {$issue['user_id']} ({$issue['name']}): K{$issue['balance']}");
            $this->warn('Manual investigation required - automatic fixes not safe');
        }
        
        $this->info('Run: php artisan finance:diagnose-balance {user_id} for detailed analysis');
    }
}
