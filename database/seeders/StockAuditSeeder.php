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

        // Get bin references for seeding items
        $bin2 = Bin::where('sa_company_id', $taradasi->id)->where('name', 'Bin 2')->first();
        $bin7 = Bin::where('sa_company_id', $taradasi->id)->where('name', 'Bin 7')->first();
        $bin8 = Bin::where('sa_company_id', $taradasi->id)->where('name', 'Bin 8')->first();
        $bin21 = Bin::where('sa_company_id', $taradasi->id)->where('name', 'Bin 21')->first();
        $bin23 = Bin::where('sa_company_id', $taradasi->id)->where('name', 'Bin 23')->first();
        $bin25 = Bin::where('sa_company_id', $taradasi->id)->where('name', 'Bin 25')->first();
        $bin27 = Bin::where('sa_company_id', $taradasi->id)->where('name', 'Bin 27')->first();

        // Seed items from the audit report (high-value discrepancy items)
        $items = [
            // Bin 8 — Composites
            [
                'sa_bin_id' => $bin8->id, 'name' => 'Nanotech 7 Syringe Kit (Shades A1–UO, Debond)', 'unit_price' => 2420.00, 'system_quantity' => 19, 'unit' => 'kit',
            ],
            [
                'sa_bin_id' => $bin8->id, 'name' => 'Supreme 7 Syringe Kit 4g (Shades A1–Opaque)', 'unit_price' => 1738.33, 'system_quantity' => 16, 'unit' => 'kit',
            ],
            [
                'sa_bin_id' => $bin8->id, 'name' => 'Dentogyl 12g', 'unit_price' => 560.00, 'system_quantity' => 23, 'unit' => 'tube',
            ],
            [
                'sa_bin_id' => $bin8->id, 'name' => 'Denbond TE', 'unit_price' => 450.00, 'system_quantity' => 27, 'unit' => 'piece',
            ],
            // Bin 25 — Infection Control
            [
                'sa_bin_id' => $bin25->id, 'name' => 'Saliva Ejectors, Standard, Clear/Blue tip (100 pcs/pk)', 'unit_price' => 180.00, 'system_quantity' => 170, 'unit' => 'pack',
            ],
            [
                'sa_bin_id' => $bin25->id, 'name' => 'Dental Bibs, 3-Ply Tissue + Poly (125 pcs/pk)', 'unit_price' => 757.58, 'system_quantity' => 33, 'unit' => 'pack',
            ],
            // Bin 2 — Instruments
            [
                'sa_bin_id' => $bin2->id, 'name' => 'Mouth Gag – Children 110mm', 'unit_price' => 150.00, 'system_quantity' => 5, 'unit' => 'piece',
            ],
            [
                'sa_bin_id' => $bin2->id, 'name' => 'Mouth Gag – Adult 140mm', 'unit_price' => 180.00, 'system_quantity' => 4, 'unit' => 'piece',
            ],
            // Bin 7 — Etchant and Bond
            [
                'sa_bin_id' => $bin7->id, 'name' => 'Dental Lidocaine 2% (50 Cartridges/Box)', 'unit_price' => 528.00, 'system_quantity' => 25, 'unit' => 'box',
            ],
            // Bin 23 — Acrylic Teeth
            [
                'sa_bin_id' => $bin23->id, 'name' => 'Acrylic Teeth Set — Full (Anteriors & Posteriors)', 'unit_price' => 450.00, 'system_quantity' => 120, 'unit' => 'set',
            ],
            // Bin 21 — Lab Materials
            [
                'sa_bin_id' => $bin21->id, 'name' => 'Beloform Powder – Investment Material, Powder 160g + Liquid 35ml', 'unit_price' => 180.00, 'system_quantity' => 218, 'unit' => 'set',
            ],
            [
                'sa_bin_id' => $bin21->id, 'name' => 'Cold Mold Seal 500ml', 'unit_price' => 180.00, 'system_quantity' => 44, 'unit' => 'bottle',
            ],
            [
                'sa_bin_id' => $bin21->id, 'name' => 'Cold Cure Denture Base Liquid 100ml', 'unit_price' => 235.00, 'system_quantity' => 20, 'unit' => 'bottle',
            ],
            [
                'sa_bin_id' => $bin21->id, 'name' => 'Cold Cure Denture Base Liquid 500ml', 'unit_price' => 240.00, 'system_quantity' => 30, 'unit' => 'bottle',
            ],
            // Bin 27 — Small Equipment
            [
                'sa_bin_id' => $bin27->id, 'name' => 'Curing Light, Woodpecker LED.B', 'unit_price' => 2500.00, 'system_quantity' => 5, 'unit' => 'piece',
            ],
            // Bin 1 — Misc (using first clinic bin)
            [
                'sa_bin_id' => Bin::where('sa_company_id', $taradasi->id)->where('name', 'Bin 1')->first()->id,
                'name' => 'Holly Hexidine 100mls', 'unit_price' => 85.00, 'system_quantity' => 6, 'unit' => 'bottle',
            ],
        ];

        foreach ($items as $itemData) {
            $bin = Bin::find($itemData['sa_bin_id']);
            Item::create(array_merge($itemData, [
                'sa_company_id' => $taradasi->id,
                'sa_department_id' => $bin->sa_department_id,
                'category' => $bin->label ?? null,
            ]));
        }
    }
}
