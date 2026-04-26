<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PricingRulesModel;

class GeopamuPricingRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Setting up pricing rules for Geopamu Aluminium & Construction...');

        // Find Geopamu company
        $company = CompanyModel::where('name', 'Geopamu Aluminium & Construction')->first();

        if (!$company) {
            $this->command->warn('Geopamu Aluminium & Construction company not found. Skipping...');
            return;
        }

        // Check if pricing rules already exist
        $existingRules = PricingRulesModel::where('company_id', $company->id)->first();

        if ($existingRules) {
            $this->command->info('Pricing rules already exist for Geopamu. Skipping...');
            return;
        }

        // Create default pricing rules
        PricingRulesModel::create([
            'company_id' => $company->id,
            
            // Selling prices per m² (Zambian Kwacha)
            'sliding_window_rate' => 500.00,
            'casement_window_rate' => 550.00,
            'sliding_door_rate' => 600.00,
            'hinged_door_rate' => 650.00,
            'other_rate' => 400.00,
            
            // Internal costs per m²
            'material_cost_per_m2' => 200.00,
            'labour_cost_per_m2' => 100.00,
            'overhead_cost_per_m2' => 50.00,
            
            // Profit rules
            'minimum_profit_percent' => 25.00,
            
            // Tax
            'tax_rate' => 16.00,
        ]);

        $this->command->info('✓ Pricing rules created for Geopamu Aluminium & Construction');
        $this->command->info('  - Sliding Window: K500/m²');
        $this->command->info('  - Casement Window: K550/m²');
        $this->command->info('  - Sliding Door: K600/m²');
        $this->command->info('  - Hinged Door: K650/m²');
        $this->command->info('  - Minimum Profit: 25%');
    }
}
