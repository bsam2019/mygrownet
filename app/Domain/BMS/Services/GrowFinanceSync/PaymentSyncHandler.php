<?php

declare(strict_types=1);

namespace App\Domain\BMS\Services\GrowFinanceSync;

use App\Domain\GrowFinance\Services\AccountingService;
use App\Infrastructure\Persistence\Eloquent\BMS\PaymentModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentSyncHandler
{
    public function __construct(
        private AccountingService $accountingService,
        private AccountMappingService $mappingService,
        private SyncStatusService $statusService
    ) {}

    /**
     * Sync payment to GrowFinance
     */
    public function sync(PaymentModel $payment): void
    {
        // 1. Idempotency check
        if ($this->statusService->isSynced($payment)) {
            Log::info("Payment #{$payment->id} already synced, skipping");
            return;
        }

        try {
            DB::transaction(function () use ($payment) {
                // 2. Get account mappings
                $cashAccount = $this->mappingService->getCashAccount(
                    $payment->company_id,
                    $payment->payment_method ?? 'bank_transfer'
                );

                $receivableAccount = $this->mappingService->getAccount(
                    $payment->company_id,
                    'invoice',
                    'receivable'
                );

                if (!$cashAccount || !$receivableAccount) {
                    throw new \Exception('Account mappings not configured');
                }

                // 3. Build journal entry lines
                $lines = [];

                // Debit: Cash/Bank Account
                $lines[] = [
                    'account_id' => $cashAccount->id,
                    'debit_amount' => $payment->amount,
                    'credit_amount' => 0,
                    'description' => 'Payment received',
                ];

                // Credit: Accounts Receivable
                $lines[] = [
                    'account_id' => $receivableAccount->id,
                    'debit_amount' => 0,
                    'credit_amount' => $payment->amount,
                    'description' => 'Payment applied to invoice',
                ];

                // 4. Create journal entry in GrowFinance
                $description = "CMS Payment #{$payment->id}";
                if ($payment->invoice) {
                    $description .= " for Invoice #{$payment->invoice->invoice_number}";
                }

                $journalEntryData = $this->accountingService->createJournalEntry(
                    businessId: $payment->company_id,
                    description: $description,
                    lines: $lines,
                    reference: "CMS-PAY-{$payment->id}",
                    createdBy: $payment->created_by ?? null
                );

                // 5. Post the entry
                $this->accountingService->postJournalEntry($journalEntryData['id']);

                // 6. Log success
                $this->statusService->logSuccess($payment, $journalEntryData['id']);

                Log::info("Successfully synced payment #{$payment->id} to GrowFinance", [
                    'payment_id' => $payment->id,
                    'journal_entry_id' => $journalEntryData['id'],
                ]);
            });
        } catch (\Exception $e) {
            // Log failure
            $this->statusService->logFailure($payment, $e->getMessage());

            Log::error("Failed to sync payment #{$payment->id} to GrowFinance", [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Sync multiple payments in bulk
     */
    public function bulkSync(array $paymentIds): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];

        foreach ($paymentIds as $paymentId) {
            $payment = PaymentModel::find($paymentId);

            if (!$payment) {
                $results['skipped']++;
                continue;
            }

            try {
                $this->sync($payment);
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
            }
        }

        return $results;
    }
}
