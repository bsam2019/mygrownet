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
        // Create subscription plans
        $starter = SubscriptionPlan::create([
            'name' => 'Starter',
            'slug' => 'starter',
            'description' => 'For small clinics and practices. Up to 500 items per audit.',
            'price_monthly' => 299.00,
            'price_yearly' => 2990.00,
            'max_companies' => 1,
            'max_items_per_audit' => 500,
            'features' => json_encode([
                'unlimited_audits',
                'physical_count_tracking',
                'expiry_management',
                'basic_reports',
                'csv_export',
            ]),
            'is_active' => true,
        ]);

        SubscriptionPlan::create([
            'name' => 'Professional',
            'slug' => 'professional',
            'description' => 'For growing businesses. Up to 5,000 items, multi-branch support.',
            'price_monthly' => 799.00,
            'price_yearly' => 7990.00,
            'max_companies' => 3,
            'max_items_per_audit' => 5000,
            'features' => json_encode([
                'unlimited_audits',
                'physical_count_tracking',
                'expiry_management',
                'advanced_reports',
                'csv_export',
                'multi_branch',
                'team_collaboration',
                'pdf_report_generation',
            ]),
            'is_active' => true,
        ]);

        SubscriptionPlan::create([
            'name' => 'Enterprise',
            'slug' => 'enterprise',
            'description' => 'Unlimited items, branches, and priority support.',
            'price_monthly' => 1999.00,
            'price_yearly' => 19990.00,
            'max_companies' => null,
            'max_items_per_audit' => null,
            'features' => json_encode([
                'unlimited_audits',
                'physical_count_tracking',
                'expiry_management',
                'advanced_reports',
                'csv_export',
                'multi_branch',
                'team_collaboration',
                'pdf_report_generation',
                'api_access',
                'priority_support',
                'custom_integrations',
            ]),
            'is_active' => true,
        ]);

        // Create Taradasi Dental Clinic
        $taradasi = Company::create([
            'name' => 'Taradasi Dental Clinic',
            'subdomain' => 'taradasi',
            'address' => 'Shop 11B, Manda Hill Shopping Centre',
            'city' => 'Lusaka',
            'country' => 'Zambia',
            'phone' => null,
            'email' => null,
            'contact_person' => 'The Owner',
            'currency' => 'ZMW',
            'status' => 'active',
            'settings' => json_encode([
                'audit_frequency' => 'quarterly',
                'default_report_title' => 'Independent Stock Audit Report',
            ]),
        ]);

        // Subscribe to Starter plan (complimentary/trial)
        CompanySubscription::create([
            'sa_company_id' => $taradasi->id,
            'sa_subscription_plan_id' => $starter->id,
            'status' => 'trial',
            'trial_ends_at' => Carbon::now()->addDays(30),
            'starts_at' => Carbon::now(),
        ]);

        // Create Departments
        $clinic = Department::create([
            'sa_company_id' => $taradasi->id,
            'name' => 'Clinic',
            'slug' => 'clinic',
            'description' => 'Clinic section — Bins 1 to 18',
            'sort_order' => 1,
        ]);

        $laboratory = Department::create([
            'sa_company_id' => $taradasi->id,
            'name' => 'Laboratory',
            'slug' => 'laboratory',
            'description' => 'Laboratory section — Bins 19 to 28',
            'sort_order' => 2,
        ]);

        // Create Bins for Clinic (1-18)
        $clinicBins = [
            ['name' => 'Bin 1', 'label' => 'Mouth Gags, Disposables & Accessories'],
            ['name' => 'Bin 2', 'label' => 'Dental Instruments and Accessories'],
            ['name' => 'Bin 3', 'label' => 'Sutures, Needles & Anaesthetics'],
            ['name' => 'Bin 4', 'label' => 'Cements, Liners & Bases'],
            ['name' => 'Bin 5', 'label' => 'Restorative Materials — Amalgam & Composite'],
            ['name' => 'Bin 6', 'label' => 'Bonding Agents & Adhesives'],
            ['name' => 'Bin 7', 'label' => 'Etchant and Bond'],
            ['name' => 'Bin 8', 'label' => 'Composites and Accessories'],
            ['name' => 'Bin 9', 'label' => 'Endodontic Materials'],
            ['name' => 'Bin 10', 'label' => 'Prosthodontic Materials'],
            ['name' => 'Bin 11', 'label' => 'Orthodontic Supplies'],
            ['name' => 'Bin 12', 'label' => 'Periodontic Supplies'],
            ['name' => 'Bin 13', 'label' => 'Oral Surgery Supplies'],
            ['name' => 'Bin 14', 'label' => 'Radiology & Diagnostic Supplies'],
            ['name' => 'Bin 15', 'label' => 'Personal Protective Equipment'],
            ['name' => 'Bin 16', 'label' => 'Sterilisation & Infection Control'],
            ['name' => 'Bin 17', 'label' => 'Consumables — Paper, Stationery & General'],
            ['name' => 'Bin 18', 'label' => 'Miscellaneous Clinical Items'],
        ];

        foreach ($clinicBins as $i => $binData) {
            Bin::create([
                'sa_company_id' => $taradasi->id,
                'sa_department_id' => $clinic->id,
                'name' => $binData['name'],
                'label' => $binData['label'],
                'sort_order' => $i + 1,
            ]);
        }

        // Create Bins for Laboratory (19-28)
        $labBins = [
            ['name' => 'Bin 19', 'label' => 'Dental Stone, Plaster & Investment Materials'],
            ['name' => 'Bin 20', 'label' => 'Wax, Acrylic & Modelling Materials'],
            ['name' => 'Bin 21', 'label' => 'Laboratory Materials'],
            ['name' => 'Bin 22', 'label' => 'Porcelain & Ceramic Supplies'],
            ['name' => 'Bin 23', 'label' => 'Acrylic Teeth'],
            ['name' => 'Bin 24', 'label' => 'Metal Alloys & Casting Supplies'],
            ['name' => 'Bin 25', 'label' => 'Infection Control, Disposables & Accessories'],
            ['name' => 'Bin 26', 'label' => 'Lab Instruments & Hand Tools'],
            ['name' => 'Bin 27', 'label' => 'Small Equipment'],
            ['name' => 'Bin 28', 'label' => 'Miscellaneous Laboratory Items'],
        ];

        foreach ($labBins as $i => $binData) {
            Bin::create([
                'sa_company_id' => $taradasi->id,
                'sa_department_id' => $laboratory->id,
                'name' => $binData['name'],
                'label' => $binData['label'],
                'sort_order' => $i + 19,
            ]);
        }

        // Build bin name → model map
        $binMap = [];
        for ($i = 1; $i <= 28; $i++) {
            $name = "Bin {$i}";
            $binMap[$name] = Bin::where('sa_company_id', $taradasi->id)->where('name', $name)->first();
        }

        // Load all items from the data file
        $items = require __DIR__ . '/data/taradasi-items.php';

        foreach ($items as $itemData) {
            $bin = $binMap[$itemData['bin']] ?? null;
            if (!$bin) {
                $this->command->warn("Bin '{$itemData['bin']}' not found, skipping: {$itemData['name']}");
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
        }
    }
}
