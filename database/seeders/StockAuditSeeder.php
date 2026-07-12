<?php

namespace Database\Seeders;

use App\Models\StockAudit\Company;
use App\Models\StockAudit\CompanySubscription;
use App\Models\StockAudit\SubscriptionPlan;
use App\Models\StockAudit\Department;
use App\Models\StockAudit\Bin;
use App\Models\StockAudit\Item;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StockAuditSeeder extends Seeder
{
    public function run(): void
    {
        // Create or retrieve subscription plans
        $starter = SubscriptionPlan::firstOrCreate(
            ['slug' => 'starter'],
            [
                'name' => 'Starter',
                'description' => 'For small clinics and practices. Up to 500 items per audit.',
                'price_monthly' => 299.00,
                'price_yearly' => 2990.00,
                'max_companies' => 1,
                'max_items_per_audit' => 500,
                'features' => json_encode(['unlimited_audits', 'physical_count_tracking', 'expiry_management', 'basic_reports', 'csv_export']),
                'is_active' => true,
            ]
        );

        SubscriptionPlan::firstOrCreate(
            ['slug' => 'professional'],
            [
                'name' => 'Professional',
                'description' => 'For growing businesses. Up to 5,000 items, multi-branch support.',
                'price_monthly' => 799.00,
                'price_yearly' => 7990.00,
                'max_companies' => 3,
                'max_items_per_audit' => 5000,
                'features' => json_encode(['unlimited_audits', 'physical_count_tracking', 'expiry_management', 'advanced_reports', 'csv_export', 'multi_branch', 'team_collaboration', 'pdf_report_generation']),
                'is_active' => true,
            ]
        );

        SubscriptionPlan::firstOrCreate(
            ['slug' => 'enterprise'],
            [
                'name' => 'Enterprise',
                'description' => 'Unlimited items, branches, and priority support.',
                'price_monthly' => 1999.00,
                'price_yearly' => 19990.00,
                'max_companies' => null,
                'max_items_per_audit' => null,
                'features' => json_encode(['unlimited_audits', 'physical_count_tracking', 'expiry_management', 'advanced_reports', 'csv_export', 'multi_branch', 'team_collaboration', 'pdf_report_generation', 'api_access', 'priority_support', 'custom_integrations']),
                'is_active' => true,
            ]
        );

        // Create or retrieve Taradasi Dental Clinic
        $taradasi = Company::firstOrCreate(
            ['subdomain' => 'taradasi'],
            [
                'name' => 'Taradasi Dental Clinic',
                'address' => 'Shop 11B, Manda Hill Shopping Centre',
                'city' => 'Lusaka',
                'country' => 'Zambia',
                'phone' => null,
                'email' => null,
                'contact_person' => 'The Owner',
                'currency' => 'ZMW',
                'status' => 'active',
                'settings' => json_encode(['audit_frequency' => 'quarterly', 'default_report_title' => 'Independent Stock Audit Report']),
            ]
        );

        // Subscribe to Starter plan (skip if already exists)
        CompanySubscription::firstOrCreate(
            ['sa_company_id' => $taradasi->id],
            [
                'sa_subscription_plan_id' => $starter->id,
                'status' => 'trial',
                'trial_ends_at' => Carbon::now()->addDays(30),
                'starts_at' => Carbon::now(),
            ]
        );

        // Create or retrieve Departments
        $clinic = Department::firstOrCreate(
            ['sa_company_id' => $taradasi->id, 'slug' => 'clinic'],
            [
                'name' => 'Clinic',
                'description' => 'Clinic section — Bins 1 to 18',
                'sort_order' => 1,
            ]
        );

        $laboratory = Department::firstOrCreate(
            ['sa_company_id' => $taradasi->id, 'slug' => 'laboratory'],
            [
                'name' => 'Laboratory',
                'description' => 'Laboratory section — Bins 19 to 28',
                'sort_order' => 2,
            ]
        );

        // Create or retrieve Bins for Clinic (1-18)
        $clinicBins = [
            ['name' => 'Bin 1', 'label' => 'Preventives'],
            ['name' => 'Bin 2', 'label' => 'Dental Instruments and Accessories'],
            ['name' => 'Bin 3', 'label' => 'Disposable Instruments and Accessories'],
            ['name' => 'Bin 4', 'label' => 'Burs'],
            ['name' => 'Bin 5', 'label' => 'Ultrasonic Scaler Tips'],
            ['name' => 'Bin 6', 'label' => 'Handpieces'],
            ['name' => 'Bin 7', 'label' => 'Etchant and Bond'],
            ['name' => 'Bin 8', 'label' => 'Composites and Accessories'],
            ['name' => 'Bin 9', 'label' => 'Cements and Liners'],
            ['name' => 'Bin 10', 'label' => 'Temporary Materials, Finishing and Polishing'],
            ['name' => 'Bin 11', 'label' => 'Clinic Accessories'],
            ['name' => 'Bin 12', 'label' => 'Surgical Materials and Accessories'],
            ['name' => 'Bin 13', 'label' => 'Endodontics Materials'],
            ['name' => 'Bin 14', 'label' => 'Endodontics Files and Accessories'],
            ['name' => 'Bin 15', 'label' => 'Prosthodontics Instruments and Accessories'],
            ['name' => 'Bin 16', 'label' => 'Orthodontics'],
            ['name' => 'Bin 17', 'label' => 'Anesthetics and Radiology'],
            ['name' => 'Bin 18', 'label' => 'Impression Materials'],
        ];

        foreach ($clinicBins as $i => $binData) {
            Bin::firstOrCreate(
                ['sa_company_id' => $taradasi->id, 'name' => $binData['name']],
                [
                    'sa_department_id' => $clinic->id,
                    'label' => $binData['label'],
                    'sort_order' => $i + 1,
                ]
            );
        }

        // Create or retrieve Bins for Laboratory (19-28)
        $labBins = [
            ['name' => 'Bin 19', 'label' => 'Laboratory Instruments'],
            ['name' => 'Bin 20', 'label' => 'Laboratory Rotary Instruments'],
            ['name' => 'Bin 21', 'label' => 'Laboratory Materials'],
            ['name' => 'Bin 22', 'label' => 'Porcelain and Ceramic Supplies'],
            ['name' => 'Bin 23', 'label' => 'Acrylic Teeth'],
            ['name' => 'Bin 24', 'label' => 'Dental Wires'],
            ['name' => 'Bin 25', 'label' => 'Infection Control and Disposables'],
            ['name' => 'Bin 26', 'label' => 'Hospital Holloware'],
            ['name' => 'Bin 27', 'label' => 'Small Equipment'],
            ['name' => 'Bin 28', 'label' => 'Teaching Models'],
        ];

        foreach ($labBins as $i => $binData) {
            Bin::firstOrCreate(
                ['sa_company_id' => $taradasi->id, 'name' => $binData['name']],
                [
                    'sa_department_id' => $laboratory->id,
                    'label' => $binData['label'],
                    'sort_order' => $i + 19,
                ]
            );
        }

        // Build bin name → model map
        $binMap = [];
        for ($i = 1; $i <= 28; $i++) {
            $name = "Bin {$i}";
            $binMap[$name] = Bin::where('sa_company_id', $taradasi->id)->where('name', $name)->first();
        }

        // Load and import all items from the data file (skip if already exist)
        $items = require __DIR__ . '/data/taradasi-items.php';
        $imported = 0;
        $skipped = 0;

        foreach ($items as $itemData) {
            $bin = $binMap[$itemData['bin']] ?? null;
            if (!$bin) {
                $this->command->warn("Bin '{$itemData['bin']}' not found, skipping: {$itemData['name']}");
                continue;
            }

            $existing = Item::where('sa_company_id', $taradasi->id)
                ->where('sa_bin_id', $bin->id)
                ->where('name', $itemData['name'])
                ->first();

            if ($existing) {
                $skipped++;
                continue;
            }

            Item::create([
                'sa_company_id' => $taradasi->id,
                'sa_department_id' => $bin->sa_department_id,
                'sa_bin_id' => $bin->id,
                'name' => $itemData['name'],
                'unit_price' => $itemData['price'],
                'unit' => $itemData['unit'] ?? 'pcs',
                'system_quantity' => $itemData['qty'],
                'category' => $bin->label,
                'is_expirable' => !empty($itemData['expiry']),
                'expiry_date' => $itemData['expiry'] ?? null,
            ]);
            $imported++;
        }

        $this->command->info("Items: {$imported} imported, {$skipped} already exist.");
    }
}
