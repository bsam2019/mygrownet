<?php

namespace Database\Seeders;

use App\Domain\CMS\Core\Services\ChartOfAccountsService;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $chartService = app(ChartOfAccountsService::class);

        // Initialize chart of accounts for all existing companies
        $companies = CompanyModel::all();

        foreach ($companies as $company) {
            $this->command->info("Initializing chart of accounts for {$company->name}...");
            $chartService->initializeChartOfAccounts($company->id);
        }

        $this->command->info('✅ Chart of accounts initialized for ' . $companies->count() . ' companies');
    }
}
