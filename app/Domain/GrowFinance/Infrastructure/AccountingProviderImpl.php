<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Infrastructure;

use App\Domain\Core\ValueObjects\PlatformContext;
use App\Domain\GrowFinance\Contracts\AccountingProvider;
use App\Domain\GrowFinance\Services\AccountingService;

class AccountingProviderImpl implements AccountingProvider
{
    public function __construct(
        private AccountingService $accounting,
    ) {}

    public function capability(): string
    {
        return 'accounting';
    }

    public function getChartOfAccounts(PlatformContext $context): array
    {
        $businessId = $context->organizationId;
        if (!$businessId) {
            return [];
        }

        $trialBalance = $this->accounting->getTrialBalance($businessId);
        return $trialBalance['balances'] ?? [];
    }

    public function createJournalEntry(PlatformContext $context, array $entry): array
    {
        $businessId = $context->organizationId;
        if (!$businessId) {
            throw new \InvalidArgumentException('Organization context required');
        }

        return $this->accounting->createJournalEntry(
            businessId: $businessId,
            description: $entry['description'] ?? '',
            lines: $entry['lines'] ?? [],
            reference: $entry['reference'] ?? null,
            createdBy: $entry['created_by'] ?? null,
            currencyCode: $entry['currency_code'] ?? 'ZMW',
            exchangeRate: (float) ($entry['exchange_rate'] ?? 1.0),
        );
    }

    public function getTrialBalance(PlatformContext $context, \DateTimeImmutable $asOf): array
    {
        $businessId = $context->organizationId;
        if (!$businessId) {
            return [];
        }

        return $this->accounting->getTrialBalance($businessId);
    }

    public function getBalanceSheet(PlatformContext $context, \DateTimeImmutable $asOf): array
    {
        $businessId = $context->organizationId;
        if (!$businessId) {
            return [];
        }

        $trialBalance = $this->accounting->getTrialBalance($businessId);
        $balances = $trialBalance['balances'] ?? [];

        $assets = array_filter($balances, fn($b) => ($b['account']['type'] ?? '') === 'asset');
        $liabilities = array_filter($balances, fn($b) => ($b['account']['type'] ?? '') === 'liability');
        $equity = array_filter($balances, fn($b) => ($b['account']['type'] ?? '') === 'equity');

        return [
            'assets' => array_values($assets),
            'liabilities' => array_values($liabilities),
            'equity' => array_values($equity),
            'total_assets' => array_sum(array_column($assets, 'debit')) - array_sum(array_column($assets, 'credit')),
            'total_liabilities' => array_sum(array_column($liabilities, 'credit')) - array_sum(array_column($liabilities, 'debit')),
            'total_equity' => array_sum(array_column($equity, 'credit')) - array_sum(array_column($equity, 'debit')),
        ];
    }
}
