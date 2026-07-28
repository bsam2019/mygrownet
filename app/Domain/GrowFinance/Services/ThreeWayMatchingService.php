<?php
declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Repositories\InvoiceRepositoryInterface;
use App\Domain\GrowFinance\Repositories\ExpenseRepositoryInterface;
use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use DateTimeImmutable;

class ThreeWayMatchingService
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepo,
        private ExpenseRepositoryInterface $expenseRepo,
        private AccountRepositoryInterface $accountRepo,
    ) {}

    /**
     * Run three-way matching: PO → Receipt → Invoice
     * Matches expenses (receipts/GRNs) to invoices by reference, amount, and vendor.
     */
    public function runMatching(int $businessId, ?DateTimeImmutable $from = null, ?DateTimeImmutable $to = null): array
    {
        $from = $from ?? new DateTimeImmutable('-90 days');
        $to = $to ?? new DateTimeImmutable('now');

        $invoices = $this->invoiceRepo->findByDateRange($businessId, $from, $to);
        $expenses = $this->expenseRepo->findByDateRange($businessId, $from, $to);

        $matches = [];
        $unmatchedInvoices = [];
        $unmatchedExpenses = [];
        $partialMatches = [];

        // Build expense lookup by reference and amount
        $expenseIndex = [];
        foreach ($expenses as $expense) {
            $key = $expense->vendorId . '|' . round($expense->amount, 2);
            $expenseIndex[$key][] = $expense;
        }

        foreach ($invoices as $invoice) {
            $key = $invoice->vendorId . '|' . round($invoice->total, 2);
            $matched = false;

            // Direct match by reference
            if (!empty($invoice->purchaseOrderRef)) {
                foreach ($expenses as $expense) {
                    if (
                        $expense->reference === $invoice->purchaseOrderRef
                        && abs($expense->amount - $invoice->total) < 0.01
                    ) {
                        $matches[] = [
                            'type' => 'exact',
                            'score' => 100,
                            'invoice_id' => $invoice->id,
                            'invoice_number' => $invoice->invoiceNumber,
                            'expense_id' => $expense->id,
                            'expense_reference' => $expense->reference,
                            'amount' => $invoice->total,
                            'vendor_id' => $invoice->vendorId,
                            'status' => 'matched',
                        ];
                        $matched = true;
                        break;
                    }
                }
            }

            // Amount + vendor match (if not already matched)
            if (!$matched && isset($expenseIndex[$key])) {
                foreach ($expenseIndex[$key] as $expense) {
                    $matches[] = [
                        'type' => 'amount_vendor',
                        'score' => 85,
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoiceNumber,
                        'expense_id' => $expense->id,
                        'expense_reference' => $expense->reference,
                        'amount' => $invoice->total,
                        'vendor_id' => $invoice->vendorId,
                        'status' => 'matched',
                    ];
                    $matched = true;
                    break;
                }
            }

            // Partial match (within 10% tolerance)
            if (!$matched) {
                foreach ($expenses as $expense) {
                    if ($expense->vendorId === $invoice->vendorId) {
                        $diff = abs($expense->amount - $invoice->total);
                        $tolerance = max($invoice->total, $expense->amount) * 0.1;
                        if ($diff <= $tolerance) {
                            $partialMatches[] = [
                                'type' => 'partial',
                                'score' => round((1 - $diff / max($invoice->total, 1)) * 100, 0),
                                'invoice_id' => $invoice->id,
                                'invoice_number' => $invoice->invoiceNumber,
                                'expense_id' => $expense->id,
                                'expense_reference' => $expense->reference,
                                'amount_invoice' => $invoice->total,
                                'amount_expense' => $expense->amount,
                                'difference' => round($invoice->total - $expense->amount, 2),
                                'vendor_id' => $invoice->vendorId,
                                'status' => 'partial',
                            ];
                        }
                    }
                }
            }

            if (!$matched) {
                $unmatchedInvoices[] = [
                    'id' => $invoice->id,
                    'number' => $invoice->invoiceNumber,
                    'amount' => $invoice->total,
                    'vendor_id' => $invoice->vendorId,
                ];
            }
        }

        // Find expenses not matched to any invoice
        $matchedExpenseIds = array_unique(array_column($matches, 'expense_id'));
        foreach ($expenses as $expense) {
            if (!in_array($expense->id, $matchedExpenseIds)) {
                $unmatchedExpenses[] = [
                    'id' => $expense->id,
                    'reference' => $expense->reference,
                    'amount' => $expense->amount,
                    'vendor_id' => $expense->vendorId,
                ];
            }
        }

        return [
            'exact_matches' => count(array_filter($matches, fn($m) => $m['type'] === 'exact')),
            'amount_matches' => count(array_filter($matches, fn($m) => $m['type'] === 'amount_vendor')),
            'partial_matches' => $partialMatches,
            'unmatched_invoices' => $unmatchedInvoices,
            'unmatched_expenses' => $unmatchedExpenses,
            'matches' => $matches,
            'summary' => [
                'total_invoices' => count($invoices),
                'total_expenses' => count($expenses),
                'matched' => count($matches),
                'partial' => count($partialMatches),
                'unmatched_invoices' => count($unmatchedInvoices),
                'unmatched_expenses' => count($unmatchedExpenses),
            ],
        ];
    }

    /**
     * Confirm a match (for partial matches requiring user approval).
     */
    public function confirmMatch(int $invoiceId, int $expenseId): array
    {
        // In a full implementation, this would create a link record
        return ['confirmed' => true, 'invoice_id' => $invoiceId, 'expense_id' => $expenseId];
    }
}
