<?php

declare(strict_types=1);

namespace App\Domain\BMS\Services\GrowFinanceSync;

use App\Domain\GrowFinance\Services\AccountingService;
use App\Infrastructure\Persistence\Eloquent\BMS\InvoiceModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceSyncHandler
{
    public function __construct(
        private AccountingService $accountingService,
        private AccountMappingService $mappingService,
        private SyncStatusService $statusService
    ) {}

    /**
     * Sync invoice to GrowFinance
     */
    public function sync(InvoiceModel $invoice): void
    {
        // 1. Idempotency check
        if ($this->statusService->isSynced($invoice)) {
            Log::info("Invoice #{$invoice->id} already synced, skipping");
            return;
        }

        try {
            DB::transaction(function () use ($invoice) {
                // 2. Get account mappings
                $salesAccount = $this->mappingService->getAccount(
                    $invoice->company_id,
                    'invoice',
                    'sales'
                );

                $vatAccount = $this->mappingService->getAccount(
                    $invoice->company_id,
                    'invoice',
                    'vat'
                );

                $receivableAccount = $this->mappingService->getAccount(
                    $invoice->company_id,
                    'invoice',
                    'receivable'
                );

                if (!$salesAccount || !$vatAccount || !$receivableAccount) {
                    throw new \Exception('Account mappings not configured');
                }

                // 3. Build journal entry lines
                $lines = [];

                // Debit: Accounts Receivable (or Cash if paid)
                if ($invoice->status === 'paid' && $invoice->payment_method) {
                    $cashAccount = $this->mappingService->getCashAccount(
                        $invoice->company_id,
                        $invoice->payment_method
                    );

                    if ($cashAccount) {
                        $lines[] = [
                            'account_id' => $cashAccount->id,
                            'debit_amount' => $invoice->total_amount,
                            'credit_amount' => 0,
                            'description' => 'Payment received',
                        ];
                    } else {
                        // Fall back to receivable if cash account not found
                        $lines[] = [
                            'account_id' => $receivableAccount->id,
                            'debit_amount' => $invoice->total_amount,
                            'credit_amount' => 0,
                            'description' => 'Invoice issued',
                        ];
                    }
                } else {
                    $lines[] = [
                        'account_id' => $receivableAccount->id,
                        'debit_amount' => $invoice->total_amount,
                        'credit_amount' => 0,
                        'description' => 'Invoice issued',
                    ];
                }

                // Credit: Sales Revenue
                $lines[] = [
                    'account_id' => $salesAccount->id,
                    'debit_amount' => 0,
                    'credit_amount' => $invoice->net_amount ?? $invoice->total_amount,
                    'description' => 'Sales revenue',
                ];

                // Credit: VAT Payable (if applicable)
                if (isset($invoice->vat_amount) && $invoice->vat_amount > 0) {
                    $lines[] = [
                        'account_id' => $vatAccount->id,
                        'debit_amount' => 0,
                        'credit_amount' => $invoice->vat_amount,
                        'description' => 'VAT on sales',
                    ];
                }

                // 4. Create journal entry in GrowFinance
                $journalEntryData = $this->accountingService->createJournalEntry(
                    businessId: $invoice->company_id,
                    description: "CMS Invoice #{$invoice->invoice_number}" . 
                                ($invoice->customer ? " - {$invoice->customer->name}" : ''),
                    lines: $lines,
                    reference: "CMS-INV-{$invoice->id}",
                    createdBy: $invoice->created_by ?? null
                );

                // 5. Post the entry (updates account balances)
                $this->accountingService->postJournalEntry($journalEntryData['id']);

                // 6. Log success
                $this->statusService->logSuccess($invoice, $journalEntryData['id']);

                Log::info("Successfully synced invoice #{$invoice->id} to GrowFinance", [
                    'invoice_id' => $invoice->id,
                    'journal_entry_id' => $journalEntryData['id'],
                ]);
            });
        } catch (\Exception $e) {
            // Log failure
            $this->statusService->logFailure($invoice, $e->getMessage());

            Log::error("Failed to sync invoice #{$invoice->id} to GrowFinance", [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw for job retry logic
            throw $e;
        }
    }

    /**
     * Sync multiple invoices in bulk
     */
    public function bulkSync(array $invoiceIds): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];

        foreach ($invoiceIds as $invoiceId) {
            $invoice = InvoiceModel::find($invoiceId);

            if (!$invoice) {
                $results['skipped']++;
                continue;
            }

            try {
                $this->sync($invoice);
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
            }
        }

        return $results;
    }
}
