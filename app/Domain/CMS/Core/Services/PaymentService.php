<?php

declare(strict_types=1);

namespace App\Domain\CMS\Core\Services;

use App\Domain\CMS\Core\ValueObjects\InvoiceStatus;
use App\Domain\CMS\Core\ValueObjects\PaymentMethod;
use App\Infrastructure\Persistence\Eloquent\CMS\CustomerModel;
use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PaymentModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PaymentAllocationModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        private readonly AuditTrailService $auditTrail
    ) {}

    /**
     * Record a payment and allocate to invoices
     */
    public function recordPayment(
        int $companyId,
        int $customerId,
        float $amount,
        PaymentMethod $method,
        ?string $reference,
        ?string $notes,
        array $allocations, // ['invoice_id' => amount]
        int $createdBy
    ): PaymentModel {
        return DB::transaction(function () use (
            $companyId,
            $customerId,
            $amount,
            $method,
            $reference,
            $notes,
            $allocations,
            $createdBy
        ) {
            // Create payment record
            $payment = PaymentModel::create([
                'company_id' => $companyId,
                'customer_id' => $customerId,
                'amount' => $amount,
                'payment_method' => $method->value,
                'reference_number' => $reference,
                'payment_date' => now(),
                'notes' => $notes,
                'created_by' => $createdBy,
            ]);

            Log::info('Payment recorded', [
                'payment_id' => $payment->id,
                'customer_id' => $customerId,
                'amount' => $amount,
            ]);

            // Allocate payment to invoices
            $totalAllocated = 0;
            foreach ($allocations as $invoiceId => $allocationAmount) {
                if ($allocationAmount <= 0) {
                    continue;
                }

                $invoice = InvoiceModel::where('company_id', $companyId)
                    ->findOrFail($invoiceId);

                // Create allocation
                PaymentAllocationModel::create([
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoiceId,
                    'amount' => $allocationAmount,
                ]);

                // Update invoice amounts
                $invoice->amount_paid += $allocationAmount;
                $invoice->save();

                // Update invoice status
                $this->updateInvoiceStatus($invoice);

                $totalAllocated += $allocationAmount;

                Log::info('Payment allocated to invoice', [
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoiceId,
                    'amount' => $allocationAmount,
                ]);
            }

            // Update unallocated amount
            $payment->unallocated_amount = $amount - $totalAllocated;
            $payment->save();

            // Update customer balance
            $this->updateCustomerBalance($customerId);

            // Audit trail
            $this->auditTrail->log(
                companyId: $companyId,
                entityType: 'payment',
                entityId: $payment->id,
                action: 'created',
                userId: $createdBy,
                newValues: $payment->toArray()
            );

            return $payment->fresh(['allocations.invoice', 'customer']);
        });
    }

    /**
     * Allocate unallocated payment to an invoice
     */
    public function allocatePayment(
        int $paymentId,
        int $invoiceId,
        float $amount,
        int $userId
    ): PaymentAllocationModel {
        return DB::transaction(function () use ($paymentId, $invoiceId, $amount, $userId) {
            $payment = PaymentModel::findOrFail($paymentId);
            $invoice = InvoiceModel::where('company_id', $payment->company_id)
                ->findOrFail($invoiceId);

            // Validate allocation amount
            if ($amount > $payment->unallocated_amount) {
                throw new \InvalidArgumentException('Allocation amount exceeds unallocated payment amount');
            }

            $balanceDue = $invoice->total_amount - $invoice->amount_paid;
            if ($amount > $balanceDue) {
                throw new \InvalidArgumentException('Allocation amount exceeds invoice balance due');
            }

            // Create allocation
            $allocation = PaymentAllocationModel::create([
                'payment_id' => $paymentId,
                'invoice_id' => $invoiceId,
                'amount' => $amount,
            ]);

            // Update payment unallocated amount
            $payment->unallocated_amount -= $amount;
            $payment->save();

            // Update invoice amount paid
            $invoice->amount_paid += $amount;
            $invoice->save();

            // Update invoice status
            $this->updateInvoiceStatus($invoice);

            // Update customer balance
            $this->updateCustomerBalance($payment->customer_id);

            // Audit trail
            $this->auditTrail->log(
                companyId: $payment->company_id,
                entityType: 'payment_allocation',
                entityId: $allocation->id,
                action: 'created',
                userId: $userId,
                newValues: $allocation->toArray()
            );

            Log::info('Payment allocated', [
                'payment_id' => $paymentId,
                'invoice_id' => $invoiceId,
                'amount' => $amount,
            ]);

            return $allocation;
        });
    }

    /**
     * Void a payment (reverses all allocations)
     */
    public function voidPayment(int $paymentId, string $reason, int $userId): void
    {
        DB::transaction(function () use ($paymentId, $reason, $userId) {
            $payment = PaymentModel::with('allocations.invoice')->findOrFail($paymentId);

            // Reverse all allocations
            foreach ($payment->allocations as $allocation) {
                $invoice = $allocation->invoice;
                $invoice->amount_paid -= $allocation->amount;
                $invoice->save();

                // Update invoice status
                $this->updateInvoiceStatus($invoice);

                // Delete allocation
                $allocation->delete();
            }

            // Mark payment as voided
            $payment->is_voided = true;
            $payment->void_reason = $reason;
            $payment->voided_at = now();
            $payment->voided_by = $userId;
            $payment->save();

            // Update customer balance
            $this->updateCustomerBalance($payment->customer_id);

            // Audit trail
            $this->auditTrail->log(
                companyId: $payment->company_id,
                entityType: 'payment',
                entityId: $payment->id,
                action: 'voided',
                userId: $userId,
                oldValues: ['is_voided' => false],
                newValues: ['is_voided' => true, 'void_reason' => $reason]
            );

            Log::info('Payment voided', [
                'payment_id' => $paymentId,
                'reason' => $reason,
            ]);
        });
    }

    /**
     * Update invoice status based on payment
     */
    private function updateInvoiceStatus(InvoiceModel $invoice): void
    {
        $balanceDue = $invoice->total_amount - $invoice->amount_paid;

        if ($balanceDue <= 0.01) { // Paid (with small tolerance for rounding)
            $invoice->status = InvoiceStatus::PAID->value;
        } elseif ($invoice->amount_paid > 0) { // Partially paid
            $invoice->status = InvoiceStatus::PARTIAL->value;
        } elseif ($invoice->status === InvoiceStatus::DRAFT->value) {
            // Keep as draft
        } else {
            $invoice->status = InvoiceStatus::SENT->value;
        }

        $invoice->save();
    }

    /**
     * Update customer outstanding balance and credit balance
     */
    private function updateCustomerBalance(int $customerId): void
    {
        $customer = CustomerModel::findOrFail($customerId);

        // Calculate total outstanding from all invoices
        $outstanding = InvoiceModel::where('customer_id', $customerId)
            ->whereNotIn('status', [InvoiceStatus::CANCELLED->value, InvoiceStatus::VOID->value])
            ->get()
            ->sum(fn($invoice) => $invoice->total_amount - $invoice->amount_paid);

        $customer->outstanding_balance = max(0, $outstanding);

        // Calculate credit balance from unallocated payments
        $creditBalance = PaymentModel::where('customer_id', $customerId)
            ->where('is_voided', false)
            ->sum('unallocated_amount');

        $customer->credit_balance = max(0, $creditBalance);
        $customer->save();

        Log::info('Customer balance updated', [
            'customer_id' => $customerId,
            'outstanding_balance' => $customer->outstanding_balance,
            'credit_balance' => $customer->credit_balance,
        ]);
    }

    /**
     * Get payment summary for a customer
     */
    public function getCustomerPaymentSummary(int $customerId): array
    {
        $payments = PaymentModel::where('customer_id', $customerId)
            ->where('is_voided', false)
            ->get();

        return [
            'total_paid' => $payments->sum('amount'),
            'total_allocated' => $payments->sum(fn($p) => $p->amount - $p->unallocated_amount),
            'total_unallocated' => $payments->sum('unallocated_amount'),
            'payment_count' => $payments->count(),
        ];
    }

    /**
     * Get unallocated payments for a customer
     */
    public function getUnallocatedPayments(int $customerId): array
    {
        return PaymentModel::where('customer_id', $customerId)
            ->where('is_voided', false)
            ->where('unallocated_amount', '>', 0)
            ->orderBy('payment_date', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get daily cash summary for a company
     */
    public function getDailyCashSummary(int $companyId, ?\DateTime $date = null): array
    {
        $date = $date ?? now();
        $dateString = $date->format('Y-m-d');

        $payments = PaymentModel::where('company_id', $companyId)
            ->where('is_voided', false)
            ->whereDate('payment_date', $dateString)
            ->get();

        $summary = [
            'date' => $dateString,
            'total_received' => $payments->sum('amount'),
            'total_allocated' => $payments->sum(fn($p) => $p->amount - $p->unallocated_amount),
            'total_unallocated' => $payments->sum('unallocated_amount'),
            'payment_count' => $payments->count(),
            'by_method' => [],
        ];

        // Group by payment method
        $byMethod = $payments->groupBy('payment_method');
        foreach ($byMethod as $method => $methodPayments) {
            $summary['by_method'][$method] = [
                'count' => $methodPayments->count(),
                'total' => $methodPayments->sum('amount'),
            ];
        }

        return $summary;
    }

    /**
     * Apply customer credit to an invoice
     */
    public function applyCreditToInvoice(
        int $customerId,
        int $invoiceId,
        float $amount,
        int $userId
    ): void {
        DB::transaction(function () use ($customerId, $invoiceId, $amount, $userId) {
            $customer = CustomerModel::findOrFail($customerId);
            $invoice = InvoiceModel::where('customer_id', $customerId)
                ->findOrFail($invoiceId);

            // Validate credit amount
            if ($amount > $customer->credit_balance) {
                throw new \InvalidArgumentException('Amount exceeds available credit balance');
            }

            $balanceDue = $invoice->total_amount - $invoice->amount_paid;
            if ($amount > $balanceDue) {
                throw new \InvalidArgumentException('Amount exceeds invoice balance due');
            }

            // Find unallocated payments to use as credit
            $unallocatedPayments = PaymentModel::where('customer_id', $customerId)
                ->where('is_voided', false)
                ->where('unallocated_amount', '>', 0)
                ->orderBy('payment_date', 'asc')
                ->get();

            $remainingAmount = $amount;

            foreach ($unallocatedPayments as $payment) {
                if ($remainingAmount <= 0) {
                    break;
                }

                $allocationAmount = min($remainingAmount, $payment->unallocated_amount);

                // Create allocation
                PaymentAllocationModel::create([
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoiceId,
                    'amount' => $allocationAmount,
                ]);

                // Update payment unallocated amount
                $payment->unallocated_amount -= $allocationAmount;
                $payment->save();

                $remainingAmount -= $allocationAmount;
            }

            // Update invoice amount paid
            $invoice->amount_paid += $amount;
            $invoice->save();

            // Update invoice status
            $this->updateInvoiceStatus($invoice);

            // Update customer balances
            $this->updateCustomerBalance($customerId);

            Log::info('Customer credit applied to invoice', [
                'customer_id' => $customerId,
                'invoice_id' => $invoiceId,
                'amount' => $amount,
            ]);
        });
    }

    /**
     * Get customer credit summary
     */
    public function getCustomerCreditSummary(int $customerId): array
    {
        $customer = CustomerModel::findOrFail($customerId);

        $unallocatedPayments = PaymentModel::where('customer_id', $customerId)
            ->where('is_voided', false)
            ->where('unallocated_amount', '>', 0)
            ->orderBy('payment_date', 'desc')
            ->get();

        return [
            'total_credit' => $customer->credit_balance,
            'credit_notes' => $customer->credit_notes,
            'unallocated_payments' => $unallocatedPayments->map(fn($p) => [
                'payment_id' => $p->id,
                'payment_number' => $p->payment_number,
                'payment_date' => $p->payment_date->format('Y-m-d'),
                'original_amount' => $p->amount,
                'unallocated_amount' => $p->unallocated_amount,
                'payment_method' => $p->payment_method,
            ])->toArray(),
        ];
    }
}
