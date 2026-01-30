<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MasterSiteTemplatesSeeder extends Seeder
{
    /**
     * Master seeder that runs all site template seeders
     * Use this to ensure all templates are seeded in production
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding all site templates...');
        $this->command->newLine();
        
        // Clear existing templates
        $this->command->info('🗑️  Clearing existing templates...');
        \DB::table('site_template_pages')->delete();
        \DB::table('site_templates')->delete();
        \DB::table('site_template_industries')->delete();
        $this->command->info('✅ Tables cleared');
        $this->command->newLine();
        
        $seeders = [
            SiteTemplatesSeeder::class,
            AdditionalSiteTemplatesSeeder::class,
            MoreSiteTemplatesSeeder::class,
            IndustryTemplatesSeeder::class,
            EnhancedTemplatesSeeder::class,
            FinalSiteTemplatesSeeder::class,
            AgricultureSiteTemplateSeeder::class,
            DHCreativeStudioSeeder::class,
            NdelimaConstructionSeeder::class,
            NdelimaConstructionV2Seeder::class,
            FlamesOfHopeChurchSeeder::class,
        ];

        foreach ($seeders as $seeder) {
            try {
                $this->command->info("  → Running {$seeder}...");
                $this->call($seeder);
                $this->command->info("  ✓ {$seeder} completed");
            } catch (\Exception $e) {
                $this->command->warn("  ⚠ {$seeder} failed: " . $e->getMessage());
                // Continue with other seeders even if one fails
            }
        }

        $this->command->info('✓ All site template seeders completed!');
    }
}
