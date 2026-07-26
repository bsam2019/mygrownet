<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Contracts;

use App\Domain\Core\Contracts\ProviderContract;
use App\Domain\Core\ValueObjects\PlatformContext;

interface AccountingProvider extends ProviderContract
{
    public function getChartOfAccounts(PlatformContext $context): array;

    public function createJournalEntry(PlatformContext $context, array $entry): array;

    public function getTrialBalance(PlatformContext $context, \DateTimeImmutable $asOf): array;

    public function getBalanceSheet(PlatformContext $context, \DateTimeImmutable $asOf): array;
}
