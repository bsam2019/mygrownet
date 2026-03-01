<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\WalletComparisonService;
use Illuminate\Console\Command;

class CompareWalletServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet:compare 
                            {--user= : Specific user ID to compare}
                            {--all : Compare all users with wallet activity}
                            {--limit=10 : Number of users to compare (default: 10)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compare old and new wallet service implementations';

    /**
     * Execute the console command.
     */
    public function handle(WalletComparisonService $comparisonService): int
    {
        $this->info('🔍 Comparing Wallet Services...');
        $this->newLine();

        // Single user comparison
        if ($userId = $this->option('user')) {
            return $this->compareSingleUser($userId, $comparisonService);
        }

        // Multiple users comparison
        if ($this->option('all')) {
            return $this->compareAllUsers($comparisonService);
        }

        // Limited comparison (default)
        return $this->compareLimitedUsers($comparisonService);
    }

    private function compareSingleUser(int $userId, WalletComparisonService $service): int
    {
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("User {$userId} not found");
            return 1;
        }

        $this->info("Comparing wallet for: {$user->name} (ID: {$userId})");
        $this->newLine();

        $result = $service->runFullComparison($user);

        // Display balance comparison
        $this->table(
            ['Metric', 'Old Service', 'New Service', 'Match'],
            [
                [
                    'Balance',
                    'K' . number_format($result['balance']['old_balance'], 2),
                    'K' . number_format($result['balance']['new_balance'], 2),
                    $result['balance']['matches'] ? '✅' : '❌'
                ],
                [
                    'Credits',
                    'K' . number_format($result['breakdown']['old']['credits'], 2),
                    'K' . number_format($result['breakdown']['new']['credits'], 2),
                    $result['breakdown']['credits_match'] ? '✅' : '❌'
                ],
                [
                    'Debits',
                    'K' . number_format($result['breakdown']['old']['debits'], 2),
                    'K' . number_format($result['breakdown']['new']['debits'], 2),
                    $result['breakdown']['debits_match'] ? '✅' : '❌'
                ],
            ]
        );

        if ($result['balance']['matches'] && $result['breakdown']['all_match']) {
            $this->info('✅ All values match!');
            return 0;
        } else {
            $this->error('❌ Mismatch detected!');
            return 1;
        }
    }

    private function compareAllUsers(WalletComparisonService $service): int
    {
        $userIds = User::whereHas('transactions')->pluck('id')->toArray();
        
        $this->info("Comparing {count($userIds)} users with wallet activity...");
        $this->newLine();

        return $this->processComparison($userIds, $service);
    }

    private function compareLimitedUsers(WalletComparisonService $service): int
    {
        $limit = (int) $this->option('limit');
        $userIds = User::whereHas('transactions')
            ->inRandomOrder()
            ->limit($limit)
            ->pluck('id')
            ->toArray();
        
        $this->info("Comparing {$limit} random users with wallet activity...");
        $this->newLine();

        return $this->processComparison($userIds, $service);
    }

    private function processComparison(array $userIds, WalletComparisonService $service): int
    {
        $bar = $this->output->createProgressBar(count($userIds));
        $bar->start();

        $results = [];
        $mismatches = 0;

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if (!$user) continue;

            $comparison = $service->runFullComparison($user);
            
            if (!$comparison['balance']['matches'] || !$comparison['breakdown']['all_match']) {
                $mismatches++;
                $results[] = [
                    'user_id' => $userId,
                    'name' => $user->name,
                    'balance_diff' => $comparison['balance']['difference'],
                ];
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $total = count($userIds);
        $matches = $total - $mismatches;
        $successRate = $total > 0 ? round($matches / $total * 100, 2) : 0;

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Users', $total],
                ['Matches', $matches],
                ['Mismatches', $mismatches],
                ['Success Rate', $successRate . '%'],
            ]
        );

        if ($mismatches > 0) {
            $this->newLine();
            $this->error("❌ Found {$mismatches} mismatches:");
            $this->table(
                ['User ID', 'Name', 'Balance Difference'],
                array_map(fn($r) => [
                    $r['user_id'],
                    $r['name'],
                    'K' . number_format($r['balance_diff'], 2)
                ], $results)
            );
            return 1;
        }

        $this->info('✅ All comparisons match!');
        return 0;
    }
}
