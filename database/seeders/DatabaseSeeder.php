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
            RolesAndPermissionsSeeder::class,  // First create roles and permissions
            UserSeeder::class,                 // Then create users with roles
            MatrixSeeder::class,               // Create matrix test data
            CategorySeeder::class,             // Create investment categories
            ModuleTierSeeder::class,           // Create module subscription tiers
            BenefitSeeder::class,              // Create starter kit benefits
            GrowStreamPointSettingsSeeder::class, // GrowStream point configuration
            
            // Agency System
            AgencyRolesSeeder::class,          // Create agency roles
            SubscriptionPlansSeeder::class,    // Create subscription plans for agencies
            
            // GrowBuilder Templates
            MasterSiteTemplatesSeeder::class,  // All site templates
            
            // Storage & Professional Levels
            StoragePlanSeeder::class,          // Storage plans
            ProfessionalLevelSeeder::class,    // Professional levels
            
            // Skip NotificationTemplateSeeder - table structure incomplete
        ]);
    }
}
