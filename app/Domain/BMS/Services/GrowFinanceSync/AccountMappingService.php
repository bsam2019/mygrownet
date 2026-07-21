<?php

declare(strict_types=1);

namespace App\Domain\BMS\Services\GrowFinanceSync;

use App\Infrastructure\Persistence\Eloquent\BMS\GrowFinanceAccountMappingModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceAccountModel;
use Illuminate\Support\Collection;

class AccountMappingService
{
    /**
     * Get GrowFinance account for a CMS entity
     */
    public function getAccount(
        int $companyId,
        string $entityType,
        ?string $category = null,
        ?string $paymentMethod = null
    ): ?GrowFinanceAccountModel {
        // Try to find specific mapping first
        $mapping = GrowFinanceAccountMappingModel::forCompany($companyId)
            ->forEntityType($entityType)
            ->when($category, fn($q) => $q->forCategory($category))
            ->when($paymentMethod, fn($q) => $q->forPaymentMethod($paymentMethod))
            ->first();

        if ($mapping) {
            return $mapping->growfinanceAccount;
        }

        // Fall back to default mapping
        $defaultMapping = GrowFinanceAccountMappingModel::forCompany($companyId)
            ->forEntityType($entityType)
            ->defaults()
            ->first();

        return $defaultMapping?->growfinanceAccount;
    }

    /**
     * Get cash account based on payment method
     */
    public function getCashAccount(int $companyId, string $paymentMethod): ?GrowFinanceAccountModel
    {
        return $this->getAccount($companyId, 'payment', null, $paymentMethod);
    }

    /**
     * Create default account mappings for a company
     */
    public function createDefaultMappings(int $companyId): void
    {
        // Get or create default GrowFinance accounts
        $accounts = $this->getOrCreateDefaultAccounts($companyId);

        $defaultMappings = [
            // Invoice mappings
            ['cms_entity_type' => 'invoice', 'cms_category' => 'sales', 'account_code' => '4000', 'is_default' => true],
            ['cms_entity_type' => 'invoice', 'cms_category' => 'vat', 'account_code' => '2300', 'is_default' => false],
            ['cms_entity_type' => 'invoice', 'cms_category' => 'receivable', 'account_code' => '1100', 'is_default' => false],

            // Expense mappings by category
            ['cms_entity_type' => 'expense', 'cms_category' => 'Materials', 'account_code' => '5000', 'is_default' => false],
            ['cms_entity_type' => 'expense', 'cms_category' => 'Labour', 'account_code' => '5100', 'is_default' => false],
            ['cms_entity_type' => 'expense', 'cms_category' => 'Equipment', 'account_code' => '5200', 'is_default' => false],
            ['cms_entity_type' => 'expense', 'cms_category' => 'Transport', 'account_code' => '5400', 'is_default' => false],
            ['cms_entity_type' => 'expense', 'cms_category' => 'Subcontractor', 'account_code' => '5000', 'is_default' => false],
            ['cms_entity_type' => 'expense', 'cms_category' => 'Utilities', 'account_code' => '5300', 'is_default' => false],
            ['cms_entity_type' => 'expense', 'cms_category' => 'Office', 'account_code' => '5500', 'is_default' => false],
            ['cms_entity_type' => 'expense', 'cms_category' => 'Marketing', 'account_code' => '5600', 'is_default' => false],
            ['cms_entity_type' => 'expense', 'cms_category' => 'Other', 'account_code' => '5900', 'is_default' => true],
            ['cms_entity_type' => 'expense', 'cms_category' => 'vat_receivable', 'account_code' => '1100', 'is_default' => false],

            // Payment method mappings
            ['cms_entity_type' => 'payment', 'cms_payment_method' => 'cash', 'account_code' => '1000', 'is_default' => false],
            ['cms_entity_type' => 'payment', 'cms_payment_method' => 'bank_transfer', 'account_code' => '1010', 'is_default' => true],
            ['cms_entity_type' => 'payment', 'cms_payment_method' => 'mobile_money', 'account_code' => '1020', 'is_default' => false],
            ['cms_entity_type' => 'payment', 'cms_payment_method' => 'cheque', 'account_code' => '1010', 'is_default' => false],
        ];

        foreach ($defaultMappings as $mapping) {
            $account = $accounts->firstWhere('code', $mapping['account_code']);
            
            if ($account) {
                GrowFinanceAccountMappingModel::updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'cms_entity_type' => $mapping['cms_entity_type'],
                        'cms_category' => $mapping['cms_category'] ?? null,
                        'cms_payment_method' => $mapping['cms_payment_method'] ?? null,
                    ],
                    [
                        'growfinance_account_id' => $account->id,
                        'is_default' => $mapping['is_default'],
                    ]
                );
            }
        }
    }

    /**
     * Get or create default GrowFinance accounts
     */
    private function getOrCreateDefaultAccounts(int $companyId): Collection
    {
        // Check if accounts already exist
        $existingAccounts = GrowFinanceAccountModel::forBusiness($companyId)->get();

        if ($existingAccounts->isNotEmpty()) {
            return $existingAccounts;
        }

        // Create default accounts using AccountingService
        $accountingService = app(\App\Domain\GrowFinance\Services\AccountingService::class);
        $accountingService->initializeChartOfAccounts($companyId);

        return GrowFinanceAccountModel::forBusiness($companyId)->get();
    }

    /**
     * Get all mappings for a company
     */
    public function getMappings(int $companyId): Collection
    {
        return GrowFinanceAccountMappingModel::forCompany($companyId)
            ->with('growfinanceAccount')
            ->get();
    }

    /**
     * Update or create a mapping
     */
    public function updateMapping(
        int $companyId,
        string $entityType,
        ?string $category,
        ?string $paymentMethod,
        int $accountId,
        bool $isDefault = false
    ): GrowFinanceAccountMappingModel {
        return GrowFinanceAccountMappingModel::updateOrCreate(
            [
                'company_id' => $companyId,
                'cms_entity_type' => $entityType,
                'cms_category' => $category,
                'cms_payment_method' => $paymentMethod,
            ],
            [
                'growfinance_account_id' => $accountId,
                'is_default' => $isDefault,
            ]
        );
    }

    /**
     * Delete a mapping
     */
    public function deleteMapping(int $mappingId): bool
    {
        $mapping = GrowFinanceAccountMappingModel::find($mappingId);
        return $mapping ? $mapping->delete() : false;
    }
}
