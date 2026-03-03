<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ExpenseCategoryModel;
use Illuminate\Database\Seeder;

class MyGrowNetExpenseCategoriesSeeder extends Seeder
{
    /**
     * Seed expense categories for MyGrowNet Platform internal tracking.
     */
    public function run(): void
    {
        // Get MyGrowNet Platform company
        $company = CompanyModel::where('name', 'MyGrowNet Platform')->first();

        if (!$company) {
            $this->command->error('MyGrowNet Platform company not found. Please create it first.');
            $this->command->info('Run: php artisan tinker');
            $this->command->info('Then: App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel::create([\'name\' => \'MyGrowNet Platform\', \'email\' => \'admin@mygrownet.com\', \'phone\' => \'+260977000000\', \'address\' => \'Lusaka, Zambia\', \'industry\' => \'Technology\', \'is_active\' => true]);');
            return;
        }

        // Check if categories already exist
        $existingCount = ExpenseCategoryModel::where('company_id', $company->id)->count();
        if ($existingCount > 0) {
            $this->command->info("Expense categories already exist for MyGrowNet Platform ({$existingCount} categories). Skipping...");
            return;
        }

        $categories = [
            [
                'name' => 'Marketing & Advertising',
                'description' => 'Digital marketing, ads, campaigns, promotional materials',
                'requires_approval' => true,
                'approval_limit' => 5000,
            ],
            [
                'name' => 'Office Expenses',
                'description' => 'Office supplies, stationery, cleaning, misc office items',
                'requires_approval' => false,
                'approval_limit' => null,
            ],
            [
                'name' => 'Travel & Transport',
                'description' => 'Business travel, fuel, vehicle maintenance, transport costs',
                'requires_approval' => true,
                'approval_limit' => 3000,
            ],
            [
                'name' => 'Infrastructure & Technology',
                'description' => 'Servers, hosting, software licenses, cloud services',
                'requires_approval' => true,
                'approval_limit' => 10000,
            ],
            [
                'name' => 'Legal & Compliance',
                'description' => 'Legal fees, compliance costs, regulatory expenses',
                'requires_approval' => true,
                'approval_limit' => 5000,
            ],
            [
                'name' => 'Professional Services',
                'description' => 'Consultants, contractors, professional fees',
                'requires_approval' => true,
                'approval_limit' => 5000,
            ],
            [
                'name' => 'Utilities',
                'description' => 'Electricity, water, internet, phone bills',
                'requires_approval' => false,
                'approval_limit' => null,
            ],
            [
                'name' => 'General Expenses',
                'description' => 'Miscellaneous expenses not categorized above',
                'requires_approval' => true,
                'approval_limit' => 2000,
            ],
            [
                'name' => 'Staff Welfare',
                'description' => 'Lunch, refreshments, staff benefits, team building',
                'requires_approval' => false,
                'approval_limit' => null,
            ],
            [
                'name' => 'Equipment & Maintenance',
                'description' => 'Equipment purchases, repairs, maintenance costs',
                'requires_approval' => true,
                'approval_limit' => 5000,
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

        $this->command->info('✅ Created ' . count($categories) . ' expense categories for MyGrowNet Platform');
        $this->command->info('Company ID: ' . $company->id);
        $this->command->info('Company Name: ' . $company->name);
    }
}
