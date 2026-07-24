<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\Account;
use App\Domain\GrowFinance\Entities\JournalEntry;
use App\Domain\GrowFinance\Entities\JournalLine;
use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalLineRepositoryInterface;
use Illuminate\Support\Facades\DB;

class BankingService
{
    public function __construct(
        private AccountRepositoryInterface $accountRepo,
        private JournalEntryRepositoryInterface $journalEntryRepo,
        private JournalLineRepositoryInterface $journalLineRepo,
    ) {}

    public function recordDeposit(
        int $businessId,
        int $accountId,
        float $amount,
        string $description,
        ?string $reference,
        string $date,
        int $createdBy
    ): array {
        return DB::transaction(function () use ($businessId, $accountId, $amount, $description, $reference, $date, $createdBy) {
            $equityAccount = $this->accountRepo->findByCode($businessId, '4200');

            if (!$equityAccount) {
                $equityAccount = $this->accountRepo->findByCode($businessId, '3000');
            }

            if (!$equityAccount) {
                throw new \RuntimeException('No equity or income account found for deposit');
            }

            $entryNumber = $this->generateEntryNumber($businessId);

            $entry = $this->journalEntryRepo->save(new JournalEntry(
                id: null,
                businessId: $businessId,
                entryNumber: $entryNumber,
                entryDate: new \DateTimeImmutable($date),
                description: $description,
                reference: $reference,
                isPosted: true,
                createdBy: $createdBy,
                createdAt: null,
                updatedAt: null,
            ));

            $this->journalLineRepo->save(new JournalLine(
                id: null,
                journalEntryId: $entry->id,
                accountId: $accountId,
                debitAmount: $amount,
                creditAmount: 0,
                description: $description,
                createdAt: null,
                updatedAt: null,
            ));

            $this->journalLineRepo->save(new JournalLine(
                id: null,
                journalEntryId: $entry->id,
                accountId: $equityAccount->id,
                debitAmount: 0,
                creditAmount: $amount,
                description: $description,
                createdAt: null,
                updatedAt: null,
            ));

            $cashAccount = $this->accountRepo->findById($accountId);
            if (!$cashAccount) {
                throw new \RuntimeException('Cash account not found');
            }

            $this->accountRepo->save(new Account(
                id: $cashAccount->id,
                businessId: $cashAccount->businessId,
                code: $cashAccount->code,
                name: $cashAccount->name,
                type: $cashAccount->type,
                category: $cashAccount->category,
                description: $cashAccount->description,
                isSystem: $cashAccount->isSystem,
                isActive: $cashAccount->isActive,
                openingBalance: $cashAccount->openingBalance,
                currentBalance: $cashAccount->currentBalance + $amount,
                createdAt: $cashAccount->createdAt,
                updatedAt: null,
            ));

            $this->accountRepo->save(new Account(
                id: $equityAccount->id,
                businessId: $equityAccount->businessId,
                code: $equityAccount->code,
                name: $equityAccount->name,
                type: $equityAccount->type,
                category: $equityAccount->category,
                description: $equityAccount->description,
                isSystem: $equityAccount->isSystem,
                isActive: $equityAccount->isActive,
                openingBalance: $equityAccount->openingBalance,
                currentBalance: $equityAccount->currentBalance + $amount,
                createdAt: $equityAccount->createdAt,
                updatedAt: null,
            ));

            return $entry->toArray();
        });
    }

    public function recordWithdrawal(
        int $businessId,
        int $accountId,
        float $amount,
        string $description,
        ?string $reference,
        string $date,
        int $createdBy
    ): array {
        return DB::transaction(function () use ($businessId, $accountId, $amount, $description, $reference, $date, $createdBy) {
            $drawingsAccount = $this->accountRepo->findByCode($businessId, '3200');

            if (!$drawingsAccount) {
                throw new \RuntimeException('No drawings account found for withdrawal');
            }

            $entryNumber = $this->generateEntryNumber($businessId);

            $entry = $this->journalEntryRepo->save(new JournalEntry(
                id: null,
                businessId: $businessId,
                entryNumber: $entryNumber,
                entryDate: new \DateTimeImmutable($date),
                description: $description,
                reference: $reference,
                isPosted: true,
                createdBy: $createdBy,
                createdAt: null,
                updatedAt: null,
            ));

            $this->journalLineRepo->save(new JournalLine(
                id: null,
                journalEntryId: $entry->id,
                accountId: $drawingsAccount->id,
                debitAmount: $amount,
                creditAmount: 0,
                description: $description,
                createdAt: null,
                updatedAt: null,
            ));

            $this->journalLineRepo->save(new JournalLine(
                id: null,
                journalEntryId: $entry->id,
                accountId: $accountId,
                debitAmount: 0,
                creditAmount: $amount,
                description: $description,
                createdAt: null,
                updatedAt: null,
            ));

            $cashAccount = $this->accountRepo->findById($accountId);
            if (!$cashAccount) {
                throw new \RuntimeException('Cash account not found');
            }

            $this->accountRepo->save(new Account(
                id: $cashAccount->id,
                businessId: $cashAccount->businessId,
                code: $cashAccount->code,
                name: $cashAccount->name,
                type: $cashAccount->type,
                category: $cashAccount->category,
                description: $cashAccount->description,
                isSystem: $cashAccount->isSystem,
                isActive: $cashAccount->isActive,
                openingBalance: $cashAccount->openingBalance,
                currentBalance: $cashAccount->currentBalance - $amount,
                createdAt: $cashAccount->createdAt,
                updatedAt: null,
            ));

            $this->accountRepo->save(new Account(
                id: $drawingsAccount->id,
                businessId: $drawingsAccount->businessId,
                code: $drawingsAccount->code,
                name: $drawingsAccount->name,
                type: $drawingsAccount->type,
                category: $drawingsAccount->category,
                description: $drawingsAccount->description,
                isSystem: $drawingsAccount->isSystem,
                isActive: $drawingsAccount->isActive,
                openingBalance: $drawingsAccount->openingBalance,
                currentBalance: $drawingsAccount->currentBalance + $amount,
                createdAt: $drawingsAccount->createdAt,
                updatedAt: null,
            ));

            return $entry->toArray();
        });
    }

    public function recordTransfer(
        int $businessId,
        int $fromAccountId,
        int $toAccountId,
        float $amount,
        string $description,
        string $date,
        int $createdBy
    ): array {
        return DB::transaction(function () use ($businessId, $fromAccountId, $toAccountId, $amount, $description, $date, $createdBy) {
            $entryNumber = $this->generateEntryNumber($businessId);

            $entry = $this->journalEntryRepo->save(new JournalEntry(
                id: null,
                businessId: $businessId,
                entryNumber: $entryNumber,
                entryDate: new \DateTimeImmutable($date),
                description: $description,
                reference: 'TRANSFER',
                isPosted: true,
                createdBy: $createdBy,
                createdAt: null,
                updatedAt: null,
            ));

            $this->journalLineRepo->save(new JournalLine(
                id: null,
                journalEntryId: $entry->id,
                accountId: $toAccountId,
                debitAmount: $amount,
                creditAmount: 0,
                description: $description,
                createdAt: null,
                updatedAt: null,
            ));

            $this->journalLineRepo->save(new JournalLine(
                id: null,
                journalEntryId: $entry->id,
                accountId: $fromAccountId,
                debitAmount: 0,
                creditAmount: $amount,
                description: $description,
                createdAt: null,
                updatedAt: null,
            ));

            $fromAccount = $this->accountRepo->findById($fromAccountId);
            if (!$fromAccount) {
                throw new \RuntimeException('Source account not found');
            }

            $toAccount = $this->accountRepo->findById($toAccountId);
            if (!$toAccount) {
                throw new \RuntimeException('Destination account not found');
            }

            $this->accountRepo->save(new Account(
                id: $fromAccount->id,
                businessId: $fromAccount->businessId,
                code: $fromAccount->code,
                name: $fromAccount->name,
                type: $fromAccount->type,
                category: $fromAccount->category,
                description: $fromAccount->description,
                isSystem: $fromAccount->isSystem,
                isActive: $fromAccount->isActive,
                openingBalance: $fromAccount->openingBalance,
                currentBalance: $fromAccount->currentBalance - $amount,
                createdAt: $fromAccount->createdAt,
                updatedAt: null,
            ));

            $this->accountRepo->save(new Account(
                id: $toAccount->id,
                businessId: $toAccount->businessId,
                code: $toAccount->code,
                name: $toAccount->name,
                type: $toAccount->type,
                category: $toAccount->category,
                description: $toAccount->description,
                isSystem: $toAccount->isSystem,
                isActive: $toAccount->isActive,
                openingBalance: $toAccount->openingBalance,
                currentBalance: $toAccount->currentBalance + $amount,
                createdAt: $toAccount->createdAt,
                updatedAt: null,
            ));

            return $entry->toArray();
        });
    }

    private function generateEntryNumber(int $businessId): string
    {
        $lastEntry = DB::table('growfinance_journal_entries')
            ->where('business_id', $businessId)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastEntry ? ((int) substr($lastEntry->entry_number, 3)) + 1 : 1;

        return 'JE-' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
