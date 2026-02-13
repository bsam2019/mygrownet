<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\IndustryPresetModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\CMS\RoleModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ExpenseCategoryModel;
use Illuminate\Support\Collection;

class IndustryPresetService
{
    /**
     * Get all active industry presets
     */
    public function getAllPresets(): Collection
    {
        return IndustryPresetModel::active()->ordered()->get();
    }

    /**
     * Get preset by code
     */
    public function getPresetByCode(string $code): ?IndustryPresetModel
    {
        return IndustryPresetModel::where('code', $code)->first();
    }

    /**
     * Apply preset to a company
     */
    public function applyPresetToCompany(int $companyId, string $presetCode): bool
    {
        $company = CompanyModel::find($companyId);
        $preset = $this->getPresetByCode($presetCode);

        if (!$company || !$preset) {
            return false;
        }

        // Update company industry type
        $company->update([
            'industry_type' => $preset->code,
        ]);

        // Apply default settings
        if ($preset->default_settings) {
            $currentSettings = $company->settings ?? [];
            $company->update([
                'settings' => array_merge($currentSettings, $preset->default_settings),
            ]);
        }

        // Create roles if they don't exist
        if ($preset->roles) {
            $this->createRolesFromPreset($company, $preset->roles);
        }

        // Create expense categories
        if ($preset->expense_categories) {
            $this->createExpenseCategoriesFromPreset($company, $preset->expense_categories);
        }

        return true;
    }

    /**
     * Create roles from preset configuration
     */
    protected function createRolesFromPreset(CompanyModel $company, array $roles): void
    {
        foreach ($roles as $roleData) {
            // Check if role already exists
            $exists = RoleModel::where('company_id', $company->id)
                ->where('name', $roleData['name'])
                ->exists();

            if (!$exists) {
                RoleModel::create([
                    'company_id' => $company->id,
                    'name' => $roleData['name'],
                    'is_system_role' => $roleData['is_system_role'] ?? true,
                    'permissions' => $roleData['permissions'] ?? [],
                    'approval_authority' => $roleData['approval_authority'] ?? ['limit' => 0],
                ]);
            }
        }
    }

    /**
     * Create expense categories from preset configuration
     */
    protected function createExpenseCategoriesFromPreset(CompanyModel $company, array $categories): void
    {
        foreach ($categories as $categoryData) {
            // Check if category already exists
            $exists = ExpenseCategoryModel::where('company_id', $company->id)
                ->where('name', $categoryData['name'])
                ->exists();

            if (!$exists) {
                ExpenseCategoryModel::create([
                    'company_id' => $company->id,
                    'name' => $categoryData['name'],
                    'description' => $categoryData['description'] ?? null,
                    'requires_approval' => $categoryData['requires_approval'] ?? false,
                    'approval_limit' => $categoryData['approval_limit'] ?? null,
                    'is_active' => true,
                ]);
            }
        }
    }

    /**
     * Get preset configuration for preview
     */
    public function getPresetConfiguration(string $code): ?array
    {
        $preset = $this->getPresetByCode($code);

        if (!$preset) {
            return null;
        }

        return [
            'code' => $preset->code,
            'name' => $preset->name,
            'description' => $preset->description,
            'icon' => $preset->icon,
            'roles_count' => count($preset->roles ?? []),
            'expense_categories_count' => count($preset->expense_categories ?? []),
            'job_types_count' => count($preset->job_types ?? []),
            'inventory_categories_count' => count($preset->inventory_categories ?? []),
            'asset_types_count' => count($preset->asset_types ?? []),
            'roles' => $preset->roles,
            'expense_categories' => $preset->expense_categories,
            'job_types' => $preset->job_types,
            'inventory_categories' => $preset->inventory_categories,
            'asset_types' => $preset->asset_types,
            'default_settings' => $preset->default_settings,
        ];
    }
}
