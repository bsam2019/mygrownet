<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;

class MyGrowNetPlatformCompanySeeder extends Seeder
{
    /**
     * Create MyGrowNet Platform company for internal expense tracking.
     * This company is used to track MyGrowNet's own operational expenses.
     */
    public function run(): void
    {
        // Check if company already exists
        $company = CompanyModel::where('name', 'MyGrowNet Platform')->first();
        
        if ($company) {
            $this->command->warn("⚠ Company already exists: {$company->name} (ID: {$company->id})");
            $this->command->info("Skipping seeder to prevent duplicates.");
            return;
        }

        // Create MyGrowNet Platform company
        $company = CompanyModel::create([
            'name' => 'MyGrowNet Platform',
            'industry_type' => 'technology',
            'email' => 'admin@mygrownet.com',
            'phone' => '+260977000000',
            'address' => 'Lusaka, Zambia',
            'city' => 'Lusaka',
            'status' => 'active',
            'settings' => [
                'currency' => 'ZMW',
                'business_hours' => '08:00-17:00',
                'payment_methods' => ['mobile_money', 'bank_transfer'],
                'vat_enabled' => false,
                'invoice_due_days' => 30,
                'approval_thresholds' => [
                    'expense' => 5000,
                    'commission' => 2000,
                ],
            ],
        ]);

        $this->command->info("✅ Created company: {$company->name}");
        $this->command->info("   Company ID: {$company->id}");
        $this->command->info("   Email: {$company->email}");
        $this->command->info("   Industry: {$company->industry_type}");
        $this->command->newLine();
        $this->command->info("💡 This company is used for internal MyGrowNet expense tracking.");
        $this->command->info("   You can now run: php artisan db:seed --class=MyGrowNetExpenseCategoriesSeeder");
    }
}
