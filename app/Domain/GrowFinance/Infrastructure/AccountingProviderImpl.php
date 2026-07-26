<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Infrastructure;

use App\Domain\Core\ValueObjects\PlatformContext;
use App\Domain\GrowFinance\Contracts\AccountingProvider;

class AccountingProviderImpl implements AccountingProvider
{
    public function capability(): string
    {
        return 'accounting';
    }

    public function getChartOfAccounts(PlatformContext $context): array
    {
        return [];
    }

    public function createJournalEntry(PlatformContext $context, array $entry): array
    {
        return $entry;
    }

    public function getTrialBalance(PlatformContext $context, \DateTimeImmutable $asOf): array
    {
        return [];
    }

    public function getBalanceSheet(PlatformContext $context, \DateTimeImmutable $asOf): array
    {
        return [];
    }
}
