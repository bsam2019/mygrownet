<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MonitorAccountIntegrity extends Command
{
    protected $signature = 'accounts:monitor {--fix : Attempt to fix issues automatically}';
    protected $description = 'Monitor account integrity and alert on issues';

    public function handle(): int
    {
        $this->info('Running account integrity check...');
        
        $issues = [];
        $autoFixed = [];

        // Check for users without emails
        $usersWithoutEmail = User::whereNull('email')->orWhere('email', '')->get();
        if ($usersWithoutEmail->count() > 0) {
            $issues[] = [
                'type' => 'missing_email',
                'count' => $usersWithoutEmail->count(),
                'message' => "Users without email: {$usersWithoutEmail->count()}",
                'users' => $usersWithoutEmail->pluck('id', 'name')->toArray(),
            ];
        }

        // Check for users without profiles
        $usersWithoutProfile = User::doesntHave('profile')->get();
        if ($usersWithoutProfile->count() > 0) {
            $issues[] = [
                'type' => 'missing_profile',
                'count' => $usersWithoutProfile->count(),
                'message' => "Users without profile: {$usersWithoutProfile->count()}",
                'users' => $usersWithoutProfile->pluck('id', 'name')->toArray(),
            ];
            
            if ($this->option('fix')) {
                foreach ($usersWithoutProfile as $user) {
                    $this->createMissingProfile($user);
                    $autoFixed[] = "Created profile for user {$user->id} ({$user->name})";
                }
            }
        }

        // Check for duplicate transactions
        $duplicateTransactions = DB::select("
            SELECT user_id, reference_number, transaction_type, COUNT(*) as count
            FROM transactions 
            WHERE reference_number IS NOT NULL
            GROUP BY user_id, reference_number, transaction_type
            HAVING COUNT(*) > 1
        ");

        if (!empty($duplicateTransactions)) {
            $issues[] = [
                'type' => 'duplicate_transactions',
                'count' => count($duplicateTransactions),
                'message' => "Duplicate transactions found: " . count($duplicateTransactions),
                'details' => array_map(fn($d) => (array) $d, $duplicateTransactions),
            ];
        }

        // Check for inconsistent phone formats (SQLite compatible)
        $invalidPhones = User::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->get()
            ->filter(function ($user) {
                return !preg_match('/^\+260[0-9]{9}$/', $user->phone);
            });
            
        if ($invalidPhones->count() > 0) {
            $issues[] = [
                'type' => 'invalid_phone_format',
                'count' => $invalidPhones->count(),
                'message' => "Invalid phone formats: {$invalidPhones->count()}",
                'users' => $invalidPhones->pluck('phone', 'id')->toArray(),
            ];
            
            if ($this->option('fix')) {
                foreach ($invalidPhones as $user) {
                    $normalized = $this->normalizePhone($user->phone);
                    if ($normalized) {
                        $user->update(['phone' => $normalized]);
                        $autoFixed[] = "Normalized phone for user {$user->id}: {$user->phone} -> {$normalized}";
                    }
                }
            }
        }

        // Check for negative wallet balances
        $negativeBalances = $this->findNegativeBalances();
        if (!empty($negativeBalances)) {
            $issues[] = [
                'type' => 'negative_balance',
                'count' => count($negativeBalances),
                'message' => "Users with negative wallet balance: " . count($negativeBalances),
                'users' => $negativeBalances,
            ];
        }

        // Check for users without points records
        $usersWithoutPoints = User::doesntHave('points')->get();
        if ($usersWithoutPoints->count() > 0) {
            $issues[] = [
                'type' => 'missing_points',
                'count' => $usersWithoutPoints->count(),
                'message' => "Users without points record: {$usersWithoutPoints->count()}",
                'users' => $usersWithoutPoints->pluck('id', 'name')->toArray(),
            ];
            
            if ($this->option('fix')) {
                foreach ($usersWithoutPoints as $user) {
                    $this->createMissingPoints($user);
                    $autoFixed[] = "Created points record for user {$user->id} ({$user->name})";
                }
            }
        }

        // Output results
        if (!empty($autoFixed)) {
            $this->info("\nAuto-fixed issues:");
            foreach ($autoFixed as $fix) {
                $this->line("  ✓ {$fix}");
            }
        }

        if (!empty($issues)) {
            $this->logIntegrityIssues($issues);
            $this->error("\nAccount integrity issues found:");
            
            foreach ($issues as $issue) {
                $this->line("  ✗ {$issue['message']}");
            }
            
            $this->newLine();
            $this->warn('Run with --fix to attempt automatic fixes where possible.');
            
            return 1;
        }

        $this->info('✓ Account integrity check passed - no issues found');
        return 0;
    }

    /**
     * Find users with negative wallet balances
     */
    private function findNegativeBalances(): array
    {
        $results = DB::select("
            SELECT u.id, u.name, u.email, SUM(t.amount) as balance
            FROM users u
            LEFT JOIN transactions t ON u.id = t.user_id AND t.status = 'completed'
            GROUP BY u.id, u.name, u.email
            HAVING SUM(t.amount) < 0
        ");

        return array_map(fn($r) => [
            'user_id' => $r->id,
            'name' => $r->name,
            'email' => $r->email,
            'balance' => $r->balance,
        ], $results);
    }

    /**
     * Create missing profile for user
     */
    private function createMissingProfile(User $user): void
    {
        try {
            $user->profile()->create([
                'bio' => null,
                'avatar' => null,
            ]);
            
            Log::info('Created missing profile', ['user_id' => $user->id]);
        } catch (\Exception $e) {
            Log::error('Failed to create profile', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create missing points record for user
     */
    private function createMissingPoints(User $user): void
    {
        try {
            $user->points()->create([
                'lifetime_points' => 0,
                'monthly_activity_points' => 0,
                'last_activity_at' => now(),
            ]);
            
            Log::info('Created missing points record', ['user_id' => $user->id]);
        } catch (\Exception $e) {
            Log::error('Failed to create points record', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Normalize phone number to standard format
     */
    private function normalizePhone(string $phone): ?string
    {
        $cleaned = preg_replace('/[^\d\+]/', '', $phone);
        
        if (preg_match('/^260\d{9}$/', $cleaned)) {
            return '+' . $cleaned;
        }
        
        if (preg_match('/^0\d{9}$/', $cleaned)) {
            return '+260' . substr($cleaned, 1);
        }
        
        if (preg_match('/^\+260\d{9}$/', $cleaned)) {
            return $cleaned;
        }
        
        return null;
    }

    /**
     * Log integrity issues for audit trail
     */
    private function logIntegrityIssues(array $issues): void
    {
        Log::warning('Account integrity issues detected', [
            'timestamp' => now()->toIso8601String(),
            'issue_count' => count($issues),
            'issues' => $issues,
        ]);
    }
}
