<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\Core\Services\OutboxService;
use App\Domain\GrowFinance\Entities\Account;
use App\Domain\GrowFinance\Entities\JournalEntry;
use App\Domain\GrowFinance\Entities\JournalLine;
use App\Domain\GrowFinance\Events\AccountBalanceChanged;
use App\Domain\GrowFinance\Events\JournalPosted;
use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalLineRepositoryInterface;
use App\Domain\GrowFinance\ValueObjects\JournalStatus;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class PostingEngine
{
    public function __construct(
        private AccountRepositoryInterface $accountRepo,
        private JournalEntryRepositoryInterface $journalEntryRepo,
        private JournalLineRepositoryInterface $journalLineRepo,
        private readonly OutboxService $outbox,
        private readonly CurrencyConversionService $currencyConversion,
    ) {}

    public function post(int $entryId): JournalEntry
    {
        $entry = $this->journalEntryRepo->findById($entryId);
        if (!$entry) {
            throw new \RuntimeException('Journal entry not found: ' . $entryId);
        }

        $lines = $this->journalLineRepo->findByJournalEntry($entry->id);

        $entryWithLines = new JournalEntry(
            id: $entry->id,
            businessId: $entry->businessId,
            journalNumber: $entry->journalNumber,
            date: $entry->date,
            description: $entry->description,
            reference: $entry->reference,
            status: $entry->status,
            reversalOfId: $entry->reversalOfId,
            reversalReason: $entry->reversalReason,
            sourceEventId: $entry->sourceEventId,
            periodId: $entry->periodId,
            currencyCode: $entry->currencyCode,
            exchangeRate: $entry->exchangeRate,
            functionalAmount: $entry->functionalAmount,
            createdBy: $entry->createdBy,
            postedAt: $entry->postedAt,
            dimensions: $entry->dimensions,
            createdAt: $entry->createdAt,
            updatedAt: $entry->updatedAt,
        );
        $entryWithLines->setLines($lines);

        if (!$entryWithLines->isBalanced()) {
            throw new \DomainException('Cannot post an unbalanced journal entry');
        }

        if (!$entryWithLines->status->isPostable()) {
            throw new \DomainException('Only draft entries can be posted');
        }

        $now = new DateTimeImmutable();
        $posted = $entryWithLines->post($now);

        $isMultiCurrency = strtoupper($entry->currencyCode) !== 'ZMW';

        DB::transaction(function () use ($posted, $entry, $lines, $isMultiCurrency) {
            $totalDebit = 0.0;
            $totalCredit = 0.0;
            $balanceChanges = [];

            foreach ($lines as $line) {
                $accountEntity = $this->accountRepo->findById($line->accountId);
                if (!$accountEntity) {
                    continue;
                }

                $netAmount = $line->debitAmount - $line->creditAmount;
                $totalDebit += $line->debitAmount;
                $totalCredit += $line->creditAmount;
                $previousBalance = $accountEntity->currentBalance;

                $newBalance = $accountEntity->normalBalance === 'debit'
                    ? $accountEntity->currentBalance + $netAmount
                    : $accountEntity->currentBalance - $netAmount;

                $this->accountRepo->save(new Account(
                    id: $accountEntity->id,
                    businessId: $accountEntity->businessId,
                    code: $accountEntity->code,
                    name: $accountEntity->name,
                    type: $accountEntity->type,
                    normalBalance: $accountEntity->normalBalance,
                    currencyCode: $accountEntity->currencyCode,
                    parentId: $accountEntity->parentId,
                    level: $accountEntity->level,
                    path: $accountEntity->path,
                    statementCategory: $accountEntity->statementCategory,
                    category: $accountEntity->category,
                    description: $accountEntity->description,
                    isSystem: $accountEntity->isSystem,
                    isActive: $accountEntity->isActive,
                    openingBalance: $accountEntity->openingBalance,
                    currentBalance: $newBalance,
                    createdAt: $accountEntity->createdAt,
                    updatedAt: null,
                ));

                // Compute functional amounts for multi-currency entries
                if ($isMultiCurrency) {
                    $func = $this->currencyConversion->computeFunctionalAmounts(
                        $line->debitAmount,
                        $line->creditAmount,
                        $entry->currencyCode,
                        $entry->exchangeRate,
                    );
                    $functionalNetAmount = $func['functional_debit'] - $func['functional_credit'];
                } else {
                    $functionalNetAmount = $netAmount;
                }

                $balanceChanges[] = new AccountBalanceChanged(
                    accountId: $accountEntity->id,
                    companyId: $entry->businessId,
                    previousBalance: $previousBalance,
                    newBalance: $newBalance,
                    changeAmount: $netAmount,
                    currency: $entry->currencyCode,
                );
            }

            // Save functional amounts on journal lines
            if ($isMultiCurrency) {
                foreach ($lines as $line) {
                    $func = $this->currencyConversion->computeFunctionalAmounts(
                        $line->debitAmount,
                        $line->creditAmount,
                        $entry->currencyCode,
                        $entry->exchangeRate,
                    );
                    $this->journalLineRepo->save(new JournalLine(
                        id: $line->id,
                        journalEntryId: $line->journalEntryId,
                        accountId: $line->accountId,
                        debitAmount: $line->debitAmount,
                        creditAmount: $line->creditAmount,
                        functionalDebitAmount: $func['functional_debit'],
                        functionalCreditAmount: $func['functional_credit'],
                        description: $line->description,
                        dimensions: $line->dimensions,
                    ));
                }
            }

            $this->journalEntryRepo->save($posted);

            foreach ($balanceChanges as $balanceEvent) {
                $this->outbox->insert(
                    eventName: AccountBalanceChanged::NAME,
                    payload: $balanceEvent->toPayload(),
                    context: ['business_id' => $entry->businessId],
                    publisher: 'growfinance',
                );
            }

            $this->outbox->insert(
                eventName: JournalPosted::NAME,
                payload: (new JournalPosted(
                    journalId: $entry->id,
                    companyId: $entry->businessId,
                    totalDebit: $totalDebit,
                    totalCredit: $totalCredit,
                    currency: $entry->currencyCode,
                    description: $entry->description ?? '',
                    postedAt: new DateTimeImmutable(),
                ))->toPayload(),
                context: ['business_id' => $entry->businessId],
                publisher: 'growfinance',
            );
        });

        return $this->journalEntryRepo->findById($entry->id);
    }

    public function reverse(int $entryId, string $reason): array
    {
        $entry = $this->journalEntryRepo->findById($entryId);
        if (!$entry) {
            throw new \RuntimeException('Journal entry not found');
        }

        if (!$entry->status->isReversible()) {
            throw new \DomainException('Only posted entries can be reversed');
        }

        $lines = $this->journalLineRepo->findByJournalEntry($entry->id);

        $reversed = $entry->reverse($reason, new DateTimeImmutable());

        return DB::transaction(function () use ($reversed, $entry, $lines, $reason) {
            $this->journalEntryRepo->save($reversed);

            $now = new DateTimeImmutable();
            $reversalJournalNumber = $this->generateJournalNumber($entry->businessId);

            $reversalEntry = $this->journalEntryRepo->save(new JournalEntry(
                id: null,
                businessId: $entry->businessId,
                journalNumber: $reversalJournalNumber,
                date: $now,
                description: 'Reversal: ' . ($entry->description ?? ''),
                reference: $entry->reference,
                status: JournalStatus::POSTED,
                reversalOfId: $entry->id,
                reversalReason: $reason,
                currencyCode: $entry->currencyCode,
                exchangeRate: $entry->exchangeRate,
                createdBy: $entry->createdBy,
                postedAt: $now,
                dimensions: $entry->dimensions,
            ));

            foreach ($lines as $line) {
                $this->journalLineRepo->save(new JournalLine(
                    id: null,
                    journalEntryId: $reversalEntry->id,
                    accountId: $line->accountId,
                    debitAmount: $line->creditAmount,
                    creditAmount: $line->debitAmount,
                    functionalDebitAmount: $line->functionalCreditAmount,
                    functionalCreditAmount: $line->functionalDebitAmount,
                    description: 'Reversal: ' . ($line->description ?? ''),
                    dimensions: $line->dimensions,
                ));
            }

            foreach ($lines as $line) {
                $accountEntity = $this->accountRepo->findById($line->accountId);
                if (!$accountEntity) {
                    continue;
                }

                $netAmount = $line->creditAmount - $line->debitAmount;
                $previousBalance = $accountEntity->currentBalance;

                $newBalance = $accountEntity->normalBalance === 'debit'
                    ? $accountEntity->currentBalance + $netAmount
                    : $accountEntity->currentBalance - $netAmount;

                $this->accountRepo->save(new Account(
                    id: $accountEntity->id,
                    businessId: $accountEntity->businessId,
                    code: $accountEntity->code,
                    name: $accountEntity->name,
                    type: $accountEntity->type,
                    normalBalance: $accountEntity->normalBalance,
                    currencyCode: $accountEntity->currencyCode,
                    parentId: $accountEntity->parentId,
                    level: $accountEntity->level,
                    path: $accountEntity->path,
                    statementCategory: $accountEntity->statementCategory,
                    category: $accountEntity->category,
                    description: $accountEntity->description,
                    isSystem: $accountEntity->isSystem,
                    isActive: $accountEntity->isActive,
                    openingBalance: $accountEntity->openingBalance,
                    currentBalance: $newBalance,
                    createdAt: $accountEntity->createdAt,
                    updatedAt: null,
                ));

                $this->outbox->insert(
                    eventName: AccountBalanceChanged::NAME,
                    payload: (new AccountBalanceChanged(
                        accountId: $accountEntity->id,
                        companyId: $entry->businessId,
                        previousBalance: $previousBalance,
                        newBalance: $newBalance,
                        changeAmount: $netAmount,
                        currency: $entry->currencyCode,
                    ))->toPayload(),
                    context: ['business_id' => $entry->businessId],
                    publisher: 'growfinance',
                );
            }

            return [
                'id' => $reversalEntry->id,
                'journal_number' => $reversalEntry->journalNumber,
                'reversed_entry' => $reversed->toArray(),
                'reversal_entry' => $reversalEntry->toArray(),
            ];
        });
    }

    public function postWithFxGainLoss(int $entryId, array $fxJournalLines): JournalEntry
    {
        $entry = $this->journalEntryRepo->findById($entryId);
        if (!$entry) {
            throw new \RuntimeException('Journal entry not found: ' . $entryId);
        }

        $lines = $this->journalLineRepo->findByJournalEntry($entry->id);

        // Prepend FX gain/loss lines as additional journal lines
        $allLines = array_merge($lines, $fxJournalLines);

        $entryWithLines = new JournalEntry(
            id: $entry->id,
            businessId: $entry->businessId,
            journalNumber: $entry->journalNumber,
            date: $entry->date,
            description: $entry->description,
            reference: $entry->reference,
            status: $entry->status,
            reversalOfId: $entry->reversalOfId,
            reversalReason: $entry->reversalReason,
            sourceEventId: $entry->sourceEventId,
            createdBy: $entry->createdBy,
            postedAt: $entry->postedAt,
            dimensions: $entry->dimensions,
            createdAt: $entry->createdAt,
            updatedAt: $entry->updatedAt,
        );
        $entryWithLines->setLines($allLines);

        if (!$entryWithLines->isBalanced()) {
            throw new \DomainException('Cannot post: entry plus FX lines must be balanced');
        }

        if (!$entryWithLines->status->isPostable()) {
            throw new \DomainException('Only draft entries can be posted');
        }

        // Save FX lines to DB
        foreach ($fxJournalLines as $fxLine) {
            $this->journalLineRepo->save($fxLine);
        }

        return $this->post($entryId);
    }

    private function generateJournalNumber(int $businessId): string
    {
        $lastEntry = DB::table('growfinance_journal_entries')
            ->where('business_id', $businessId)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastEntry ? ((int) substr($lastEntry->journal_number, 3)) + 1 : 1;

        return 'JE-' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
