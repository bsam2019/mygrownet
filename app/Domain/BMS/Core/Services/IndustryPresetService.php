<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\IndustryPresetModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\CMS\RoleModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ExpenseCategoryModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PricingRulesModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IndustryPresetService
{
    public function getAllPresets(): Collection
    {
        return IndustryPresetModel::active()->ordered()->get();
    }

    public function getPresetByCode(string $code): ?IndustryPresetModel
    {
        return IndustryPresetModel::where('code', $code)->first();
    }

    /**
     * Check what data already exists for a company
     * Returns counts of existing data that won't be overwritten
     */
    public function checkExistingData(int $companyId): array
    {
        $company = CompanyModel::find($companyId);
        
        if (!$company) {
            return [];
        }

        return [
            'has_industry' => !empty($company->industry_type),
            'current_industry' => $company->industry_type,
            'roles_count' => RoleModel::where('company_id', $companyId)->count(),
            'expense_categories_count' => ExpenseCategoryModel::where('company_id', $companyId)->count(),
            'pricing_rules_exist' => PricingRulesModel::where('company_id', $companyId)->exists(),
            'has_custom_job_types' => !empty($company->settings['job_types']),
            'has_custom_inventory_categories' => !empty($company->settings['inventory_categories']),
        ];
    }

    /**
     * Apply preset to a company — comprehensive setup.
     *
     * Applies:
     *  1. industry_type on company
     *  2. default_settings (merged, not overwritten)
     *  3. Roles
     *  4. Expense categories
     *  5. Pricing rules (aluminium/fabrication presets only)
     *  6. Inventory category labels stored on company settings
     *  7. Job type labels stored on company settings
     */
    public function applyPresetToCompany(int $companyId, string $presetCode): bool
    {
        $company = CompanyModel::find($companyId);
        $preset  = $this->getPresetByCode($presetCode);

        if (!$company || !$preset) {
            return false;
        }

        try {
            DB::transaction(function () use ($company, $preset) {
                Log::info("Applying preset {$preset->code} to company {$company->id}");

                // 1. Set industry type
                $oldIndustry = $company->industry_type;
                $company->update(['industry_type' => $preset->code]);
                Log::info("Industry type changed from '{$oldIndustry}' to '{$preset->code}'");

                // 2. Merge default settings (never overwrite existing user settings)
                if ($preset->default_settings) {
                    $current = $company->settings ?? [];
                    // array_merge: later arrays override earlier ones
                    // So $current (existing user settings) takes precedence over preset defaults
                    $merged = array_merge($preset->default_settings, $current);
                    $company->update(['settings' => $merged]);
                    Log::info("Settings merged. Existing settings preserved: " . count($current) . " keys");
                }

                // 3. Roles
                if ($preset->roles) {
                    $created = $this->createRolesFromPreset($company, $preset->roles);
                    Log::info("Roles: {$created} new roles created, existing roles preserved");
                }

                // 4. Expense categories
                if ($preset->expense_categories) {
                    $created = $this->createExpenseCategoriesFromPreset($company, $preset->expense_categories);
                    Log::info("Expense categories: {$created} new categories created, existing categories preserved");
                }

                // 5. Pricing rules — seed defaults for fabrication presets
                if ($preset->default_settings['fabrication_module'] ?? false) {
                    $created = $this->createDefaultPricingRules($company);
                    if ($created) {
                        Log::info("Pricing rules: Default rules created");
                    } else {
                        Log::info("Pricing rules: Existing rules preserved, no new rules created");
                    }
                }

                // 6. Store job types and inventory categories in company settings
                //    (used by dropdowns in job creation and inventory forms)
                $this->storePresetMetadata($company, $preset);
                Log::info("Preset metadata stored (job types, inventory categories, asset types)");

                // 7. Auto-enable Material Planning module for construction/aluminium industries
                if (in_array($preset->code, ['aluminium_fabrication', 'construction'])) {
                    $this->enableMaterialPlanningModule($company);
                    Log::info("Material Planning module auto-enabled for {$preset->code} industry");
                }
            });

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to apply preset: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create roles from preset — skip if already exists
     * Returns count of newly created roles
     */
    protected function createRolesFromPreset(CompanyModel $company, array $roles): int
    {
        $created = 0;
        
        foreach ($roles as $roleData) {
            $exists = RoleModel::where('company_id', $company->id)
                ->where('name', $roleData['name'])
                ->exists();

            if (!$exists) {
                RoleModel::create([
                    'company_id'        => $company->id,
                    'name'              => $roleData['name'],
                    'is_system_role'    => $roleData['is_system_role'] ?? true,
                    'permissions'       => $roleData['permissions'] ?? [],
                    'approval_authority'=> $roleData['approval_authority'] ?? ['limit' => 0],
                ]);
                $created++;
            }
        }
        
        return $created;
    }

    /**
     * Create expense categories — skip if already exists
     * Returns count of newly created categories
     */
    protected function createExpenseCategoriesFromPreset(CompanyModel $company, array $categories): int
    {
        $created = 0;
        
        foreach ($categories as $cat) {
            $exists = ExpenseCategoryModel::where('company_id', $company->id)
                ->where('name', $cat['name'])
                ->exists();

            if (!$exists) {
                ExpenseCategoryModel::create([
                    'company_id'       => $company->id,
                    'name'             => $cat['name'],
                    'description'      => $cat['description'] ?? null,
                    'requires_approval'=> $cat['requires_approval'] ?? false,
                    'approval_limit'   => $cat['approval_limit'] ?? null,
                    'is_active'        => true,
                ]);
                $created++;
            }
        }
        
        return $created;
    }

    /**
     * Seed default pricing rules for fabrication companies.
     * Only creates if no rules exist yet — never overwrites.
     * Returns true if rules were created, false if they already existed
     */
    protected function createDefaultPricingRules(CompanyModel $company): bool
    {
        $exists = PricingRulesModel::where('company_id', $company->id)->exists();

        if (!$exists) {
            PricingRulesModel::create([
                'company_id'             => $company->id,
                'sliding_window_rate'    => 500.00,
                'casement_window_rate'   => 550.00,
                'sliding_door_rate'      => 600.00,
                'hinged_door_rate'       => 650.00,
                'other_rate'             => 400.00,
                'material_cost_per_m2'   => 200.00,
                'labour_cost_per_m2'     => 100.00,
                'overhead_cost_per_m2'   => 50.00,
                'minimum_profit_percent' => 25.00,
                'tax_rate'               => 16.00,
            ]);
            return true;
        }
        
        return false;
    }

    /**
     * Store job types and inventory categories in company settings
     * so they appear in dropdowns without needing separate tables.
     */
    protected function storePresetMetadata(CompanyModel $company, IndustryPresetModel $preset): void
    {
        $settings = $company->fresh()->settings ?? [];

        // Only set if not already customised
        if (empty($settings['job_types']) && !empty($preset->job_types)) {
            $settings['job_types'] = $preset->job_types;
        }

        if (empty($settings['inventory_categories']) && !empty($preset->inventory_categories)) {
            $settings['inventory_categories'] = $preset->inventory_categories;
        }

        if (empty($settings['asset_types']) && !empty($preset->asset_types)) {
            $settings['asset_types'] = $preset->asset_types;
        }

        $company->update(['settings' => $settings]);
    }

    /**
     * Get preset configuration for the preview modal
     */
    public function getPresetConfiguration(string $code): ?array
    {
        $preset = $this->getPresetByCode($code);

        if (!$preset) {
            return null;
        }

        $hasFabrication = $preset->default_settings['fabrication_module'] ?? false;

        return [
            'code'                      => $preset->code,
            'name'                      => $preset->name,
            'description'               => $preset->description,
            'icon'                      => $preset->icon,
            'roles'                     => $preset->roles,
            'expense_categories'        => $preset->expense_categories,
            'job_types'                 => $preset->job_types,
            'inventory_categories'      => $preset->inventory_categories,
            'asset_types'               => $preset->asset_types,
            'default_settings'          => $preset->default_settings,
            'has_fabrication_module'    => $hasFabrication,
            // Counts for the preview card
            'roles_count'               => count($preset->roles ?? []),
            'expense_categories_count'  => count($preset->expense_categories ?? []),
            'job_types_count'           => count($preset->job_types ?? []),
            'inventory_categories_count'=> count($preset->inventory_categories ?? []),
            'asset_types_count'         => count($preset->asset_types ?? []),
        ];
    }

    /**
     * Get preset configuration with existing data check
     */
    public function getPresetConfigurationWithExistingData(string $code, int $companyId): ?array
    {
        $config = $this->getPresetConfiguration($code);
        
        if (!$config) {
            return null;
        }

        $config['existing_data'] = $this->checkExistingData($companyId);
        
        return $config;
    }

    /**
     * Enable Material Planning module for company
     */
    private function enableMaterialPlanningModule(CompanyModel $company): void
    {
        // Check if module exists
        $module = DB::table('modules')->where('id', 'material-planning')->first();
        
        if (!$module) {
            Log::warning("Material Planning module not found in modules table");
            return;
        }

        // Check if already enabled
        $exists = DB::table('module_subscriptions')
            ->where('user_id', $company->owner_id)
            ->where('module_id', 'material-planning')
            ->exists();

        if ($exists) {
            Log::info("Material Planning module already enabled for company {$company->id}");
            return;
        }

        // Enable the module for the company owner
        DB::table('module_subscriptions')->insert([
            'user_id' => $company->owner_id,
            'module_id' => 'material-planning',
            'subscription_tier' => 'free',
            'status' => 'active',
            'started_at' => now(),
            'expires_at' => null,
            'auto_renew' => false,
            'billing_cycle' => 'monthly',
            'amount' => 0.00,
            'currency' => 'ZMW',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info("Material Planning module enabled for company {$company->id}");
    }
}
