<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Master Template Seeder
 * 
 * Seeds all GrowBuilder site templates in the correct order.
 * Run with: php artisan db:seed --class=AllTemplatesSeeder
 */
class AllTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Seeding GrowBuilder Templates...');
        $this->command->newLine();

        // Seed industries first (required for templates)
        $this->command->info('📋 Seeding template industries...');
        $this->call(SiteTemplatesSeeder::class);
        $this->command->info('✅ Industries seeded');
        $this->command->newLine();

        // Seed basic templates
        $this->command->info('🎨 Seeding basic templates...');
        $this->call(MoreSiteTemplatesSeeder::class);
        $this->command->info('✅ Basic templates seeded');
        $this->command->newLine();

        // Seed enhanced templates
        $this->command->info('✨ Seeding enhanced templates...');
        $this->call(EnhancedTemplatesSeeder::class);
        $this->command->info('✅ Enhanced templates seeded');
        $this->command->newLine();

        // Seed industry-specific templates
        $this->command->info('🏢 Seeding industry templates...');
        $this->call(IndustryTemplatesSeeder::class);
        $this->command->info('✅ Industry templates seeded');
        $this->command->newLine();

        // Seed final templates
        $this->command->info('🎯 Seeding final templates...');
        $this->call(FinalSiteTemplatesSeeder::class);
        $this->command->info('✅ Final templates seeded');
        $this->command->newLine();

        // Seed premium templates
        $this->command->info('💎 Seeding premium templates...');
        $this->call(PremiumTemplatesSeeder::class);
        $this->command->info('✅ Premium templates seeded');
        $this->command->newLine();

        $this->command->info('🎉 All templates seeded successfully!');
        $this->command->newLine();

        // Display summary
        $this->displaySummary();
    }

    private function displaySummary(): void
    {
        $totalTemplates = \App\Models\GrowBuilder\SiteTemplate::count();
        $premiumTemplates = \App\Models\GrowBuilder\SiteTemplate::where('is_premium', true)->count();
        $activeTemplates = \App\Models\GrowBuilder\SiteTemplate::where('is_active', true)->count();
        $industries = \App\Models\GrowBuilder\SiteTemplateIndustry::count();

        $this->command->table(
            ['Metric', 'Count'],
            [
                ['Total Templates', $totalTemplates],
                ['Premium Templates', $premiumTemplates],
                ['Active Templates', $activeTemplates],
                ['Industries', $industries],
            ]
        );
    }
}
