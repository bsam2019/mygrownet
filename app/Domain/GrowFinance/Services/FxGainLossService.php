<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalLineRepositoryInterface;
use App\Domain\GrowFinance\Repositories\InvoiceRepositoryInterface;
use App\Domain\GrowFinance\Repositories\ExpenseRepositoryInterface;
use DateTimeImmutable;

class FxGainLossService
{
    public function __construct(
        private readonly AccountRepositoryInterface $accountRepo,
        private readonly JournalEntryRepositoryInterface $journalEntryRepo,
        private readonly JournalLineRepositoryInterface $journalLineRepo,
        private readonly InvoiceRepositoryInterface $invoiceRepo,
        private readonly ExpenseRepositoryInterface $expenseRepo,
        private readonly PostingEngine $postingEngine,
    ) {}

    public function computeAndPostRealizedFxGainLoss(
        int $businessId,
        int $journalEntryId,
        string $currencyCode,
        float $exchangeRate,
        float $amountInCurrency,
        int $fxGainAccountId,
        int $fxLossAccountId,
        string $description,
    ): void {
        if (strtoupper($currencyCode) === 'ZMW') {
            return;
        }

        // The original amount was recorded at some original rate.
        // We determine the functional amount at the original rate by looking at
        // existing journal lines for this business and account.
        // For simplicity, we assume the entry's exchangeRate IS the rate being used.
        $functionalAmount = round($amountInCurrency * $exchangeRate, 2);

        // We need the original functional amount from the referenced records.
        // This is a simplified version - in production, the caller provides the
        // original rate and amount via context.
        // The FX gain/loss is handled externally when the controller provides
        // the FX line data to PostingEngine::postWithFxGainLoss()
    }

    public function computePeriodEndRevaluation(
        int $businessId,
        int $accountId,
        string $currencyCode,
        float $periodEndRate,
    ): float {
        $account = $this->accountRepo->findById($accountId);
        if (!$account || strtoupper($currencyCode) === 'ZMW') {
            return 0.0;
        }

        // The current balance is in ZMW (functional). We need to determine
        // what it would be at the period-end rate. Since we don't track
        // the per-balance exchange rate, this is a best-effort computation.
        // This is a placeholder for period-end revaluation logic.
        return 0.0;
    }
}
