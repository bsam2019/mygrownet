<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * Seed the application's database for production environment.
     * Only includes essential data required for MyGrowNet platform to function.
     * 
     * MyGrowNet is a subscription-based community empowerment platform with:
     * - 7-level professional progression (Associate → Ambassador)
     * - Learning packs, mentorship, and skills training
     * - Community projects with profit-sharing
     * - Dual points system (Lifetime Points & Monthly Activity Points)
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting MyGrowNet production seeding...');
        $this->command->newLine();

        $this->call([
            // Core System Data (Required)
            RolesAndPermissionsSeeder::class,   // User roles and permissions (must be first)
            UserSeeder::class,                  // Admin and staff users
            
            // Membership Packages (7 Levels: Associate to Ambassador)
            PackageSeeder::class,               // Subscription packages with learning materials
            
            // Community Projects & Categories
            CategorySeeder::class,              // Project and investment categories
            
            // Achievement & Recognition System
            MyGrowNetAchievementsSeeder::class, // Milestone achievements and badges
            
            // Optional: Educational Content (uncomment if ready)
            // EducationalContentSeeder::class,  // Learning packs and courses
        ]);

        $this->command->newLine();
        $this->command->info('✅ Production seeding completed successfully!');
        $this->command->newLine();
        
        $this->command->info('📋 Essential data seeded:');
        $this->command->line('  ✓ User roles and permissions (Administrator, Manager, Support, Member)');
        $this->command->line('  ✓ Admin and staff users (admin@mygrownet.com, manager@mygrownet.com)');
        $this->command->line('  ✓ Subscription packages (7 levels: Associate → Ambassador)');
        $this->command->line('  ✓ Achievement system (milestones, badges, rewards)');
        $this->command->line('  ✓ Community project categories');
        
        $this->command->newLine();
        $this->command->info('💡 MyGrowNet Features Ready:');
        $this->command->line('  • 7-level professional progression system');
        $this->command->line('  • Dual points system (LP & MAP)');
        $this->command->line('  • Learning packs and mentorship programs');
        $this->command->line('  • Community projects with profit-sharing');
        $this->command->line('  • 3×3 forced matrix (7 levels deep)');
        
        $this->command->newLine();
        $this->command->warn('⚠️  Next Steps:');
        $this->command->line('  1. Login as admin: admin@mygrownet.com / mygrownet@2025!');
        $this->command->line('  2. Configure payment gateways (MTN MoMo, Airtel Money)');
        $this->command->line('  3. Upload learning materials and courses');
        $this->command->line('  4. Set up community projects for profit-sharing');
        
        $this->command->newLine();
        $this->command->info('🚀 Your MyGrowNet platform is ready for production!');
        $this->command->info('🌐 Visit: https://mygrownet.com');
    }
}