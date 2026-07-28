<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Repositories\IntercompanyTransactionRepositoryInterface;
use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalLineRepositoryInterface;
use App\Domain\GrowFinance\Entities\JournalEntry;
use App\Domain\GrowFinance\Entities\JournalLine;

class IntercompanyEliminationService
{
    public function __construct(
        private IntercompanyTransactionRepositoryInterface $icTxRepo,
        private AccountRepositoryInterface $accountRepo,
        private JournalEntryRepositoryInterface $journalEntryRepo,
        private JournalLineRepositoryInterface $journalLineRepo,
        private AccountingService $accountingService,
    ) {}

    /**
     * Auto-eliminate all matched intercompany transactions for a given period.
     * Returns array of created elimination journal entry IDs.
     */
    public function eliminateForPeriod(int $businessId, string $period): array
    {
        $start = $period . '-01';
        $end = date('Y-m-t', strtotime($start));

        $transactions = array_filter(
            $this->icTxRepo->findMatched(),
            fn($tx) => $tx->fromOrgId === $businessId || $tx->toOrgId === $businessId
        );

        $eliminated = [];

        foreach ($transactions as $tx) {
            if ($tx->status !== 'matched') continue;

            $elimAccount = $this->accountRepo->findByCode($businessId, '9100');
            if (!$elimAccount) continue;

            $description = sprintf(
                'IC elimination: %s %s %s → %s (%s)',
                $tx->transactionType,
                $tx->reference ?? '',
                $tx->fromOrgId,
                $tx->toOrgId,
                $tx->description ?? ''
            );

            $result = $this->accountingService->createJournalEntry(
                businessId: $businessId,
                description: trim($description),
                lines: [
                    [
                        'account_id' => $elimAccount->id,
                        'debit_amount' => $tx->fromOrgId === $businessId ? $tx->amount : 0,
                        'credit_amount' => $tx->toOrgId === $businessId ? $tx->amount : 0,
                    ],
                    [
                        'account_id' => $elimAccount->id,
                        'debit_amount' => $tx->toOrgId === $businessId ? $tx->amount : 0,
                        'credit_amount' => $tx->fromOrgId === $businessId ? $tx->amount : 0,
                    ],
                ],
                reference: 'IC-ELIM-' . $tx->id,
                date: new \DateTimeImmutable($end),
            );

            $eliminatedTx = $tx->eliminate();
            $this->icTxRepo->save($eliminatedTx);

            $eliminated[] = $result['id'];
        }

        return $eliminated;
    }
}
