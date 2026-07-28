<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use Illuminate\Support\Facades\Log;

class AutoJournalingService
{
    public function __construct(
        private AccountRepositoryInterface $accountRepo,
        private AccountingService $accountingService,
    ) {}

    public function onSaleCompleted(array $payload): array
    {
        try {
            $businessId = (int) ($payload['business_id'] ?? 0);
            $totalAmount = (float) ($payload['total'] ?? $payload['total_amount'] ?? 0);
            $items = $payload['items'] ?? [];
            $saleId = $payload['sale_id'] ?? 'unknown';
            $receiptNumber = $payload['receipt_number'] ?? $saleId;

            $arAccount = $this->accountRepo->findByCode($businessId, '1200');
            $revenueAccount = $this->accountRepo->findByCode($businessId, '4100');
            $cogsAccount = $this->accountRepo->findByCode($businessId, '5100');
            $inventoryAccount = $this->accountRepo->findByCode($businessId, '1300');

            if (!$arAccount || !$revenueAccount) {
                throw new \RuntimeException("Required accounts (1200, 4100) not found for business {$businessId}");
            }

            $lines = [
                ['account_id' => $arAccount->id, 'debit_amount' => $totalAmount, 'credit_amount' => 0],
                ['account_id' => $revenueAccount->id, 'debit_amount' => 0, 'credit_amount' => $totalAmount],
            ];

            $totalCost = 0;
            if (!empty($items) && $cogsAccount && $inventoryAccount) {
                foreach ($items as $item) {
                    $qty = (float) ($item['quantity'] ?? 1);
                    $cost = (float) ($item['cost'] ?? 0);
                    $totalCost += $cost * $qty;
                }

                if ($totalCost > 0) {
                    $lines[] = ['account_id' => $cogsAccount->id, 'debit_amount' => $totalCost, 'credit_amount' => 0];
                    $lines[] = ['account_id' => $inventoryAccount->id, 'debit_amount' => 0, 'credit_amount' => $totalCost];
                }
            }

            return $this->accountingService->createJournalEntry(
                businessId: $businessId,
                description: "Auto-journal: StockFlow sale #{$receiptNumber} completed",
                lines: $lines,
                reference: "SF-SALE-{$saleId}",
            );
        } catch (\Throwable $e) {
            Log::error("AutoJournalingService::onSaleCompleted failed", [
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function onPurchaseReceived(array $payload): array
    {
        try {
            $businessId = (int) ($payload['business_id'] ?? 0);
            $items = $payload['items'] ?? [];
            $purchaseId = $payload['purchase_order_id'] ?? 'unknown';

            $inventoryAccount = $this->accountRepo->findByCode($businessId, '1300');
            $apAccount = $this->accountRepo->findByCode($businessId, '2100');

            if (!$inventoryAccount || !$apAccount) {
                throw new \RuntimeException("Required accounts (1300, 2100) not found for business {$businessId}");
            }

            $totalCost = 0;
            foreach ($items as $item) {
                $qty = (float) ($item['quantity'] ?? 1);
                $cost = (float) ($item['cost'] ?? 0);
                $totalCost += $cost * $qty;
            }

            if ($totalCost <= 0) {
                throw new \RuntimeException("Purchase {$purchaseId} has zero total cost");
            }

            $lines = [
                ['account_id' => $inventoryAccount->id, 'debit_amount' => $totalCost, 'credit_amount' => 0],
                ['account_id' => $apAccount->id, 'debit_amount' => 0, 'credit_amount' => $totalCost],
            ];

            return $this->accountingService->createJournalEntry(
                businessId: $businessId,
                description: "Auto-journal: StockFlow purchase #{$purchaseId} received",
                lines: $lines,
                reference: "SF-PO-{$purchaseId}",
            );
        } catch (\Throwable $e) {
            Log::error("AutoJournalingService::onPurchaseReceived failed", [
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function onStockAdjusted(array $payload): array
    {
        try {
            $businessId = (int) ($payload['business_id'] ?? 0);
            $qtyChange = (float) ($payload['quantity_change'] ?? 0);
            $unitCost = (float) ($payload['unit_cost'] ?? 0);
            $itemId = $payload['item_id'] ?? 'unknown';
            $occurredAt = $payload['occurred_at'] ?? 'unknown';

            $inventoryAccount = $this->accountRepo->findByCode($businessId, '1300');
            $retainedEarnings = $this->accountRepo->findByCode($businessId, '3100');

            if (!$inventoryAccount || !$retainedEarnings) {
                throw new \RuntimeException("Required accounts (1300, 3100) not found for business {$businessId}");
            }

            $amount = abs($qtyChange) * $unitCost;

            if ($qtyChange > 0) {
                $lines = [
                    ['account_id' => $inventoryAccount->id, 'debit_amount' => $amount, 'credit_amount' => 0],
                    ['account_id' => $retainedEarnings->id, 'debit_amount' => 0, 'credit_amount' => $amount],
                ];
            } else {
                $lines = [
                    ['account_id' => $retainedEarnings->id, 'debit_amount' => $amount, 'credit_amount' => 0],
                    ['account_id' => $inventoryAccount->id, 'debit_amount' => 0, 'credit_amount' => $amount],
                ];
            }

            return $this->accountingService->createJournalEntry(
                businessId: $businessId,
                description: "Auto-journal: StockFlow stock adjustment item #{$itemId}",
                lines: $lines,
                reference: "SF-ADJ-{$itemId}-{$occurredAt}",
            );
        } catch (\Throwable $e) {
            Log::error("AutoJournalingService::onStockAdjusted failed", [
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function onBmsInvoiceCreated(array $payload): array
    {
        try {
            $businessId = (int) ($payload['company_id'] ?? $payload['business_id'] ?? 0);
            $invoiceId = $payload['invoice_id'] ?? 'unknown';
            $totalAmount = (float) ($payload['total_amount'] ?? 0);

            $arAccount = $this->accountRepo->findByCode($businessId, '1200');
            $revenueAccount = $this->accountRepo->findByCode($businessId, '4100');

            if (!$arAccount || !$revenueAccount) {
                throw new \RuntimeException("Required accounts (1200, 4100) not found for business {$businessId}");
            }

            if ($totalAmount <= 0) {
                $lines = [
                    ['account_id' => $arAccount->id, 'debit_amount' => 0, 'credit_amount' => 0],
                ];
            } else {
                $lines = [
                    ['account_id' => $arAccount->id, 'debit_amount' => $totalAmount, 'credit_amount' => 0],
                    ['account_id' => $revenueAccount->id, 'debit_amount' => 0, 'credit_amount' => $totalAmount],
                ];
            }

            return $this->accountingService->createJournalEntry(
                businessId: $businessId,
                description: "Auto-journal: BMS invoice #{$invoiceId} created",
                lines: $lines,
                reference: "BMS-INV-{$invoiceId}",
            );
        } catch (\Throwable $e) {
            Log::error("AutoJournalingService::onBmsInvoiceCreated failed", [
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function onBmsInvoicePaid(array $payload): array
    {
        try {
            $businessId = (int) ($payload['company_id'] ?? $payload['business_id'] ?? 0);
            $invoiceId = $payload['invoice_id'] ?? 'unknown';
            $amountPaid = (float) ($payload['amount_paid'] ?? 0);

            $cashAccount = $this->accountRepo->findByCode($businessId, '1110');
            $arAccount = $this->accountRepo->findByCode($businessId, '1200');

            if (!$cashAccount || !$arAccount) {
                throw new \RuntimeException("Required accounts (1110, 1200) not found for business {$businessId}");
            }

            $lines = [
                ['account_id' => $cashAccount->id, 'debit_amount' => $amountPaid, 'credit_amount' => 0],
                ['account_id' => $arAccount->id, 'debit_amount' => 0, 'credit_amount' => $amountPaid],
            ];

            return $this->accountingService->createJournalEntry(
                businessId: $businessId,
                description: "Auto-journal: BMS invoice #{$invoiceId} paid",
                lines: $lines,
                reference: "BMS-PAY-{$invoiceId}",
            );
        } catch (\Throwable $e) {
            Log::error("AutoJournalingService::onBmsInvoicePaid failed", [
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function onBmsExpenseRecorded(array $payload): array
    {
        try {
            $businessId = (int) ($payload['company_id'] ?? $payload['business_id'] ?? 0);
            $expenseId = $payload['expense_id'] ?? 'unknown';
            $amount = (float) ($payload['amount'] ?? 0);
            $category = $payload['category'] ?? 'other';

            $expenseAccount = $this->accountRepo->findByCode($businessId, '5300');
            $description = $payload['description'] ?? 'Miscellaneous Expenses';
            $categoryMap = [
                'payroll' => '5210',
                'rent' => '5220',
                'utilities' => '5230',
                'transport' => '5240',
                'supplies' => '5250',
                'marketing' => '5260',
                'bank' => '5270',
                'depreciation' => '5280',
                'professional' => '5290',
            ];
            $expenseCode = $categoryMap[strtolower($category)] ?? '5300';
            $expenseAccount = $this->accountRepo->findByCode($businessId, $expenseCode);
            $cashAccount = $this->accountRepo->findByCode($businessId, '1110');

            if (!$expenseAccount) {
                throw new \RuntimeException("Expense account ({$expenseCode}) not found for business {$businessId}");
            }

            $lines = [
                ['account_id' => $expenseAccount->id, 'debit_amount' => $amount, 'credit_amount' => 0],
            ];

            if ($cashAccount) {
                $lines[] = ['account_id' => $cashAccount->id, 'debit_amount' => 0, 'credit_amount' => $amount];
            } else {
                $apAccount = $this->accountRepo->findByCode($businessId, '2100');
                if ($apAccount) {
                    $lines[] = ['account_id' => $apAccount->id, 'debit_amount' => 0, 'credit_amount' => $amount];
                } else {
                    throw new \RuntimeException("No cash or AP account found for business {$businessId}");
                }
            }

            return $this->accountingService->createJournalEntry(
                businessId: $businessId,
                description: "Auto-journal: BMS expense #{$expenseId} - {$description}",
                lines: $lines,
                reference: "BMS-EXP-{$expenseId}",
            );
        } catch (\Throwable $e) {
            Log::error("AutoJournalingService::onBmsExpenseRecorded failed", [
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function onPaymentSettled(int $organizationId, float $settledAmount, float $fee, string $currency): array
    {
        try {
            $bankAccount = $this->accountRepo->findByCode($organizationId, '1120');
            $bankChargesAccount = $this->accountRepo->findByCode($organizationId, '5270');
            $arAccount = $this->accountRepo->findByCode($organizationId, '1200');

            if (!$bankAccount || !$bankChargesAccount || !$arAccount) {
                throw new \RuntimeException("Required accounts (1120, 5270, 1200) not found for organization {$organizationId}");
            }

            $lines = [
                ['account_id' => $bankAccount->id, 'debit_amount' => $settledAmount, 'credit_amount' => 0],
                ['account_id' => $bankChargesAccount->id, 'debit_amount' => $fee, 'credit_amount' => 0],
                ['account_id' => $arAccount->id, 'debit_amount' => 0, 'credit_amount' => $settledAmount + $fee],
            ];

            return $this->accountingService->createJournalEntry(
                businessId: $organizationId,
                description: "Auto-journal: Payment settlement of {$settledAmount} {$currency}",
                lines: $lines,
                reference: "PMT-SETTLE-{$organizationId}",
                currencyCode: $currency,
            );
        } catch (\Throwable $e) {
            Log::error("AutoJournalingService::onPaymentSettled failed", [
                'organization_id' => $organizationId,
                'settled_amount' => $settledAmount,
                'fee' => $fee,
                'currency' => $currency,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
