<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ExpenseCategoryModel;
use Illuminate\Database\Seeder;

class ExpenseCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        // Get Geopamu company
        $company = CompanyModel::where('name', 'Geopamu Investments Limited')->first();

        if (!$company) {
            $this->command->warn('Geopamu company not found. Run GeopamuCmsSeeder first.');
            return;
        }

        $categories = [
            [
                'name' => 'Materials & Supplies',
                'description' => 'Raw materials, ink, paper, vinyl, t-shirts, etc.',
                'requires_approval' => false,
                'approval_limit' => null,
            ],
            [
                'name' => 'Equipment & Maintenance',
                'description' => 'Printer maintenance, equipment repairs, spare parts',
                'requires_approval' => true,
                'approval_limit' => 5000,
            ],
            [
                'name' => 'Transport & Delivery',
                'description' => 'Fuel, vehicle maintenance, delivery costs',
                'requires_approval' => false,
                'approval_limit' => null,
            ],
            [
                'name' => 'Utilities',
                'description' => 'Electricity, water, internet, phone',
                'requires_approval' => false,
                'approval_limit' => null,
            ],
            [
                'name' => 'Rent & Premises',
                'description' => 'Office rent, security, cleaning',
                'requires_approval' => true,
                'approval_limit' => 10000,
            ],
            [
                'name' => 'Marketing & Advertising',
                'description' => 'Promotional materials, advertising costs',
                'requires_approval' => true,
                'approval_limit' => 3000,
            ],
            [
                'name' => 'Professional Services',
                'description' => 'Accounting, legal, consulting fees',
                'requires_approval' => true,
                'approval_limit' => 5000,
            ],
            [
                'name' => 'Office Supplies',
                'description' => 'Stationery, cleaning supplies, misc office items',
                'requires_approval' => false,
                'approval_limit' => null,
            ],
            [
                'name' => 'Staff Welfare',
                'description' => 'Lunch, refreshments, staff benefits',
                'requires_approval' => false,
                'approval_limit' => null,
            ],
            [
                'name' => 'Miscellaneous',
                'description' => 'Other expenses not categorized above',
                'requires_approval' => true,
                'approval_limit' => 2000,
            ],
        ];

        foreach ($categories as $category) {
            ExpenseCategoryModel::create([
                'company_id' => $company->id,
                'name' => $category['name'],
                'description' => $category['description'],
                'requires_approval' => $category['requires_approval'],
                'approval_limit' => $category['approval_limit'],
                'is_active' => true,
            ]);
        }

        $this->command->info('✅ Created ' . count($categories) . ' expense categories for Geopamu');
    }
}
