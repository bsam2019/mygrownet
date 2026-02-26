<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            InvestmentTierSeeder::class,
            RolesAndPermissionsSeeder::class,  // First create roles and permissions
            UserSeeder::class,                 // Then create users with roles
            MatrixSeeder::class,               // Create matrix test data
            CategorySeeder::class,             // Create investment categories
            InvestmentSeeder::class,          // Create investments for users
            TransactionSeeder::class,          // Create related transactions
            InvestmentMetricSeeder::class,     // Create investment metrics
            InvestmentOpportunitySeeder::class,
            ModuleTierSeeder::class,           // Create module subscription tiers
            BenefitSeeder::class,              // Create starter kit benefits
        ]);
    }
}
