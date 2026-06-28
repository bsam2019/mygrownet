<?php

namespace App\Domain\GrowFinance\Services;

use App\Domain\Module\Contracts\ModuleUsageProviderInterface;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * GrowFinance Usage Provider
 * 
 * Provides usage metrics for GrowFinance module.
 * Implements the centralized ModuleUsageProviderInterface.
 */
class GrowFinanceUsageProvider implements ModuleUsageProviderInterface
{
    public function getModuleId(): string
    {
        return 'growfinance';
    }

    public function getUsageMetrics(User $user): array
    {
        return [
            'transactions_per_month' => $this->getMonthlyTransactionCount($user),
            'invoices_per_month' => $this->getMonthlyInvoiceCount($user),
            'customers' => $this->getCustomerCount($user),
            'vendors' => $this->getVendorCount($user),
            'bank_accounts' => $this->getBankAccountCount($user),
            'receipt_storage_mb' => (int) round($this->getStorageUsed($user) / 1024 / 1024),
        ];
    }

    public function getMetric(User $user, string $metricKey): int
    {
        return match ($metricKey) {
            'transactions_per_month' => $this->getMonthlyTransactionCount($user),
            'invoices_per_month' => $this->getMonthlyInvoiceCount($user),
            'customers' => $this->getCustomerCount($user),
            'vendors' => $this->getVendorCount($user),
            'bank_accounts' => $this->getBankAccountCount($user),
            'receipt_storage_mb' => (int) round($this->getStorageUsed($user) / 1024 / 1024),
            'invoice_templates' => $this->getInvoiceTemplateCount($user),
            'team_members' => $this->getTeamMemberCount($user),
            default => 0,
        };
    }

    public function clearCache(User $user): void
    {
        $monthKey = now()->format('Y-m');
        Cache::forget("growfinance_tx_count_{$user->id}_{$monthKey}");
        Cache::forget("growfinance_inv_count_{$user->id}_{$monthKey}");
        Cache::forget("growfinance_customer_count_{$user->id}");
        Cache::forget("growfinance_vendor_count_{$user->id}");
        Cache::forget("growfinance_bank_count_{$user->id}");
        Cache::forget("growfinance_storage_{$user->id}");
        Cache::forget("growfinance_template_count_{$user->id}");
        Cache::forget("growfinance_team_count_{$user->id}");
    }

    public function getStorageUsed(User $user): int
    {
        $cacheKey = "growfinance_storage_{$user->id}";

        return Cache::remember($cacheKey, 600, function () use ($user) {
            return DB::table('growfinance_expenses')
                ->where('business_id', $user->id)
                ->whereNotNull('receipt_path')
                ->sum('receipt_size') ?? 0;
        });
    }

    private function getMonthlyTransactionCount(User $user): int
    {
        $cacheKey = "growfinance_tx_count_{$user->id}_" . now()->format('Y-m');

        return Cache::remember($cacheKey, 300, function () use ($user) {
            $startOfMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();

            $invoices = DB::table('growfinance_invoices')
                ->where('business_id', $user->id)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();

            $expenses = DB::table('growfinance_expenses')
                ->where('business_id', $user->id)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();

            return $invoices + $expenses;
        });
    }

    private function getMonthlyInvoiceCount(User $user): int
    {
        $cacheKey = "growfinance_inv_count_{$user->id}_" . now()->format('Y-m');

        return Cache::remember($cacheKey, 300, function () use ($user) {
            return DB::table('growfinance_invoices')
                ->where('business_id', $user->id)
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count();
        });
    }

    private function getCustomerCount(User $user): int
    {
        $cacheKey = "growfinance_customer_count_{$user->id}";

        return Cache::remember($cacheKey, 300, function () use ($user) {
            return DB::table('growfinance_customers')
                ->where('business_id', $user->id)
                ->count();
        });
    }

    private function getVendorCount(User $user): int
    {
        $cacheKey = "growfinance_vendor_count_{$user->id}";

        return Cache::remember($cacheKey, 300, function () use ($user) {
            return DB::table('growfinance_vendors')
                ->where('business_id', $user->id)
                ->count();
        });
    }

    private function getBankAccountCount(User $user): int
    {
        $cacheKey = "growfinance_bank_count_{$user->id}";

        return Cache::remember($cacheKey, 300, function () use ($user) {
            return DB::table('growfinance_bank_accounts')
                ->where('business_id', $user->id)
                ->count();
        });
    }

    private function getInvoiceTemplateCount(User $user): int
    {
        $cacheKey = "growfinance_template_count_{$user->id}";

        return Cache::remember($cacheKey, 300, function () use ($user) {
            return DB::table('growfinance_invoice_templates')
                ->where('business_id', $user->id)
                ->count();
        });
    }

    private function getTeamMemberCount(User $user): int
    {
        $cacheKey = "growfinance_team_count_{$user->id}";

        return Cache::remember($cacheKey, 300, function () use ($user) {
            return DB::table('growfinance_team_members')
                ->where('business_id', $user->id)
                ->where('status', 'active')
                ->count();
        });
    }
}
