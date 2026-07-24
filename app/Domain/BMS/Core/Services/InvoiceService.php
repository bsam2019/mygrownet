<?php

declare(strict_types=1);

namespace App\Domain\BMS\Core\Services;

use App\Domain\BMS\Core\ValueObjects\InvoiceNumber;
use App\Domain\BMS\Core\ValueObjects\InvoiceStatus;
use App\Domain\BMS\Entities\Invoice;
use App\Domain\BMS\Entities\InvoiceItem;
use App\Domain\BMS\Repositories\InvoiceRepositoryInterface;
use App\Domain\BMS\Repositories\InvoiceItemRepositoryInterface;
use App\Domain\BMS\Repositories\JobRepositoryInterface;
use App\Domain\BMS\Repositories\CustomerRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $invoiceRepo,
        private readonly InvoiceItemRepositoryInterface $invoiceItemRepo,
        private readonly JobRepositoryInterface $jobRepo,
        private readonly CustomerRepositoryInterface $customerRepo,
        private readonly AuditTrailService $auditTrail
    ) {}

    public function generateFromJob(int $jobId, int $createdBy): Invoice
    {
        return DB::transaction(function () use ($jobId, $createdBy) {
            $job = $this->jobRepo->findById($jobId);
            if (!$job) throw new \InvalidArgumentException('Job not found');
            if ($job->status !== 'completed') throw new \InvalidArgumentException('Only completed jobs can be invoiced');

            $invoiceNumber = $this->generateInvoiceNumber($job->companyId);

            $invoice = Invoice::reconstitute([
                'company_id' => $job->companyId,
                'customer_id' => $job->customerId,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => now()->format('Y-m-d'),
                'due_date' => now()->addDays(30)->format('Y-m-d'),
                'status' => InvoiceStatus::DRAFT->value,
                'subtotal' => $job->actualValue ?? $job->quotedValue ?? 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $job->actualValue ?? $job->quotedValue ?? 0,
                'amount_paid' => 0,
                'notes' => "Invoice for Job: {$job->jobNumber} - {$job->jobType}",
                'created_by' => $createdBy,
            ]);
            $invoice = $this->invoiceRepo->save($invoice);

            $item = InvoiceItem::reconstitute([
                'invoice_id' => $invoice->id,
                'description' => $job->jobType . ($job->description ? "\n" . $job->description : ''),
                'quantity' => 1,
                'unit_price' => $job->actualValue ?? $job->quotedValue ?? 0,
                'line_total' => $job->actualValue ?? $job->quotedValue ?? 0,
            ]);
            $this->invoiceItemRepo->save($item);

            $this->auditTrail->log($job->companyId, $createdBy, 'invoice', $invoice->id, 'created', null, $invoice->toArray());
            Log::info('Invoice generated from job', ['invoice_id' => $invoice->id, 'job_id' => $jobId]);

            return $invoice;
        });
    }

    public function createInvoice(
        int $companyId, int $customerId, array $items, ?string $dueDate, ?string $notes, int $createdBy
    ): Invoice {
        return DB::transaction(function () use ($companyId, $customerId, $items, $dueDate, $notes, $createdBy) {
            $invoiceNumber = $this->generateInvoiceNumber($companyId);
            $subtotal = collect($items)->sum(fn($item) => $item['quantity'] * $item['unit_price']);

            $invoice = Invoice::reconstitute([
                'company_id' => $companyId,
                'customer_id' => $customerId,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => now()->format('Y-m-d'),
                'due_date' => $dueDate ?? now()->addDays(30)->format('Y-m-d'),
                'status' => InvoiceStatus::DRAFT->value,
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
                'amount_paid' => 0,
                'notes' => $notes,
                'created_by' => $createdBy,
            ]);
            $invoice = $this->invoiceRepo->save($invoice);

            foreach ($items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $invoiceItem = InvoiceItem::reconstitute([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'amount' => $lineTotal,
                    'line_total' => $lineTotal,
                    'dimensions' => $item['dimensions'] ?? null,
                    'dimensions_value' => $item['dimensions_value'] ?? 1,
                ]);
                $this->invoiceItemRepo->save($invoiceItem);
            }

            $this->auditTrail->log($companyId, $createdBy, 'invoice', $invoice->id, 'created', null, $invoice->toArray());
            Log::info('Manual invoice created', ['invoice_id' => $invoice->id, 'invoice_number' => $invoiceNumber]);

            return $invoice;
        });
    }

    public function updateInvoice(int $invoiceId, array $items, ?string $dueDate, ?string $notes, int $userId): Invoice
    {
        return DB::transaction(function () use ($invoiceId, $items, $dueDate, $notes, $userId) {
            $invoice = $this->invoiceRepo->findById($invoiceId);
            if (!$invoice) throw new \InvalidArgumentException('Invoice not found');
            if ($invoice->status !== InvoiceStatus::DRAFT->value) throw new \InvalidArgumentException('Only draft invoices can be edited');

            $oldValues = $invoice->toArray();
            $subtotal = collect($items)->sum(fn($item) => $item['quantity'] * $item['unit_price']);

            $updated = Invoice::reconstitute(array_merge($invoice->toArray(), [
                'due_date' => $dueDate ?? $invoice->dueDate?->format('Y-m-d'),
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
                'notes' => $notes,
            ]));
            $this->invoiceRepo->save($updated);
            $this->invoiceItemRepo->deleteByInvoice($invoiceId);

            foreach ($items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $invoiceItem = InvoiceItem::reconstitute([
                    'invoice_id' => $invoiceId,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'amount' => $lineTotal,
                    'line_total' => $lineTotal,
                    'dimensions' => $item['dimensions'] ?? null,
                    'dimensions_value' => $item['dimensions_value'] ?? 1,
                ]);
                $this->invoiceItemRepo->save($invoiceItem);
            }

            $this->auditTrail->log($invoice->companyId, $userId, 'invoice', $invoiceId, 'updated', $oldValues, $updated->toArray());
            Log::info('Invoice updated', ['invoice_id' => $invoiceId]);

            return $this->invoiceRepo->findById($invoiceId);
        });
    }

    public function sendInvoice(int $invoiceId, int $userId): Invoice
    {
        $invoice = $this->invoiceRepo->findById($invoiceId);
        if (!$invoice) throw new \InvalidArgumentException('Invoice not found');

        $oldStatus = $invoice->status;
        $updated = Invoice::reconstitute(array_merge($invoice->toArray(), ['status' => InvoiceStatus::SENT->value]));
        $this->invoiceRepo->save($updated);

        $this->auditTrail->log($invoice->companyId, $userId, 'invoice', $invoiceId, 'sent', ['status' => $oldStatus], ['status' => InvoiceStatus::SENT->value]);
        Log::info('Invoice sent', ['invoice_id' => $invoiceId]);

        return $this->invoiceRepo->findById($invoiceId);
    }

    public function cancelInvoice(int $invoiceId, string $reason, int $userId): Invoice
    {
        return DB::transaction(function () use ($invoiceId, $reason, $userId) {
            $invoice = $this->invoiceRepo->findById($invoiceId);
            if (!$invoice) throw new \InvalidArgumentException('Invoice not found');
            if ($invoice->status === InvoiceStatus::PAID->value) throw new \InvalidArgumentException('Cannot cancel paid invoices. Use void instead.');

            $oldStatus = $invoice->status;
            $newNotes = ($invoice->notes ? $invoice->notes . "\n\n" : '') . "Cancelled: {$reason}";
            $updated = Invoice::reconstitute(array_merge($invoice->toArray(), ['status' => InvoiceStatus::CANCELLED->value, 'notes' => $newNotes]));
            $this->invoiceRepo->save($updated);
            $this->updateCustomerBalance($invoice->customerId);

            $this->auditTrail->log($invoice->companyId, $userId, 'invoice', $invoiceId, 'cancelled', ['status' => $oldStatus], ['status' => InvoiceStatus::CANCELLED->value, 'reason' => $reason]);
            Log::info('Invoice cancelled', ['invoice_id' => $invoiceId, 'reason' => $reason]);

            return $this->invoiceRepo->findById($invoiceId);
        });
    }

    public function voidInvoice(int $invoiceId, string $reason, int $userId): Invoice
    {
        return DB::transaction(function () use ($invoiceId, $reason, $userId) {
            $invoice = $this->invoiceRepo->findById($invoiceId);
            if (!$invoice) throw new \InvalidArgumentException('Invoice not found');
            if ($invoice->status !== InvoiceStatus::PAID->value) throw new \InvalidArgumentException('Only paid invoices can be voided');

            $oldStatus = $invoice->status;
            $newNotes = ($invoice->notes ? $invoice->notes . "\n\n" : '') . "Voided: {$reason}";
            $updated = Invoice::reconstitute(array_merge($invoice->toArray(), ['status' => InvoiceStatus::VOID->value, 'notes' => $newNotes]));
            $this->invoiceRepo->save($updated);
            $this->updateCustomerBalance($invoice->customerId);

            $this->auditTrail->log($invoice->companyId, $userId, 'invoice', $invoiceId, 'voided', ['status' => $oldStatus], ['status' => InvoiceStatus::VOID->value, 'reason' => $reason]);
            Log::info('Invoice voided', ['invoice_id' => $invoiceId, 'reason' => $reason]);

            return $this->invoiceRepo->findById($invoiceId);
        });
    }

    public function getInvoiceSummary(int $companyId): array
    {
        return $this->invoiceRepo->getSummary($companyId);
    }

    private function generateInvoiceNumber(int $companyId): string
    {
        $year = date('Y');
        $invoices = $this->invoiceRepo->findByCompany($companyId);
        $lastInvoice = null;
        foreach ($invoices as $inv) {
            if (str_starts_with($inv->invoiceNumber, "INV-{$year}-")) {
                $lastInvoice = $inv;
            }
        }
        $sequence = $lastInvoice ? (int) substr($lastInvoice->invoiceNumber, -4) + 1 : 1;
        return InvoiceNumber::generate($year, $sequence);
    }

    private function updateCustomerBalance(int $customerId): void
    {
        $customer = $this->customerRepo->findById($customerId);
        if (!$customer) return;

        $invoices = $this->invoiceRepo->findByCustomer($customerId);
        $outstanding = collect($invoices)->filter(fn($inv) => !in_array($inv->status, [InvoiceStatus::CANCELLED->value, InvoiceStatus::VOID->value]))
            ->sum(fn($inv) => $inv->totalAmount - $inv->amountPaid);

        $updated = Customer::reconstitute(array_merge($customer->toArray(), [
            'outstanding_balance' => max(0, $outstanding),
        ]));
        $this->customerRepo->save($updated);
    }
}
