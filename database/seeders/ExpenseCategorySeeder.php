<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ExpenseCategoryModel;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultCategories = [
            [
                'name' => 'Office Supplies',
                'description' => 'Stationery, paper, pens, and other office supplies',
            ],
            [
                'name' => 'Marketing & Advertising',
                'description' => 'Marketing campaigns, advertising, promotional materials',
            ],
            [
                'name' => 'Travel & Transportation',
                'description' => 'Business travel, fuel, vehicle maintenance, transport costs',
            ],
            [
                'name' => 'Utilities',
                'description' => 'Electricity, water, internet, phone bills',
            ],
            [
                'name' => 'Professional Services',
                'description' => 'Legal fees, accounting, consulting services',
            ],
            [
                'name' => 'Equipment & Maintenance',
                'description' => 'Equipment purchases, repairs, and maintenance',
            ],
            [
                'name' => 'Software & Subscriptions',
                'description' => 'Software licenses, SaaS subscriptions, cloud services',
            ],
            [
                'name' => 'Employee Benefits',
                'description' => 'Staff welfare, training, team building activities',
            ],
            [
                'name' => 'Rent & Facilities',
                'description' => 'Office rent, facility management, building maintenance',
            ],
            [
                'name' => 'Miscellaneous',
                'description' => 'Other business expenses not covered by specific categories',
            ],
        ];

        // Get all companies
        $companies = CompanyModel::all();

        foreach ($companies as $company) {
            foreach ($defaultCategories as $category) {
                ExpenseCategoryModel::firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'name' => $category['name'],
                    ],
                    [
                        'description' => $category['description'],
                        'requires_approval' => false,
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command->info('Default expense categories seeded successfully!');
    }
}
