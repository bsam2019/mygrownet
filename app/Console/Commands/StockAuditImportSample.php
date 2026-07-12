<?php

namespace App\Console\Commands;

use App\Models\StockAudit\Bin;
use App\Models\StockAudit\Company;
use App\Models\StockAudit\Item;
use Illuminate\Console\Command;

class StockAuditImportSample extends Command
{
    protected $signature = 'stock-audit:import-sample
        {company? : Company ID or name (defaults to first active company)}
    ';

    protected $description = 'Import all Taradasi Dental Clinic inventory items into the Stock Audit module';

    public function handle(): int
    {
        $company = $this->argument('company')
            ? (is_numeric($this->argument('company'))
                ? Company::find($this->argument('company'))
                : Company::where('name', $this->argument('company'))->first())
            : Company::where('status', 'active')->first();

        if (!$company) {
            $this->error('No active company found. Run the StockAuditSeeder first.');
            return Command::FAILURE;
        }

        $existingCount = Item::where('sa_company_id', $company->id)->count();
        if ($existingCount > 50) {
            if (!$this->confirm("Company '{$company->name}' already has {$existingCount} items. Add more?")) {
                return Command::SUCCESS;
            }
        }

        $items = require database_path('seeders/data/taradasi-items.php');
        $imported = 0;

        foreach ($items as $data) {
            $b = Bin::where('sa_company_id', $company->id)->where('name', $data['bin'])->first();
            if (!$b) {
                $this->warn("Bin '{$data['bin']}' not found, skipping: {$data['name']}");
                continue;
            }

            Item::create([
                'sa_company_id' => $company->id,
                'sa_department_id' => $b->sa_department_id,
                'sa_bin_id' => $b->id,
                'name' => $data['name'],
                'unit_price' => $data['price'],
                'unit' => $data['unit'] ?? 'pcs',
                'system_quantity' => $data['qty'],
                'category' => $b->label,
                'is_expirable' => !empty($data['expiry']),
                'expiry_date' => $data['expiry'] ?? null,
            ]);
            $imported++;
        }

        $this->info("Imported {$imported} items for {$company->name}.");

        return Command::SUCCESS;
    }
}
