<?php

declare(strict_types=1);

namespace App\Domain\BMS\Core\Services;

use App\Domain\BMS\Core\ValueObjects\InvoiceStatus;
use App\Domain\BMS\Core\ValueObjects\PaymentMethod;
use App\Domain\BMS\Entities\Payment;
use App\Domain\BMS\Entities\PaymentAllocation;
use App\Domain\BMS\Entities\Invoice;
use App\Domain\BMS\Entities\Customer;
use App\Domain\BMS\Repositories\PaymentRepositoryInterface;
use App\Domain\BMS\Repositories\PaymentAllocationRepositoryInterface;
use App\Domain\BMS\Repositories\InvoiceRepositoryInterface;
use App\Domain\BMS\Repositories\CustomerRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        private readonly PaymentRepositoryInterface $paymentRepo,
        private readonly PaymentAllocationRepositoryInterface $allocationRepo,
        private readonly InvoiceRepositoryInterface $invoiceRepo,
        private readonly CustomerRepositoryInterface $customerRepo,
        private readonly AuditTrailService $auditTrail
    ) {}

    public function recordPayment(
        int $companyId, int $customerId, float $amount, PaymentMethod $method,
        ?string $reference, ?string $notes, array $allocations, int $createdBy
    ): array {
        return DB::transaction(function () use ($companyId, $customerId, $amount, $method, $reference, $notes, $allocations, $createdBy) {
            $payment = Payment::reconstitute([
                'company_id' => $companyId,
                'customer_id' => $customerId,
                'amount' => $amount,
                'payment_method' => $method->value,
                'reference_number' => $reference,
                'payment_date' => now()->format('Y-m-d'),
                'unallocated_amount' => $amount,
                'is_voided' => false,
                'notes' => $notes,
                'created_by' => $createdBy,
            ]);
            $payment = $this->paymentRepo->save($payment);

            Log::info('Payment recorded', ['payment_id' => $payment->id, 'customer_id' => $customerId, 'amount' => $amount]);

            $totalAllocated = 0;
            foreach ($allocations as $invoiceId => $allocationAmount) {
                if ($allocationAmount <= 0) continue;

                $invoice = $this->invoiceRepo->findById($invoiceId);
                if (!$invoice) continue;

                $allocation = PaymentAllocation::reconstitute([
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoiceId,
                    'amount' => $allocationAmount,
                ]);
                $this->allocationRepo->save($allocation);

                $updatedInvoice = Invoice::reconstitute(array_merge($invoice->toArray(), [
                    'amount_paid' => $invoice->amountPaid + $allocationAmount,
                ]));
                $this->invoiceRepo->save($updatedInvoice);
                $this->updateInvoiceStatus($updatedInvoice);
                $totalAllocated += $allocationAmount;

                Log::info('Payment allocated to invoice', ['payment_id' => $payment->id, 'invoice_id' => $invoiceId, 'amount' => $allocationAmount]);
            }

            $paymentUpdated = Payment::reconstitute(array_merge($payment->toArray(), ['unallocated_amount' => $amount - $totalAllocated]));
            $this->paymentRepo->save($paymentUpdated);
            $this->updateCustomerBalance($customerId);

            $this->auditTrail->log($companyId, $createdBy, 'payment', $payment->id, 'created', null, $payment->toArray());

            return ['payment' => $this->paymentRepo->findById($payment->id), 'payment_model' => null];
        });
    }

    public function allocatePayment(int $paymentId, int $invoiceId, float $amount, int $userId): PaymentAllocation
    {
        return DB::transaction(function () use ($paymentId, $invoiceId, $amount, $userId) {
            $payment = $this->paymentRepo->findById($paymentId);
            if (!$payment) throw new \InvalidArgumentException('Payment not found');

            $invoice = $this->invoiceRepo->findById($invoiceId);
            if (!$invoice) throw new \InvalidArgumentException('Invoice not found');

            if ($amount > $payment->unallocatedAmount) throw new \InvalidArgumentException('Allocation amount exceeds unallocated payment amount');
            $balanceDue = $invoice->totalAmount - $invoice->amountPaid;
            if ($amount > $balanceDue) throw new \InvalidArgumentException('Allocation amount exceeds invoice balance due');

            $allocation = PaymentAllocation::reconstitute(['payment_id' => $paymentId, 'invoice_id' => $invoiceId, 'amount' => $amount]);
            $allocation = $this->allocationRepo->save($allocation);

            $paymentUpdated = Payment::reconstitute(array_merge($payment->toArray(), ['unallocated_amount' => $payment->unallocatedAmount - $amount]));
            $this->paymentRepo->save($paymentUpdated);

            $updatedInvoice = Invoice::reconstitute(array_merge($invoice->toArray(), ['amount_paid' => $invoice->amountPaid + $amount]));
            $this->invoiceRepo->save($updatedInvoice);
            $this->updateInvoiceStatus($updatedInvoice);
            $this->updateCustomerBalance($payment->customerId);

            $this->auditTrail->log($payment->companyId, $userId, 'payment_allocation', $allocation->id, 'created', null, $allocation->toArray());
            Log::info('Payment allocated', ['payment_id' => $paymentId, 'invoice_id' => $invoiceId, 'amount' => $amount]);

            return $allocation;
        });
    }

    public function voidPayment(int $paymentId, string $reason, int $userId): void
    {
        DB::transaction(function () use ($paymentId, $reason, $userId) {
            $payment = $this->paymentRepo->findById($paymentId);
            if (!$payment) throw new \InvalidArgumentException('Payment not found');

            $allocations = $this->allocationRepo->findByPayment($paymentId);
            foreach ($allocations as $allocation) {
                $invoice = $this->invoiceRepo->findById($allocation->invoiceId);
                if ($invoice) {
                    $updatedInvoice = Invoice::reconstitute(array_merge($invoice->toArray(), ['amount_paid' => $invoice->amountPaid - $allocation->amount]));
                    $this->invoiceRepo->save($updatedInvoice);
                    $this->updateInvoiceStatus($updatedInvoice);
                }
                $this->allocationRepo->delete($allocation->id);
            }

            $paymentUpdated = Payment::reconstitute(array_merge($payment->toArray(), [
                'is_voided' => true,
                'void_reason' => $reason,
                'voided_at' => now()->format('Y-m-d H:i:s'),
                'voided_by' => $userId,
            ]));
            $this->paymentRepo->save($paymentUpdated);
            $this->updateCustomerBalance($payment->customerId);

            $this->auditTrail->log($payment->companyId, $userId, 'payment', $paymentId, 'voided', ['is_voided' => false], ['is_voided' => true, 'void_reason' => $reason]);
            Log::info('Payment voided', ['payment_id' => $paymentId, 'reason' => $reason]);
        });
    }

    private function updateInvoiceStatus(Invoice $invoice): void
    {
        $balanceDue = $invoice->totalAmount - $invoice->amountPaid;
        $newStatus = $invoice->status;

        if ($balanceDue <= 0.01) {
            $newStatus = InvoiceStatus::PAID->value;
        } elseif ($invoice->amountPaid > 0) {
            $newStatus = InvoiceStatus::PARTIAL->value;
        } elseif ($invoice->status === InvoiceStatus::DRAFT->value) {
            $newStatus = InvoiceStatus::DRAFT->value;
        } else {
            $newStatus = InvoiceStatus::SENT->value;
        }

        if ($newStatus !== $invoice->status) {
            $updated = Invoice::reconstitute(array_merge($invoice->toArray(), ['status' => $newStatus]));
            $this->invoiceRepo->save($updated);
        }
    }

    private function updateCustomerBalance(int $customerId): void
    {
        $customer = $this->customerRepo->findById($customerId);
        if (!$customer) return;

        $invoices = $this->invoiceRepo->findByCustomer($customerId);
        $outstanding = collect($invoices)
            ->filter(fn($inv) => !in_array($inv->status, [InvoiceStatus::CANCELLED->value, InvoiceStatus::VOID->value]))
            ->sum(fn($inv) => $inv->totalAmount - $inv->amountPaid);

        $payments = $this->paymentRepo->findByCustomer($customerId);
        $creditBalance = collect($payments)->where('isVoided', false)->sum('unallocatedAmount');

        $updated = Customer::reconstitute(array_merge($customer->toArray(), [
            'outstanding_balance' => max(0, $outstanding),
            'credit_balance' => max(0, $creditBalance),
        ]));
        $this->customerRepo->save($updated);

        Log::info('Customer balance updated', ['customer_id' => $customerId, 'outstanding_balance' => $updated->outstandingBalance, 'credit_balance' => $updated->creditBalance]);
    }

    public function getCustomerPaymentSummary(int $customerId): array
    {
        return $this->paymentRepo->getCustomerPaymentSummary($customerId);
    }

    public function getUnallocatedPayments(int $customerId): array
    {
        return array_map(fn($p) => $p->toArray(), $this->paymentRepo->findUnallocatedByCustomer($customerId));
    }

    public function getDailyCashSummary(int $companyId, ?\DateTime $date = null): array
    {
        $dateStr = ($date ?? now())->format('Y-m-d');
        return $this->paymentRepo->getDailySummary($companyId, $dateStr);
    }

    public function getCustomerCreditSummary(int $customerId): array
    {
        $customer = $this->customerRepo->findById($customerId);
        if (!$customer) throw new \InvalidArgumentException('Customer not found');

        $unallocatedPayments = $this->paymentRepo->findUnallocatedByCustomer($customerId);

        return [
            'total_credit' => $customer->creditBalance,
            'unallocated_payments' => array_map(fn($p) => [
                'payment_id' => $p->id,
                'payment_date' => $p->paymentDate->format('Y-m-d'),
                'original_amount' => $p->amount,
                'unallocated_amount' => $p->unallocatedAmount,
                'payment_method' => $p->paymentMethod,
            ], $unallocatedPayments),
        ];
    }

    public function applyCreditToInvoice(int $customerId, int $invoiceId, float $amount, int $userId): void
    {
        DB::transaction(function () use ($customerId, $invoiceId, $amount, $userId) {
            $customer = $this->customerRepo->findById($customerId);
            if (!$customer) throw new \InvalidArgumentException('Customer not found');

            $invoice = $this->invoiceRepo->findById($invoiceId);
            if (!$invoice) throw new \InvalidArgumentException('Invoice not found');

            if ($amount > $customer->creditBalance) throw new \InvalidArgumentException('Amount exceeds available credit balance');
            $balanceDue = $invoice->totalAmount - $invoice->amountPaid;
            if ($amount > $balanceDue) throw new \InvalidArgumentException('Amount exceeds invoice balance due');

            $unallocatedPayments = $this->paymentRepo->findUnallocatedByCustomer($customerId);
            $remainingAmount = $amount;

            foreach ($unallocatedPayments as $payment) {
                if ($remainingAmount <= 0) break;
                $allocationAmount = min($remainingAmount, $payment->unallocatedAmount);

                $allocation = PaymentAllocation::reconstitute(['payment_id' => $payment->id, 'invoice_id' => $invoiceId, 'amount' => $allocationAmount]);
                $this->allocationRepo->save($allocation);

                $paymentUpdated = Payment::reconstitute(array_merge($payment->toArray(), ['unallocated_amount' => $payment->unallocatedAmount - $allocationAmount]));
                $this->paymentRepo->save($paymentUpdated);
                $remainingAmount -= $allocationAmount;
            }

            $updatedInvoice = Invoice::reconstitute(array_merge($invoice->toArray(), ['amount_paid' => $invoice->amountPaid + $amount]));
            $this->invoiceRepo->save($updatedInvoice);
            $this->updateInvoiceStatus($updatedInvoice);
            $this->updateCustomerBalance($customerId);

            Log::info('Customer credit applied to invoice', ['customer_id' => $customerId, 'invoice_id' => $invoiceId, 'amount' => $amount]);
        });
    }
}
