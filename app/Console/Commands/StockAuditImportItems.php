<?php

namespace App\Console\Commands;

use App\Models\StockAudit\Bin;
use App\Models\StockAudit\Company;
use App\Models\StockAudit\Department;
use App\Models\StockAudit\Item;
use Illuminate\Console\Command;

class StockAuditImportItems extends Command
{
    protected $signature = 'stockflow:import-items
        {company : The ID or name of the company}
        {file : Path to the CSV file}
        {--delimiter=, : CSV delimiter}
    ';

    protected $description = 'Import inventory items from a CSV file into StockFlow';

    public function handle(): int
    {
        $companyInput = $this->argument('company');
        $filePath = $this->argument('file');
        $delimiter = $this->option('delimiter');

        // Resolve company
        $company = is_numeric($companyInput)
            ? Company::find($companyInput)
            : Company::where('name', $companyInput)->first();

        if (!$company) {
            $this->error("Company not found: {$companyInput}");
            return Command::FAILURE;
        }

        if (!file_exists($filePath) || !is_readable($filePath)) {
            $this->error("File not found or not readable: {$filePath}");
            return Command::FAILURE;
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $this->error("Could not open file: {$filePath}");
            return Command::FAILURE;
        }

        // Read header row
        $headers = fgetcsv($handle, 0, $delimiter);
        if (!$headers) {
            fclose($handle);
            $this->error('Empty or invalid CSV file');
            return Command::FAILURE;
        }

        $headers = array_map('trim', $headers);
        $expected = ['name', 'price', 'system_qty', 'bin_name'];
        $missing = array_diff($expected, array_map('strtolower', $headers));

        $headerMap = [];
        foreach ($headers as $i => $h) {
            $headerMap[strtolower($h)] = $i;
        }

        $imported = 0;
        $skipped = 0;
        $binCache = [];
        $deptCache = [];

        $bar = $this->output->createProgressBar();

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $bar->advance();

            $data = [];
            foreach ($headerMap as $key => $index) {
                $data[$key] = $row[$index] ?? '';
            }

            $name = trim($data['name'] ?? '');
            if (empty($name)) {
                $skipped++;
                continue;
            }

            // Resolve bin
            $binName = trim($data['bin_name'] ?? '');
            $binId = null;
            $deptId = null;

            if (!empty($binName)) {
                if (!isset($binCache[$binName])) {
                    $bin = Bin::where('sa_company_id', $company->id)
                        ->where('name', $binName)
                        ->first();

                    if ($bin) {
                        $binCache[$binName] = $bin->id;
                        $deptCache[$binName] = $bin->sa_department_id;
                    } else {
                        // Try matching by label
                        $bin = Bin::where('sa_company_id', $company->id)
                            ->where('label', 'like', "%{$binName}%")
                            ->first();
                        if ($bin) {
                            $binCache[$binName] = $bin->id;
                            $deptCache[$binName] = $bin->sa_department_id;
                        } else {
                            $binCache[$binName] = null;
                            $deptCache[$binName] = null;
                        }
                    }
                }
                $binId = $binCache[$binName];
                $deptId = $deptCache[$binName];
            }

            $unitPrice = (float) str_replace(['K', ',', ' '], '', $data['price'] ?? 0);
            $systemQty = (float) str_replace(',', '', $data['system_qty'] ?? 0);
            $expiryDate = !empty($data['expiry']) ? $this->parseExpiry($data['expiry']) : null;
            $remarks = trim($data['remarks'] ?? '');

            $itemData = [
                'sa_company_id' => $company->id,
                'sa_department_id' => $deptId,
                'sa_bin_id' => $binId,
                'name' => $name,
                'sku' => trim($data['sku'] ?? trim($data['s_no'] ?? '')),
                'unit_price' => $unitPrice,
                'unit' => trim($data['unit'] ?? 'pcs'),
                'system_quantity' => $systemQty,
                'category' => trim($data['category'] ?? ''),
                'is_expirable' => !empty($expiryDate) || stripos($remarks, 'expired') !== false,
                'expiry_date' => $expiryDate,
                'notes' => $remarks,
            ];

            Item::create($itemData);
            $imported++;
        }

        $bar->finish();
        fclose($handle);

        $this->newLine();
        $this->info("Imported {$imported} items for {$company->name}, {$skipped} skipped.");

        return Command::SUCCESS;
    }

    private function parseExpiry(string $value): ?string
    {
        $value = trim($value);
        if (empty($value) || $value === '-' || $value === 'N/A') {
            return null;
        }

        // Try DD/MM/YYYY or DD.MM.YYYY
        $formats = ['d/m/Y', 'd.m.Y', 'Y-m-d', 'm/Y', 'm.y'];
        foreach ($formats as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $value);
            if ($dt && $dt->format($fmt) === $value) {
                return $dt->format('Y-m-d');
            }
        }

        // Try Excel serial date (integer)
        if (is_numeric($value)) {
            $unix = ($value - 25569) * 86400;
            return date('Y-m-d', (int) $unix);
        }

        return null;
    }
}
