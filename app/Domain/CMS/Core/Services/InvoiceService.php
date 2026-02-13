<?php

declare(strict_types=1);

namespace App\Domain\CMS\Core\Services;

use App\Domain\CMS\Core\ValueObjects\InvoiceNumber;
use App\Domain\CMS\Core\ValueObjects\InvoiceStatus;
use App\Infrastructure\Persistence\Eloquent\CMS\CustomerModel;
use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceItemModel;
use App\Infrastructure\Persistence\Eloquent\CMS\JobModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    public function __construct(
        private readonly AuditTrailService $auditTrail
    ) {}

    /**
     * Auto-generate invoice from completed job
     */
    public function generateFromJob(int $jobId, int $createdBy): InvoiceModel
    {
        return DB::transaction(function () use ($jobId, $createdBy) {
            $job = JobModel::with('customer')->findOrFail($jobId);

            // Validate job is completed
            if ($job->status !== 'completed') {
                throw new \InvalidArgumentException('Only completed jobs can be invoiced');
            }

            // Check if invoice already exists
            if ($job->invoice_id) {
                throw new \InvalidArgumentException('Invoice already exists for this job');
            }

            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber($job->company_id);

            // Create invoice
            $invoice = InvoiceModel::create([
                'company_id' => $job->company_id,
                'customer_id' => $job->customer_id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => now(),
                'due_date' => now()->addDays(30), // Default 30 days
                'status' => InvoiceStatus::DRAFT->value,
                'subtotal' => $job->actual_value ?? $job->estimated_value,
                'total_amount' => $job->actual_value ?? $job->estimated_value,
                'amount_paid' => 0,
                'notes' => "Invoice for Job: {$job->job_number} - {$job->title}",
                'created_by' => $createdBy,
            ]);

            // Create invoice item
            InvoiceItemModel::create([
                'invoice_id' => $invoice->id,
                'description' => $job->title . ($job->description ? "\n" . $job->description : ''),
                'quantity' => 1,
                'unit_price' => $job->actual_value ?? $job->estimated_value,
                'line_total' => $job->actual_value ?? $job->estimated_value,
            ]);

            // Link invoice to job
            $job->invoice_id = $invoice->id;
            $job->save();

            // Audit trail
            $this->auditTrail->log(
                companyId: $job->company_id,
                entityType: 'invoice',
                entityId: $invoice->id,
                action: 'created',
                userId: $createdBy,
                newValues: $invoice->toArray()
            );

            Log::info('Invoice generated from job', [
                'invoice_id' => $invoice->id,
                'job_id' => $jobId,
                'invoice_number' => $invoiceNumber,
            ]);

            return $invoice->fresh(['items', 'customer']);
        });
    }

    /**
     * Create manual invoice
     */
    public function createInvoice(
        int $companyId,
        int $customerId,
        array $items,
        ?string $dueDate,
        ?string $notes,
        int $createdBy
    ): InvoiceModel {
        return DB::transaction(function () use (
            $companyId,
            $customerId,
            $items,
            $dueDate,
            $notes,
            $createdBy
        ) {
            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber($companyId);

            // Calculate totals
            $subtotal = collect($items)->sum(fn($item) => $item['quantity'] * $item['unit_price']);

            // Create invoice
            $invoice = InvoiceModel::create([
                'company_id' => $companyId,
                'customer_id' => $customerId,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => now(),
                'due_date' => $dueDate ?? now()->addDays(30),
                'status' => InvoiceStatus::DRAFT->value,
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
                'amount_paid' => 0,
                'notes' => $notes,
                'created_by' => $createdBy,
            ]);

            // Create invoice items
            foreach ($items as $item) {
                InvoiceItemModel::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            // Audit trail
            $this->auditTrail->log(
                companyId: $companyId,
                entityType: 'invoice',
                entityId: $invoice->id,
                action: 'created',
                userId: $createdBy,
                newValues: $invoice->toArray()
            );

            Log::info('Manual invoice created', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoiceNumber,
            ]);

            return $invoice->fresh(['items', 'customer']);
        });
    }

    /**
     * Update invoice
     */
    public function updateInvoice(
        int $invoiceId,
        array $items,
        ?string $dueDate,
        ?string $notes,
        int $userId
    ): InvoiceModel {
        return DB::transaction(function () use ($invoiceId, $items, $dueDate, $notes, $userId) {
            $invoice = InvoiceModel::findOrFail($invoiceId);

            // Only draft invoices can be edited
            if ($invoice->status !== InvoiceStatus::DRAFT->value) {
                throw new \InvalidArgumentException('Only draft invoices can be edited');
            }

            $oldValues = $invoice->toArray();

            // Calculate new totals
            $subtotal = collect($items)->sum(fn($item) => $item['quantity'] * $item['unit_price']);

            // Update invoice
            $invoice->update([
                'due_date' => $dueDate ?? $invoice->due_date,
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
                'notes' => $notes,
            ]);

            // Delete old items and create new ones
            $invoice->items()->delete();
            foreach ($items as $item) {
                InvoiceItemModel::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            // Audit trail
            $this->auditTrail->log(
                companyId: $invoice->company_id,
                entityType: 'invoice',
                entityId: $invoice->id,
                action: 'updated',
                userId: $userId,
                oldValues: $oldValues,
                newValues: $invoice->fresh()->toArray()
            );

            Log::info('Invoice updated', ['invoice_id' => $invoiceId]);

            return $invoice->fresh(['items', 'customer']);
        });
    }

    /**
     * Send invoice (mark as sent)
     */
    public function sendInvoice(int $invoiceId, int $userId): InvoiceModel
    {
        $invoice = InvoiceModel::findOrFail($invoiceId);

        $oldStatus = $invoice->status;
        $invoice->status = InvoiceStatus::SENT->value;
        $invoice->save();

        // Audit trail
        $this->auditTrail->log(
            companyId: $invoice->company_id,
            entityType: 'invoice',
            entityId: $invoice->id,
            action: 'sent',
            userId: $userId,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => InvoiceStatus::SENT->value]
        );

        Log::info('Invoice sent', ['invoice_id' => $invoiceId]);

        return $invoice->fresh(['items', 'customer']);
    }

    /**
     * Cancel invoice
     */
    public function cancelInvoice(int $invoiceId, string $reason, int $userId): InvoiceModel
    {
        return DB::transaction(function () use ($invoiceId, $reason, $userId) {
            $invoice = InvoiceModel::findOrFail($invoiceId);

            // Cannot cancel paid invoices
            if ($invoice->status === InvoiceStatus::PAID->value) {
                throw new \InvalidArgumentException('Cannot cancel paid invoices. Use void instead.');
            }

            $oldStatus = $invoice->status;
            $invoice->status = InvoiceStatus::CANCELLED->value;
            $invoice->notes = ($invoice->notes ? $invoice->notes . "\n\n" : '') . "Cancelled: {$reason}";
            $invoice->save();

            // Update customer balance
            $this->updateCustomerBalance($invoice->customer_id);

            // Audit trail
            $this->auditTrail->log(
                companyId: $invoice->company_id,
                entityType: 'invoice',
                entityId: $invoice->id,
                action: 'cancelled',
                userId: $userId,
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => InvoiceStatus::CANCELLED->value, 'reason' => $reason]
            );

            Log::info('Invoice cancelled', ['invoice_id' => $invoiceId, 'reason' => $reason]);

            return $invoice->fresh(['items', 'customer']);
        });
    }

    /**
     * Void invoice (for paid invoices)
     */
    public function voidInvoice(int $invoiceId, string $reason, int $userId): InvoiceModel
    {
        return DB::transaction(function () use ($invoiceId, $reason, $userId) {
            $invoice = InvoiceModel::findOrFail($invoiceId);

            // Only paid invoices can be voided
            if ($invoice->status !== InvoiceStatus::PAID->value) {
                throw new \InvalidArgumentException('Only paid invoices can be voided');
            }

            $oldStatus = $invoice->status;
            $invoice->status = InvoiceStatus::VOID->value;
            $invoice->notes = ($invoice->notes ? $invoice->notes . "\n\n" : '') . "Voided: {$reason}";
            $invoice->save();

            // Update customer balance
            $this->updateCustomerBalance($invoice->customer_id);

            // Audit trail
            $this->auditTrail->log(
                companyId: $invoice->company_id,
                entityType: 'invoice',
                entityId: $invoice->id,
                action: 'voided',
                userId: $userId,
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => InvoiceStatus::VOID->value, 'reason' => $reason]
            );

            Log::info('Invoice voided', ['invoice_id' => $invoiceId, 'reason' => $reason]);

            return $invoice->fresh(['items', 'customer']);
        });
    }

    /**
     * Get invoice summary for dashboard
     */
    public function getInvoiceSummary(int $companyId): array
    {
        $invoices = InvoiceModel::where('company_id', $companyId)->get();

        return [
            'total_invoices' => $invoices->count(),
            'draft_count' => $invoices->where('status', InvoiceStatus::DRAFT->value)->count(),
            'sent_count' => $invoices->where('status', InvoiceStatus::SENT->value)->count(),
            'partial_count' => $invoices->where('status', InvoiceStatus::PARTIAL->value)->count(),
            'paid_count' => $invoices->where('status', InvoiceStatus::PAID->value)->count(),
            'total_value' => $invoices->sum('total_amount'),
            'total_paid' => $invoices->sum('amount_paid'),
            'total_outstanding' => $invoices->sum(fn($inv) => $inv->total_amount - $inv->amount_paid),
        ];
    }

    /**
     * Generate next invoice number for company
     */
    private function generateInvoiceNumber(int $companyId): string
    {
        $year = date('Y');
        $lastInvoice = InvoiceModel::where('company_id', $companyId)
            ->where('invoice_number', 'like', "INV-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            // Extract sequence number from last invoice
            $parts = explode('-', $lastInvoice->invoice_number);
            $sequence = (int) end($parts) + 1;
        } else {
            $sequence = 1;
        }

        return InvoiceNumber::generate($year, $sequence);
    }

    /**
     * Update customer outstanding balance
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
        $customer->save();
    }
}
