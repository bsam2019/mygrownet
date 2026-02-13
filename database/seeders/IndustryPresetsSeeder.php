<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\CMS\IndustryPresetModel;
use Illuminate\Database\Seeder;

class IndustryPresetsSeeder extends Seeder
{
    public function run(): void
    {
        $presets = [
            [
                'code' => 'printing_branding',
                'name' => 'Printing & Branding',
                'description' => 'For printing shops, branding agencies, and signage businesses',
                'icon' => 'printer',
                'sort_order' => 1,
                'roles' => $this->getPrintingRoles(),
                'expense_categories' => $this->getPrintingExpenseCategories(),
                'job_types' => ['Printing', 'Branding', 'Signage', 'T-Shirt Printing', 'Banner Design', 'Business Cards', 'Flyers', 'Posters'],
                'inventory_categories' => ['Ink & Toner', 'Paper & Cardstock', 'Vinyl & Stickers', 'T-Shirts & Apparel', 'Banners & Flags'],
                'asset_types' => ['Printer', 'Cutting Machine', 'Heat Press', 'Computer', 'Design Software'],
                'default_settings' => [
                    'currency' => 'ZMW',
                    'vat_enabled' => true,
                    'vat_rate' => 16,
                    'invoice_due_days' => 30,
                    'payment_methods' => ['cash', 'mobile_money', 'bank_transfer'],
                ],
            ],
            [
                'code' => 'construction',
                'name' => 'Construction & Building',
                'description' => 'For construction companies, contractors, and building services',
                'icon' => 'building',
                'sort_order' => 2,
                'roles' => $this->getConstructionRoles(),
                'expense_categories' => $this->getConstructionExpenseCategories(),
                'job_types' => ['New Construction', 'Renovation', 'Plumbing', 'Electrical', 'Painting', 'Roofing', 'Flooring', 'Landscaping'],
                'inventory_categories' => ['Cement & Concrete', 'Bricks & Blocks', 'Timber & Wood', 'Roofing Materials', 'Plumbing Supplies', 'Electrical Supplies', 'Paint & Finishes'],
                'asset_types' => ['Vehicle', 'Excavator', 'Mixer', 'Scaffolding', 'Power Tools', 'Safety Equipment'],
                'default_settings' => [
                    'currency' => 'ZMW',
                    'vat_enabled' => true,
                    'vat_rate' => 16,
                    'invoice_due_days' => 30,
                    'payment_methods' => ['cash', 'mobile_money', 'bank_transfer', 'cheque'],
                    'approval_thresholds' => [
                        'expense' => 5000,
                        'commission' => 1000,
                    ],
                ],
            ],
            [
                'code' => 'retail',
                'name' => 'Retail & Shop',
                'description' => 'For retail stores, shops, and merchandise businesses',
                'icon' => 'shopping-bag',
                'sort_order' => 3,
                'roles' => $this->getRetailRoles(),
                'expense_categories' => $this->getRetailExpenseCategories(),
                'job_types' => ['Sale', 'Order', 'Delivery', 'Return', 'Exchange'],
                'inventory_categories' => ['Products', 'Merchandise', 'Supplies', 'Packaging'],
                'asset_types' => ['POS System', 'Display Shelves', 'Refrigerator', 'Security System', 'Vehicle'],
                'default_settings' => [
                    'currency' => 'ZMW',
                    'vat_enabled' => true,
                    'vat_rate' => 16,
                    'invoice_due_days' => 7,
                    'payment_methods' => ['cash', 'mobile_money', 'card'],
                ],
            ],
            [
                'code' => 'services',
                'name' => 'Professional Services',
                'description' => 'For consulting, accounting, legal, and other professional services',
                'icon' => 'briefcase',
                'sort_order' => 4,
                'roles' => $this->getServicesRoles(),
                'expense_categories' => $this->getServicesExpenseCategories(),
                'job_types' => ['Consultation', 'Project', 'Retainer', 'Audit', 'Advisory', 'Training'],
                'inventory_categories' => ['Office Supplies', 'Software Licenses', 'Reference Materials'],
                'asset_types' => ['Computer', 'Software', 'Office Furniture', 'Server', 'Network Equipment'],
                'default_settings' => [
                    'currency' => 'ZMW',
                    'vat_enabled' => true,
                    'vat_rate' => 16,
                    'invoice_due_days' => 30,
                    'payment_methods' => ['bank_transfer', 'mobile_money', 'cheque'],
                ],
            ],
            [
                'code' => 'automotive',
                'name' => 'Automotive & Repair',
                'description' => 'For auto repair shops, mechanics, and vehicle services',
                'icon' => 'wrench',
                'sort_order' => 5,
                'roles' => $this->getAutomotiveRoles(),
                'expense_categories' => $this->getAutomotiveExpenseCategories(),
                'job_types' => ['Service', 'Repair', 'Maintenance', 'Inspection', 'Parts Replacement', 'Bodywork', 'Painting'],
                'inventory_categories' => ['Engine Parts', 'Body Parts', 'Oils & Fluids', 'Tires', 'Batteries', 'Filters', 'Tools'],
                'asset_types' => ['Lift', 'Diagnostic Equipment', 'Tools', 'Air Compressor', 'Welding Machine'],
                'default_settings' => [
                    'currency' => 'ZMW',
                    'vat_enabled' => true,
                    'vat_rate' => 16,
                    'invoice_due_days' => 14,
                    'payment_methods' => ['cash', 'mobile_money', 'bank_transfer'],
                ],
            ],
            [
                'code' => 'hospitality',
                'name' => 'Hospitality & Food',
                'description' => 'For restaurants, cafes, catering, and food services',
                'icon' => 'utensils',
                'sort_order' => 6,
                'roles' => $this->getHospitalityRoles(),
                'expense_categories' => $this->getHospitalityExpenseCategories(),
                'job_types' => ['Dine-In', 'Takeaway', 'Delivery', 'Catering', 'Event'],
                'inventory_categories' => ['Food Ingredients', 'Beverages', 'Packaging', 'Cleaning Supplies', 'Kitchen Supplies'],
                'asset_types' => ['Kitchen Equipment', 'Refrigerator', 'Freezer', 'Oven', 'POS System', 'Furniture'],
                'default_settings' => [
                    'currency' => 'ZMW',
                    'vat_enabled' => true,
                    'vat_rate' => 16,
                    'invoice_due_days' => 0,
                    'payment_methods' => ['cash', 'mobile_money', 'card'],
                ],
            ],
            [
                'code' => 'general',
                'name' => 'General Business',
                'description' => 'Generic preset for any type of business',
                'icon' => 'building-office',
                'sort_order' => 99,
                'roles' => $this->getGeneralRoles(),
                'expense_categories' => $this->getGeneralExpenseCategories(),
                'job_types' => ['Service', 'Project', 'Order', 'Task'],
                'inventory_categories' => ['Products', 'Materials', 'Supplies'],
                'asset_types' => ['Equipment', 'Furniture', 'Vehicle', 'Computer'],
                'default_settings' => [
                    'currency' => 'ZMW',
                    'vat_enabled' => true,
                    'vat_rate' => 16,
                    'invoice_due_days' => 30,
                    'payment_methods' => ['cash', 'mobile_money', 'bank_transfer'],
                ],
            ],
        ];

        foreach ($presets as $preset) {
            IndustryPresetModel::updateOrCreate(
                ['code' => $preset['code']],
                $preset
            );
        }

        $this->command->info('✅ Created ' . count($presets) . ' industry presets');
    }

    protected function getPrintingRoles(): array
    {
        return [
            [
                'name' => 'Owner',
                'is_system_role' => true,
                'permissions' => ['*'],
                'approval_authority' => ['limit' => 999999999],
            ],
            [
                'name' => 'Manager',
                'is_system_role' => true,
                'permissions' => ['customers.manage', 'jobs.manage', 'invoices.manage', 'payments.manage', 'inventory.manage', 'reports.view'],
                'approval_authority' => ['limit' => 10000],
            ],
            [
                'name' => 'Designer',
                'is_system_role' => true,
                'permissions' => ['jobs.view', 'jobs.create', 'jobs.update', 'customers.view'],
                'approval_authority' => ['limit' => 0],
            ],
            [
                'name' => 'Printer Operator',
                'is_system_role' => true,
                'permissions' => ['jobs.view', 'inventory.view'],
                'approval_authority' => ['limit' => 0],
            ],
        ];
    }

    protected function getPrintingExpenseCategories(): array
    {
        return [
            ['name' => 'Materials & Supplies', 'description' => 'Ink, paper, vinyl, t-shirts, etc.', 'requires_approval' => false],
            ['name' => 'Equipment & Maintenance', 'description' => 'Printer maintenance, repairs', 'requires_approval' => true, 'approval_limit' => 5000],
            ['name' => 'Transport & Delivery', 'description' => 'Fuel, delivery costs', 'requires_approval' => false],
            ['name' => 'Utilities', 'description' => 'Electricity, water, internet', 'requires_approval' => false],
            ['name' => 'Rent & Premises', 'description' => 'Office rent, security', 'requires_approval' => true, 'approval_limit' => 10000],
            ['name' => 'Marketing', 'description' => 'Advertising, promotions', 'requires_approval' => true, 'approval_limit' => 3000],
            ['name' => 'Office Supplies', 'description' => 'Stationery, misc items', 'requires_approval' => false],
            ['name' => 'Staff Welfare', 'description' => 'Lunch, benefits', 'requires_approval' => false],
        ];
    }

    protected function getConstructionRoles(): array
    {
        return [
            [
                'name' => 'Owner',
                'is_system_role' => true,
                'permissions' => ['*'],
                'approval_authority' => ['limit' => 999999999],
            ],
            [
                'name' => 'Project Manager',
                'is_system_role' => true,
                'permissions' => ['customers.manage', 'jobs.manage', 'invoices.manage', 'payments.manage', 'inventory.manage', 'workers.manage', 'reports.view'],
                'approval_authority' => ['limit' => 20000],
            ],
            [
                'name' => 'Site Supervisor',
                'is_system_role' => true,
                'permissions' => ['jobs.view', 'jobs.update', 'inventory.view', 'workers.view'],
                'approval_authority' => ['limit' => 5000],
            ],
            [
                'name' => 'Foreman',
                'is_system_role' => true,
                'permissions' => ['jobs.view', 'workers.view'],
                'approval_authority' => ['limit' => 0],
            ],
        ];
    }

    protected function getConstructionExpenseCategories(): array
    {
        return [
            ['name' => 'Materials', 'description' => 'Cement, bricks, timber, etc.', 'requires_approval' => true, 'approval_limit' => 10000],
            ['name' => 'Equipment Rental', 'description' => 'Machinery, tools rental', 'requires_approval' => true, 'approval_limit' => 5000],
            ['name' => 'Subcontractors', 'description' => 'Specialist contractors', 'requires_approval' => true, 'approval_limit' => 15000],
            ['name' => 'Transport', 'description' => 'Fuel, vehicle maintenance', 'requires_approval' => false],
            ['name' => 'Site Costs', 'description' => 'Security, utilities', 'requires_approval' => false],
            ['name' => 'Safety Equipment', 'description' => 'PPE, safety gear', 'requires_approval' => false],
            ['name' => 'Permits & Licenses', 'description' => 'Building permits, licenses', 'requires_approval' => true, 'approval_limit' => 5000],
        ];
    }

    protected function getRetailRoles(): array
    {
        return [
            [
                'name' => 'Owner',
                'is_system_role' => true,
                'permissions' => ['*'],
                'approval_authority' => ['limit' => 999999999],
            ],
            [
                'name' => 'Store Manager',
                'is_system_role' => true,
                'permissions' => ['customers.manage', 'jobs.manage', 'invoices.manage', 'payments.manage', 'inventory.manage', 'reports.view'],
                'approval_authority' => ['limit' => 5000],
            ],
            [
                'name' => 'Cashier',
                'is_system_role' => true,
                'permissions' => ['jobs.create', 'invoices.create', 'payments.create', 'inventory.view'],
                'approval_authority' => ['limit' => 0],
            ],
            [
                'name' => 'Stock Clerk',
                'is_system_role' => true,
                'permissions' => ['inventory.view', 'inventory.update'],
                'approval_authority' => ['limit' => 0],
            ],
        ];
    }

    protected function getRetailExpenseCategories(): array
    {
        return [
            ['name' => 'Inventory Purchase', 'description' => 'Stock purchases', 'requires_approval' => true, 'approval_limit' => 10000],
            ['name' => 'Rent', 'description' => 'Shop rent', 'requires_approval' => true, 'approval_limit' => 15000],
            ['name' => 'Utilities', 'description' => 'Electricity, water', 'requires_approval' => false],
            ['name' => 'Marketing', 'description' => 'Advertising, promotions', 'requires_approval' => true, 'approval_limit' => 3000],
            ['name' => 'Packaging', 'description' => 'Bags, boxes, wrapping', 'requires_approval' => false],
            ['name' => 'Security', 'description' => 'Security services', 'requires_approval' => false],
            ['name' => 'Maintenance', 'description' => 'Shop maintenance', 'requires_approval' => false],
        ];
    }

    protected function getServicesRoles(): array
    {
        return [
            [
                'name' => 'Owner',
                'is_system_role' => true,
                'permissions' => ['*'],
                'approval_authority' => ['limit' => 999999999],
            ],
            [
                'name' => 'Partner',
                'is_system_role' => true,
                'permissions' => ['customers.manage', 'jobs.manage', 'invoices.manage', 'payments.manage', 'reports.view'],
                'approval_authority' => ['limit' => 50000],
            ],
            [
                'name' => 'Consultant',
                'is_system_role' => true,
                'permissions' => ['customers.view', 'jobs.view', 'jobs.create', 'jobs.update'],
                'approval_authority' => ['limit' => 0],
            ],
            [
                'name' => 'Admin',
                'is_system_role' => true,
                'permissions' => ['customers.manage', 'invoices.manage', 'payments.manage', 'reports.view'],
                'approval_authority' => ['limit' => 5000],
            ],
        ];
    }

    protected function getServicesExpenseCategories(): array
    {
        return [
            ['name' => 'Professional Services', 'description' => 'Subcontractors, specialists', 'requires_approval' => true, 'approval_limit' => 10000],
            ['name' => 'Office Rent', 'description' => 'Office space rental', 'requires_approval' => true, 'approval_limit' => 15000],
            ['name' => 'Software & Subscriptions', 'description' => 'Software licenses, tools', 'requires_approval' => true, 'approval_limit' => 5000],
            ['name' => 'Travel', 'description' => 'Client visits, meetings', 'requires_approval' => true, 'approval_limit' => 3000],
            ['name' => 'Marketing', 'description' => 'Advertising, networking', 'requires_approval' => true, 'approval_limit' => 5000],
            ['name' => 'Office Supplies', 'description' => 'Stationery, supplies', 'requires_approval' => false],
            ['name' => 'Utilities', 'description' => 'Internet, phone, electricity', 'requires_approval' => false],
        ];
    }

    protected function getAutomotiveRoles(): array
    {
        return [
            [
                'name' => 'Owner',
                'is_system_role' => true,
                'permissions' => ['*'],
                'approval_authority' => ['limit' => 999999999],
            ],
            [
                'name' => 'Workshop Manager',
                'is_system_role' => true,
                'permissions' => ['customers.manage', 'jobs.manage', 'invoices.manage', 'payments.manage', 'inventory.manage', 'workers.manage', 'reports.view'],
                'approval_authority' => ['limit' => 10000],
            ],
            [
                'name' => 'Mechanic',
                'is_system_role' => true,
                'permissions' => ['jobs.view', 'jobs.update', 'inventory.view'],
                'approval_authority' => ['limit' => 0],
            ],
            [
                'name' => 'Parts Manager',
                'is_system_role' => true,
                'permissions' => ['inventory.manage', 'jobs.view'],
                'approval_authority' => ['limit' => 5000],
            ],
        ];
    }

    protected function getAutomotiveExpenseCategories(): array
    {
        return [
            ['name' => 'Parts & Supplies', 'description' => 'Auto parts, oils, fluids', 'requires_approval' => true, 'approval_limit' => 5000],
            ['name' => 'Tools & Equipment', 'description' => 'Tools, diagnostic equipment', 'requires_approval' => true, 'approval_limit' => 10000],
            ['name' => 'Rent', 'description' => 'Workshop rent', 'requires_approval' => true, 'approval_limit' => 10000],
            ['name' => 'Utilities', 'description' => 'Electricity, water', 'requires_approval' => false],
            ['name' => 'Waste Disposal', 'description' => 'Oil disposal, waste management', 'requires_approval' => false],
            ['name' => 'Marketing', 'description' => 'Advertising', 'requires_approval' => true, 'approval_limit' => 2000],
        ];
    }

    protected function getHospitalityRoles(): array
    {
        return [
            [
                'name' => 'Owner',
                'is_system_role' => true,
                'permissions' => ['*'],
                'approval_authority' => ['limit' => 999999999],
            ],
            [
                'name' => 'Manager',
                'is_system_role' => true,
                'permissions' => ['customers.manage', 'jobs.manage', 'invoices.manage', 'payments.manage', 'inventory.manage', 'workers.manage', 'reports.view'],
                'approval_authority' => ['limit' => 5000],
            ],
            [
                'name' => 'Chef',
                'is_system_role' => true,
                'permissions' => ['inventory.view', 'inventory.update', 'jobs.view'],
                'approval_authority' => ['limit' => 0],
            ],
            [
                'name' => 'Waiter',
                'is_system_role' => true,
                'permissions' => ['jobs.create', 'jobs.view', 'payments.create'],
                'approval_authority' => ['limit' => 0],
            ],
        ];
    }

    protected function getHospitalityExpenseCategories(): array
    {
        return [
            ['name' => 'Food & Ingredients', 'description' => 'Fresh produce, ingredients', 'requires_approval' => false],
            ['name' => 'Beverages', 'description' => 'Drinks, alcohol', 'requires_approval' => true, 'approval_limit' => 3000],
            ['name' => 'Kitchen Equipment', 'description' => 'Equipment, utensils', 'requires_approval' => true, 'approval_limit' => 5000],
            ['name' => 'Rent', 'description' => 'Restaurant rent', 'requires_approval' => true, 'approval_limit' => 15000],
            ['name' => 'Utilities', 'description' => 'Gas, electricity, water', 'requires_approval' => false],
            ['name' => 'Cleaning Supplies', 'description' => 'Cleaning materials', 'requires_approval' => false],
            ['name' => 'Marketing', 'description' => 'Advertising, promotions', 'requires_approval' => true, 'approval_limit' => 2000],
        ];
    }

    protected function getGeneralRoles(): array
    {
        return [
            [
                'name' => 'Owner',
                'is_system_role' => true,
                'permissions' => ['*'],
                'approval_authority' => ['limit' => 999999999],
            ],
            [
                'name' => 'Manager',
                'is_system_role' => true,
                'permissions' => ['customers.manage', 'jobs.manage', 'invoices.manage', 'payments.manage', 'inventory.manage', 'reports.view'],
                'approval_authority' => ['limit' => 10000],
            ],
            [
                'name' => 'Staff',
                'is_system_role' => true,
                'permissions' => ['customers.view', 'jobs.view', 'jobs.create', 'jobs.update', 'inventory.view'],
                'approval_authority' => ['limit' => 0],
            ],
        ];
    }

    protected function getGeneralExpenseCategories(): array
    {
        return [
            ['name' => 'Materials & Supplies', 'description' => 'Business materials', 'requires_approval' => false],
            ['name' => 'Equipment', 'description' => 'Equipment purchases', 'requires_approval' => true, 'approval_limit' => 5000],
            ['name' => 'Rent', 'description' => 'Office/shop rent', 'requires_approval' => true, 'approval_limit' => 10000],
            ['name' => 'Utilities', 'description' => 'Electricity, water, internet', 'requires_approval' => false],
            ['name' => 'Transport', 'description' => 'Fuel, vehicle costs', 'requires_approval' => false],
            ['name' => 'Marketing', 'description' => 'Advertising, promotions', 'requires_approval' => true, 'approval_limit' => 3000],
            ['name' => 'Miscellaneous', 'description' => 'Other expenses', 'requires_approval' => true, 'approval_limit' => 2000],
        ];
    }
}
