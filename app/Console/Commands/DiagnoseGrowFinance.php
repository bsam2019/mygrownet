<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\CMS\GrowFinanceSyncConfigModel;
use App\Domain\CMS\Services\GrowFinanceSync\GrowFinanceReportService;

class DiagnoseGrowFinance extends Command
{
    protected $signature = 'growfinance:diagnose {company_id?}';
    protected $description = 'Diagnose GrowFinance integration status for a company';

    public function handle(GrowFinanceReportService $reportService)
    {
        $companyId = $this->argument('company_id');

        if (!$companyId) {
            // Show all companies
            $this->info('All CMS Companies:');
            $companies = CompanyModel::all();
            
            if ($companies->isEmpty()) {
                $this->warn('No CMS companies found. Create a company first.');
                return 0;
            }

            foreach ($companies as $company) {
                $isEnabled = $reportService->isEnabled($company->id);
                $status = $isEnabled ? '<fg=green>✓ ENABLED</>' : '<fg=red>✗ DISABLED</>';
                $this->line("  [{$company->id}] {$company->name} - {$status}");
            }

            $this->newLine();
            $this->info('Run: php artisan growfinance:diagnose {company_id} for details');
            return 0;
        }

        // Show specific company details
        $company = CompanyModel::find($companyId);
        
        if (!$company) {
            $this->error("Company with ID {$companyId} not found.");
            return 1;
        }

        $this->info("GrowFinance Integration Status for: {$company->name}");
        $this->newLine();

        // Check config
        $config = GrowFinanceSyncConfigModel::where('company_id', $companyId)->first();
        
        if (!$config) {
            $this->error('✗ GrowFinance sync config NOT found');
            $this->warn('  Module has not been enabled yet.');
            $this->newLine();
            $this->info('To enable:');
            $this->line('  1. Go to CMS → Settings → Modules');
            $this->line('  2. Enable "GrowFinance (Full Accounting)"');
            return 0;
        }

        $this->info('✓ GrowFinance sync config found');
        $this->line("  - Enabled: " . ($config->is_enabled ? '<fg=green>YES</>' : '<fg=red>NO</>'));
        $this->line("  - Auto Sync: " . ($config->auto_sync ? 'YES' : 'NO'));
        $this->line("  - Sync Invoices: " . ($config->sync_invoices ? 'YES' : 'NO'));
        $this->line("  - Sync Expenses: " . ($config->sync_expenses ? 'YES' : 'NO'));
        $this->line("  - Sync Payments: " . ($config->sync_payments ? 'YES' : 'NO'));
        $this->line("  - GrowFinance Business ID: {$config->growfinance_business_id}");

        $this->newLine();

        // Check if reports will show
        $isEnabled = $reportService->isEnabled($companyId);
        
        if ($isEnabled) {
            $this->info('✓ GrowFinance Reports WILL BE VISIBLE in CMS Reports page');
            $this->newLine();
            $this->info('Available Reports:');
            $this->line('  - Balance Sheet: ' . route('cms.reports.balance-sheet'));
            $this->line('  - Cash Flow Statement: ' . route('cms.reports.cash-flow-statement'));
            $this->line('  - General Ledger: ' . route('cms.reports.general-ledger'));
            $this->line('  - Trial Balance: ' . route('cms.reports.trial-balance'));
        } else {
            $this->error('✗ GrowFinance Reports WILL NOT BE VISIBLE');
            $this->warn('  The module is disabled in the config.');
        }

        return 0;
    }
}
