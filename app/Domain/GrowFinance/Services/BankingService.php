<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Infrastructure\Persistence\Eloquent\GrowFinanceAccountModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceJournalEntryModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceJournalLineModel;
use Illuminate\Support\Facades\DB;

class BankingService
{
    public function recordDeposit(
        int $businessId,
        int $accountId,
        float $amount,
        string $description,
        ?string $reference,
        string $date,
        int $createdBy
    ): GrowFinanceJournalEntryModel {
        return DB::transaction(function () use ($businessId, $accountId, $amount, $description, $reference, $date, $createdBy) {
            // Get the equity/income account for deposits (Owner's Capital or Other Income)
            $equityAccount = GrowFinanceAccountModel::forBusiness($businessId)
                ->where('code', '4200') // Other Income
                ->first();

            if (!$equityAccount) {
                $equityAccount = GrowFinanceAccountModel::forBusiness($businessId)
                    ->where('code', '3000') // Owner's Capital
                    ->first();
            }

            if (!$equityAccount) {
                throw new \RuntimeException('No equity or income account found for deposit');
            }

            $entryNumber = $this->generateEntryNumber($businessId);

            $entry = GrowFinanceJournalEntryModel::create([
                'business_id' => $businessId,
                'entry_number' => $entryNumber,
                'entry_date' => $date,
                'description' => $description,
                'reference' => $reference,
                'is_posted' => true,
                'created_by' => $createdBy,
            ]);

            // Debit cash account (increase)
            GrowFinanceJournalLineModel::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $accountId,
                'debit_amount' => $amount,
                'credit_amount' => 0,
                'description' => $description,
            ]);

            // Credit equity/income account
            GrowFinanceJournalLineModel::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $equityAccount->id,
                'debit_amount' => 0,
                'credit_amount' => $amount,
                'description' => $description,
            ]);

            // Update account balances
            $cashAccount = GrowFinanceAccountModel::findOrFail($accountId);
            $cashAccount->current_balance += $amount;
            $cashAccount->save();

            $equityAccount->current_balance += $amount;
            $equityAccount->save();

            return $entry->load('lines.account');
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
    ): GrowFinanceJournalEntryModel {
        return DB::transaction(function () use ($businessId, $accountId, $amount, $description, $reference, $date, $createdBy) {
            // Get the drawings account
            $drawingsAccount = GrowFinanceAccountModel::forBusiness($businessId)
                ->where('code', '3200') // Owner's Drawings
                ->first();

            if (!$drawingsAccount) {
                throw new \RuntimeException('No drawings account found for withdrawal');
            }

            $entryNumber = $this->generateEntryNumber($businessId);

            $entry = GrowFinanceJournalEntryModel::create([
                'business_id' => $businessId,
                'entry_number' => $entryNumber,
                'entry_date' => $date,
                'description' => $description,
                'reference' => $reference,
                'is_posted' => true,
                'created_by' => $createdBy,
            ]);

            // Debit drawings account
            GrowFinanceJournalLineModel::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $drawingsAccount->id,
                'debit_amount' => $amount,
                'credit_amount' => 0,
                'description' => $description,
            ]);

            // Credit cash account (decrease)
            GrowFinanceJournalLineModel::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $accountId,
                'debit_amount' => 0,
                'credit_amount' => $amount,
                'description' => $description,
            ]);

            // Update account balances
            $cashAccount = GrowFinanceAccountModel::findOrFail($accountId);
            $cashAccount->current_balance -= $amount;
            $cashAccount->save();

            $drawingsAccount->current_balance += $amount; // Drawings is contra-equity
            $drawingsAccount->save();

            return $entry->load('lines.account');
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
    ): GrowFinanceJournalEntryModel {
        return DB::transaction(function () use ($businessId, $fromAccountId, $toAccountId, $amount, $description, $date, $createdBy) {
            $entryNumber = $this->generateEntryNumber($businessId);

            $entry = GrowFinanceJournalEntryModel::create([
                'business_id' => $businessId,
                'entry_number' => $entryNumber,
                'entry_date' => $date,
                'description' => $description,
                'reference' => 'TRANSFER',
                'is_posted' => true,
                'created_by' => $createdBy,
            ]);

            // Debit destination account (increase)
            GrowFinanceJournalLineModel::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $toAccountId,
                'debit_amount' => $amount,
                'credit_amount' => 0,
                'description' => $description,
            ]);

            // Credit source account (decrease)
            GrowFinanceJournalLineModel::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $fromAccountId,
                'debit_amount' => 0,
                'credit_amount' => $amount,
                'description' => $description,
            ]);

            // Update account balances
            $fromAccount = GrowFinanceAccountModel::findOrFail($fromAccountId);
            $fromAccount->current_balance -= $amount;
            $fromAccount->save();

            $toAccount = GrowFinanceAccountModel::findOrFail($toAccountId);
            $toAccount->current_balance += $amount;
            $toAccount->save();

            return $entry->load('lines.account');
        });
    }

    private function generateEntryNumber(int $businessId): string
    {
        $lastEntry = GrowFinanceJournalEntryModel::forBusiness($businessId)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastEntry ? ((int) substr($lastEntry->entry_number, 3)) + 1 : 1;

        return 'JE-' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
