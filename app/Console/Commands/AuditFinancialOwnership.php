<?php

namespace App\Console\Commands;

use App\Domain\Core\Services\DataOwnershipRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuditFinancialOwnership extends Command
{
    protected $signature = 'platform:audit-financial-ownership {--fix : Add missing tables to registry}';
    protected $description = 'Audit financial tables against DataOwnershipRegistry';

    private const FINANCIAL_TABLES = [
        'billing_subscriptions' => 'platform-billing',
        'billing_invoices' => 'platform-billing',
        'subscription_plans' => 'platform-billing',
        'payment_transactions' => 'platform-payments',
        'payment_attempts' => 'platform-payments',
        'payment_settlements' => 'platform-payments',
        'currencies' => 'financial-services-core',
        'exchange_rates' => 'financial-services-core',
        'payment_logs' => 'platform-payments',
        'growfinance_accounts' => 'growfinance',
        'growfinance_invoices' => 'growfinance',
        'growfinance_journal_entries' => 'growfinance',
        'growfinance_budgets' => 'growfinance',
        'growfinance_budget_items' => 'growfinance',
        'loans_receivable' => 'growfinance',
        'transactions' => 'transaction',
        'withdrawal_requests' => 'transaction',
    ];

    public function __construct(
        private readonly DataOwnershipRegistry $registry,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $errors = 0;
        $fixes = [];

        $this->line('=== Financial Table Ownership Audit ===');
        $this->newLine();

        foreach (self::FINANCIAL_TABLES as $table => $expectedOwner) {
            $exists = Schema::hasTable($table);

            if (!$exists) {
                $this->warn("  ⚠  {$table} — table does not exist in database");
                continue;
            }

            $registeredOwner = $this->registry->owner($table);

            if ($registeredOwner === null) {
                $this->error("  ✗  {$table} — NOT REGISTERED (expected: {$expectedOwner})");
                $fixes[] = ['table' => $table, 'module' => $expectedOwner];
                $errors++;
            } elseif ($registeredOwner !== $expectedOwner) {
                $this->error("  ✗  {$table} — owned by {$registeredOwner}, expected {$expectedOwner}");
                $fixes[] = ['table' => $table, 'module' => $expectedOwner];
                $errors++;
            } else {
                $this->info("  ✓  {$table} — {$registeredOwner}");
            }

            $this->checkTenantColumn($table);
        }

        $tenantOwned = $this->registry->tablesOwnedBy('platform-billing');
        $tenantOwned += $this->registry->tablesOwnedBy('platform-payments');
        $tenantOwned += $this->registry->tablesOwnedBy('financial-services-core');

        $this->newLine();
        $this->line("Total financial tables: " . count(self::FINANCIAL_TABLES));
        $this->line("Errors found: {$errors}");

        if ($errors > 0 && $this->option('fix')) {
            $this->newLine();
            $this->line('Applying fixes...');
            foreach ($fixes as $fix) {
                $this->registry->register($fix['table'], $fix['module']);
                $this->info("  Registered {$fix['table']} → {$fix['module']}");
            }
        }

        return $errors === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function checkTenantColumn(string $table): void
    {
        $tenantColumn = $this->registry->tenantColumn($table);

        if ($tenantColumn === null) {
            return;
        }

        if (!Schema::hasColumn($table, $tenantColumn)) {
            $this->warn("     └─ Missing tenant column: {$tenantColumn}");
        }
    }
}
