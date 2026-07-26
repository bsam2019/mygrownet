<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditTenantScoping extends Command
{
    protected $signature = 'platform:audit-tenant-scoping
        {--table= : Specific table to audit}
        {--fix : Output fix suggestions}';

    protected $description = 'Audit all queries for missing organization_id scoping';

    public function handle(): int
    {
        $this->info('Tenant Scoping Audit');
        $this->newLine();

        $missing = 0;
        $tables = $this->getTenantScopedTables();

        foreach ($tables as $table => $tenantColumn) {
            if ($this->option('table') && $this->option('table') !== $table) {
                continue;
            }

            try {
                $total = DB::table($table)->count();
                $scoped = DB::table($table)->whereNotNull($tenantColumn)->count();
                $unscoped = $total - $scoped;

                if ($unscoped > 0) {
                    $this->warn("  {$table}: {$unscoped}/{$total} rows missing {$tenantColumn}");

                    if ($this->option('fix')) {
                        $this->line("    SUGGESTION: UPDATE {$table} SET {$tenantColumn} = ? WHERE {$tenantColumn} IS NULL");
                    }

                    $missing += $unscoped;
                } else {
                    $this->line("  ✓ {$table}: all {$total} rows scoped");
                }
            } catch (\Exception $e) {
                $this->line("  ? {$table}: could not query ({$e->getMessage()})");
            }
        }

        $this->newLine();

        if ($missing > 0) {
            $this->warn("Found {$missing} unscoped rows across " . count($tables) . " tables.");
            $this->warn('Run with --fix to see suggestions.');
            return 1;
        }

        $this->info('All tables properly scoped.');
        return 0;
    }

    private function getTenantScopedTables(): array
    {
        return [
            'companies' => 'organization_id',
            'invoices' => 'organization_id',
            'employees' => 'organization_id',
            'grow_net_users' => 'organization_id',
            'grow_net_commissions' => 'organization_id',
            'growfinance_accounts' => 'organization_id',
            'growfinance_invoices' => 'organization_id',
            'products' => 'organization_id',
            'orders' => 'organization_id',
            'grow_builder_sites' => 'organization_id',
            'support_tickets' => 'organization_id',
            'application_installations' => 'organization_id',
            'feature_flags' => 'organization_id',
            'sa_companies' => 'sa_company_id',
            'sa_items' => 'sa_company_id',
            'sa_sales' => 'sa_company_id',
            'sa_purchase_orders' => 'sa_company_id',
        ];
    }
}
