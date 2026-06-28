<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\RecurringInvoiceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\RecurringInvoiceHistoryModel;
use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use App\Services\CMS\EmailService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecurringInvoiceService
{
    public function __construct(
        private InvoiceService $invoiceService,
        private EmailService $emailService
    ) {}

    public function create(int $companyId, array $data): RecurringInvoiceModel
    {
        $data['company_id'] = $companyId;
        $data['next_generation_date'] = $data['start_date'];
        $data['occurrences_count'] = 0;
        
        return RecurringInvoiceModel::create($data);
    }

    public function update(RecurringInvoiceModel $recurringInvoice, array $data): RecurringInvoiceModel
    {
        $recurringInvoice->update($data);
        return $recurringInvoice->fresh();
    }

    public function delete(RecurringInvoiceModel $recurringInvoice): bool
    {
        return $recurringInvoice->delete();
    }

    public function pause(RecurringInvoiceModel $recurringInvoice): RecurringInvoiceModel
    {
        $recurringInvoice->update(['status' => 'paused']);
        return $recurringInvoice->fresh();
    }

    public function resume(RecurringInvoiceModel $recurringInvoice): RecurringInvoiceModel
    {
        $recurringInvoice->update(['status' => 'active']);
        return $recurringInvoice->fresh();
    }

    public function cancel(RecurringInvoiceModel $recurringInvoice): RecurringInvoiceModel
    {
        $recurringInvoice->update(['status' => 'cancelled']);
        return $recurringInvoice->fresh();
    }


    public function generateInvoice(RecurringInvoiceModel $recurringInvoice): ?InvoiceModel
    {
        if ($recurringInvoice->status !== 'active') {
            return null;
        }

        // Check if we've reached max occurrences
        if ($recurringInvoice->max_occurrences && $recurringInvoice->occurrences_count >= $recurringInvoice->max_occurrences) {
            $recurringInvoice->update(['status' => 'completed']);
            return null;
        }

        // Check if we've passed end date
        if ($recurringInvoice->end_date && Carbon::today()->gt($recurringInvoice->end_date)) {
            $recurringInvoice->update(['status' => 'completed']);
            return null;
        }

        return DB::transaction(function () use ($recurringInvoice) {
            // Generate invoice number
            $invoiceNumber = $this->invoiceService->generateInvoiceNumber($recurringInvoice->company_id);
            
            // Calculate due date
            $invoiceDate = Carbon::today();
            $dueDate = $invoiceDate->copy()->addDays($recurringInvoice->payment_terms_days);

            // Create invoice
            $invoice = InvoiceModel::create([
                'company_id' => $recurringInvoice->company_id,
                'customer_id' => $recurringInvoice->customer_id,
                'job_id' => $recurringInvoice->job_id,
                'recurring_invoice_id' => $recurringInvoice->id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'subtotal' => $recurringInvoice->subtotal,
                'tax_amount' => $recurringInvoice->tax_amount,
                'total_amount' => $recurringInvoice->total,
                'amount_paid' => 0,
                'amount_due' => $recurringInvoice->total,
                'status' => 'draft',
                'notes' => $recurringInvoice->notes,
                'terms' => "Generated from recurring invoice: {$recurringInvoice->title}",
            ]);

            // Create invoice items
            foreach ($recurringInvoice->items as $item) {
                $invoice->items()->create($item);
            }

            // Update invoice status to sent
            $invoice->update(['status' => 'sent']);

            // Record in history
            $history = RecurringInvoiceHistoryModel::create([
                'recurring_invoice_id' => $recurringInvoice->id,
                'invoice_id' => $invoice->id,
                'generated_date' => $invoiceDate,
                'email_sent' => false,
            ]);

            // Send email if enabled
            if ($recurringInvoice->auto_send_email) {
                $emailTo = $recurringInvoice->email_to ?: $recurringInvoice->customer->email;
                $emailCc = $recurringInvoice->email_cc;

                $sent = $this->emailService->sendInvoice($invoice, $emailTo, $emailCc);
                
                if ($sent) {
                    $history->update([
                        'email_sent' => true,
                        'email_sent_at' => now(),
                    ]);
                }
            }

            // Calculate next generation date
            $nextDate = $this->calculateNextGenerationDate(
                $recurringInvoice->next_generation_date,
                $recurringInvoice->frequency,
                $recurringInvoice->interval
            );

            // Update recurring invoice
            $recurringInvoice->update([
                'occurrences_count' => $recurringInvoice->occurrences_count + 1,
                'next_generation_date' => $nextDate,
                'last_generated_at' => now(),
            ]);

            return $invoice;
        });
    }

    public function calculateNextGenerationDate(Carbon|string $currentDate, string $frequency, int $interval): Carbon
    {
        $date = $currentDate instanceof Carbon ? $currentDate : Carbon::parse($currentDate);

        return match ($frequency) {
            'daily' => $date->copy()->addDays($interval),
            'weekly' => $date->copy()->addWeeks($interval),
            'monthly' => $date->copy()->addMonths($interval),
            'yearly' => $date->copy()->addYears($interval),
            default => $date->copy()->addMonths($interval),
        };
    }

    public function getDueForGeneration(int $companyId): array
    {
        return RecurringInvoiceModel::where('company_id', $companyId)
            ->where('status', 'active')
            ->where('next_generation_date', '<=', Carbon::today())
            ->with(['customer', 'job'])
            ->get()
            ->toArray();
    }

    public function getHistory(RecurringInvoiceModel $recurringInvoice): array
    {
        return $recurringInvoice->history()
            ->with('invoice')
            ->orderBy('generated_date', 'desc')
            ->get()
            ->toArray();
    }

    public function getAll(int $companyId, ?string $status = null): array
    {
        $query = RecurringInvoiceModel::where('company_id', $companyId)
            ->with(['customer', 'job']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('next_generation_date', 'asc')
            ->get()
            ->toArray();
    }

    public function getById(int $id, int $companyId): ?RecurringInvoiceModel
    {
        return RecurringInvoiceModel::where('id', $id)
            ->where('company_id', $companyId)
            ->with(['customer', 'job', 'history.invoice'])
            ->first();
    }
}
