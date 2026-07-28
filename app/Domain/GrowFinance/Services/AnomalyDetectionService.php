<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalLineRepositoryInterface;
use App\Domain\GrowFinance\Repositories\InvoiceRepositoryInterface;
use App\Domain\GrowFinance\Repositories\ExpenseRepositoryInterface;
use App\Domain\GrowFinance\Repositories\PaymentRepositoryInterface;
use DateTimeImmutable;

class AnomalyDetectionService
{
    public function __construct(
        private JournalEntryRepositoryInterface $journalEntryRepo,
        private JournalLineRepositoryInterface $journalLineRepo,
        private AccountRepositoryInterface $accountRepo,
        private InvoiceRepositoryInterface $invoiceRepo,
        private ExpenseRepositoryInterface $expenseRepo,
        private PaymentRepositoryInterface $paymentRepo,
    ) {}

    /**
     * Run all anomaly checks and return categorized results.
     */
    public function runAll(int $businessId, ?DateTimeImmutable $from = null, ?DateTimeImmutable $to = null): array
    {
        $from = $from ?? new DateTimeImmutable('-90 days');
        $to = $to ?? new DateTimeImmutable('now');

        return [
            'duplicate_invoices' => $this->detectDuplicateInvoices($businessId, $from, $to),
            'unusual_journal_patterns' => $this->detectUnusualJournalPatterns($businessId, $from, $to),
            'out_of_range_amounts' => $this->detectOutOfRangeAmounts($businessId, $from, $to),
            'unusual_payment_patterns' => $this->detectUnusualPaymentPatterns($businessId, $from, $to),
            'missing_reference_journals' => $this->detectMissingReferences($businessId, $from, $to),
            'summary' => [],
        ];
    }

    /**
     * Detect potential duplicate invoices by matching amount + customer + similar date.
     */
    public function detectDuplicateInvoices(int $businessId, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $invoices = $this->invoiceRepo->findByDateRange($businessId, $from, $to);
        $duplicates = [];
        $seen = [];

        foreach ($invoices as $inv) {
            $key = $inv->customerId . '|' . round($inv->totalAmount, 2);
            if (isset($seen[$key])) {
                $duplicates[] = [
                    'type' => 'duplicate_invoice',
                    'severity' => 'high',
                    'message' => "Potential duplicate invoice for customer #{$inv->customerId}: {$inv->invoiceNumber} ({$inv->totalAmount}) matches #{$seen[$key]['invoice_number']}",
                    'reference_id' => $inv->id,
                    'reference_type' => 'invoice',
                    'reference_number' => $inv->invoiceNumber,
                    'amount' => $inv->totalAmount,
                    'date' => $inv->invoiceDate?->format('Y-m-d'),
                    'matched_with' => $seen[$key]['invoice_number'],
                ];
            } else {
                $seen[$key] = [
                    'invoice_number' => $inv->invoiceNumber,
                    'customer_id' => $inv->customerId,
                    'total' => $inv->totalAmount,
                ];
            }
        }

        return $duplicates;
    }

    /**
     * Detect unusual journal patterns - round numbers, weekends, off-hours, infrequent accounts.
     */
    public function detectUnusualJournalPatterns(int $businessId, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $entries = $this->journalEntryRepo->findByDateRange($businessId, $from, $to);
        $anomalies = [];

        foreach ($entries as $entry) {
            if ($entry->status->value !== 'posted') {
                continue;
            }

            $lines = $this->journalLineRepo->findByJournalEntry($entry->id);
            $issues = [];

            foreach ($lines as $line) {
                $amount = max($line->debitAmount, $line->creditAmount);
                if ($amount >= 1000 && abs($amount - round($amount, -2)) < 0.01) {
                    $issues[] = "Round amount: {$amount}";
                }
            }

            $entryDate = $entry->date;
            if ($entryDate !== null) {
                $dayOfWeek = (int) $entryDate->format('N');
                if ($dayOfWeek >= 6) {
                    $issues[] = 'Weekend posting';
                }

                $hour = (int) $entryDate->format('G');
                if ($hour < 6 || $hour >= 22) {
                    $issues[] = "Off-hours posting ({$hour}:00)";
                }
            }

            $accounts = $this->accountRepo->findActive($businessId);
            foreach ($lines as $line) {
                $account = current(array_filter($accounts, fn($a) => $a->id === $line->accountId));
                if ($account) {
                    $activity = $this->journalEntryRepo->findByAccount($businessId, $account->id);
                    if (count($activity) <= 2) {
                        $issues[] = "Infrequent account used: {$account->code} {$account->name}";
                    }
                }
            }

            if (!empty($issues)) {
                $anomalies[] = [
                    'type' => 'unusual_journal_pattern',
                    'severity' => count($issues) >= 2 ? 'medium' : 'low',
                    'message' => "Journal #{$entry->journalNumber}: " . implode('; ', $issues),
                    'reference_id' => $entry->id,
                    'reference_type' => 'journal_entry',
                    'reference_number' => $entry->journalNumber,
                    'amount' => 0,
                    'date' => $entry->date?->format('Y-m-d'),
                    'issues' => $issues,
                ];
            }
        }

        return $anomalies;
    }

    /**
     * Detect journal entries with amounts significantly outside normal range.
     */
    public function detectOutOfRangeAmounts(int $businessId, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $entries = $this->journalEntryRepo->findByDateRange($businessId, $from, $to);
        $amounts = [];
        $anomalies = [];

        foreach ($entries as $entry) {
            if ($entry->status->value !== 'posted') {
                continue;
            }
            $lines = $this->journalLineRepo->findByJournalEntry($entry->id);
            foreach ($lines as $line) {
                $amt = max($line->debitAmount, $line->creditAmount);
                if ($amt > 0) {
                    $amounts[$line->accountId][] = $amt;
                }
            }
        }

        foreach ($amounts as $accountId => $values) {
            if (count($values) < 5) {
                continue;
            }
            sort($values);
            $median = $values[(int) (count($values) / 2)];
            $mean = array_sum($values) / count($values);
            $variance = array_sum(array_map(fn($v) => ($v - $mean) ** 2, $values)) / count($values);
            $stdDev = sqrt($variance);

            foreach ($entries as $entry) {
                if ($entry->status->value !== 'posted') {
                    continue;
                }
                $lines = $this->journalLineRepo->findByJournalEntry($entry->id);
                foreach ($lines as $line) {
                    if ($line->accountId !== $accountId) {
                        continue;
                    }
                    $amt = max($line->debitAmount, $line->creditAmount);
                    $account = current(array_filter(
                        $this->accountRepo->findActive($businessId),
                        fn($a) => $a->id === $accountId
                    ));

                    if ($stdDev > 0 && $amt > ($mean + 3 * $stdDev)) {
                        $anomalies[] = [
                            'type' => 'out_of_range_amount',
                            'severity' => 'medium',
                            'message' => "Amount {$amt} in journal #{$entry->journalNumber} is >3sigma from mean ({$mean}) for account {$account?->code} {$account?->name}",
                            'reference_id' => $entry->id,
                            'reference_type' => 'journal_entry',
                            'reference_number' => $entry->journalNumber,
                            'amount' => $amt,
                            'date' => $entry->date?->format('Y-m-d'),
                            'account_code' => $account?->code,
                            'account_name' => $account?->name,
                            'mean' => round($mean, 2),
                            'std_dev' => round($stdDev, 2),
                        ];
                    }
                }
            }
        }

        return $anomalies;
    }

    /**
     * Detect unusual payment patterns - frequent small payments suggesting structuring.
     */
    public function detectUnusualPaymentPatterns(int $businessId, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $payments = $this->paymentRepo->findInDateRange($businessId, $from->format('Y-m-d'), $to->format('Y-m-d'));
        $anomalies = [];

        $recent = [];
        foreach ($payments as $payment) {
            $key = ($payment->payableType ?? 'unknown') . ':' . ($payment->payableId ?? '0');
            if (!isset($recent[$key])) {
                $recent[$key] = [];
            }
            $recent[$key][] = $payment;
        }

        foreach ($recent as $key => $txs) {
            if (count($txs) > 10) {
                $total = array_sum(array_map(fn($p) => $p->amount, $txs));
                $avg = $total / count($txs);
                if ($avg < 500 && $total > 0) {
                    $anomalies[] = [
                        'type' => 'unusual_payment_pattern',
                        'severity' => 'medium',
                        'message' => "{$key}: {$total} total across " . count($txs) . " payments (avg {$avg}) - possible structuring",
                        'reference_id' => null,
                        'reference_type' => 'payment',
                        'reference_number' => null,
                        'amount' => $total,
                        'date' => $from->format('Y-m-d'),
                        'transaction_count' => count($txs),
                        'average_amount' => round($avg, 2),
                    ];
                }
            }
        }

        return $anomalies;
    }

    /**
     * Detect posted journals without reference numbers or description.
     */
    public function detectMissingReferences(int $businessId, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $entries = $this->journalEntryRepo->findByDateRange($businessId, $from, $to);
        $anomalies = [];

        foreach ($entries as $entry) {
            if ($entry->status->value !== 'posted') {
                continue;
            }
            if (empty($entry->reference) && empty($entry->description)) {
                $anomalies[] = [
                    'type' => 'missing_reference',
                    'severity' => 'low',
                    'message' => "Journal #{$entry->journalNumber} has no reference or description",
                    'reference_id' => $entry->id,
                    'reference_type' => 'journal_entry',
                    'reference_number' => $entry->journalNumber,
                    'amount' => 0,
                    'date' => $entry->date?->format('Y-m-d'),
                ];
            }
        }

        return $anomalies;
    }
}
