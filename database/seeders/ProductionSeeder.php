<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * Seed the application's database for production environment.
     * Only includes essential data required for the platform to function.
     */
    public function run(): void
    {
        $this->call([
            // Essential system data
            InvestmentTierSeeder::class,        // Investment tiers are core to the business
            RolesAndPermissionsSeeder::class,   // Required for user access control
            CategorySeeder::class,              // Investment categories are essential
        ]);

        $this->command->info('Production seeding completed successfully!');
        $this->command->info('Essential data seeded:');
        $this->command->info('- Investment tiers (Basic, Starter, Builder, Leader, Elite)');
        $this->command->info('- User roles and permissions');
        $this->command->info('- Investment categories');
        $this->command->warn('Note: No test users, investments, or transactions were created.');
        $this->command->warn('Create your admin user manually or use: php artisan make:admin');
    }
}