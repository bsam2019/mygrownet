<?php

namespace App\Domain\Core\Services;

class DataOwnershipRegistry
{
    private array $tableOwnership = [];

    public function __construct()
    {
        $this->registerDefaults();
    }

    public function register(string $tableName, string $module, string $tenantColumn = 'organization_id'): void
    {
        $this->tableOwnership[$tableName] = [
            'module' => $module,
            'tenant_column' => $tenantColumn,
        ];
    }

    public function owner(string $tableName): ?string
    {
        return $this->tableOwnership[$tableName]['module'] ?? null;
    }

    public function tenantColumn(string $tableName): ?string
    {
        return $this->tableOwnership[$tableName]['tenant_column'] ?? null;
    }

    public function isTenantScoped(string $tableName): bool
    {
        return isset($this->tableOwnership[$tableName]);
    }

    public function tablesOwnedBy(string $module): array
    {
        return array_keys(
            array_filter($this->tableOwnership, fn($t) => $t['module'] === $module)
        );
    }

    public function all(): array
    {
        return $this->tableOwnership;
    }

    private function registerDefaults(): void
    {
        $this->register('users', 'platform-core');
        $this->register('organizations', 'platform-core', '');
        $this->register('organization_members', 'platform-core');
        $this->register('applications', 'platform-core');
        $this->register('feature_flags', 'platform-core');
        $this->register('domains', 'platform-core');
        $this->register('dead_letter_queue', 'platform-core');
        $this->register('application_installations', 'platform-core');

        $this->register('companies', 'bms');
        $this->register('cms_users', 'bms');
        $this->register('invoices', 'bms');
        $this->register('customers', 'bms');
        $this->register('employees', 'bms');
        $this->register('bms_expenses', 'bms');

        $this->register('grow_net_users', 'grownet');
        $this->register('grow_net_commissions', 'grownet');

        $this->register('sa_companies', 'stockflow');
        $this->register('sa_items', 'stockflow');
        $this->register('sa_sales', 'stockflow');
        $this->register('sa_purchase_orders', 'stockflow');

        $this->register('growfinance_accounts', 'growfinance');
        $this->register('growfinance_invoices', 'growfinance');
        $this->register('growfinance_journal_entries', 'growfinance');
        $this->register('growfinance_budgets', 'growfinance');
        $this->register('growfinance_budget_items', 'growfinance');
        $this->register('loans_receivable', 'growfinance');

        // Phase F1-F3: Financial platform domains
        $this->register('billing_subscriptions', 'platform-billing');
        $this->register('billing_invoices', 'platform-billing');
        $this->register('subscription_plans', 'platform-billing');
        $this->register('payment_transactions', 'platform-payments');
        $this->register('payment_attempts', 'platform-payments');
        $this->register('payment_settlements', 'platform-payments');
        $this->register('currencies', 'financial-services-core');
        $this->register('exchange_rates', 'financial-services-core');

        $this->register('products', 'growmart');
        $this->register('orders', 'growmart');
        $this->register('grow_builder_sites', 'growbuilder');

        $this->register('notifications', 'notification');
        $this->register('support_tickets', 'support');
    }
}
