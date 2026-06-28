<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\ValueObjects\QuotationStatus;
use App\Domain\GrowFinance\ValueObjects\InvoiceStatus;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceQuotationModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceQuotationItemModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceInvoiceModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceInvoiceItemModel;
use Illuminate\Support\Facades\DB;

class QuotationService
{
    public function generateQuotationNumber(int $businessId): string
    {
        $lastQuotation = GrowFinanceQuotationModel::forBusiness($businessId)
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = $lastQuotation 
            ? ((int) substr($lastQuotation->quotation_number, 4)) + 1 
            : 1;
        
        return 'QUO-' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function create(int $businessId, array $data): GrowFinanceQuotationModel
    {
        return DB::transaction(function () use ($businessId, $data) {
            $quotationNumber = $this->generateQuotationNumber($businessId);
            $subtotal = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);

            $quotation = GrowFinanceQuotationModel::create([
                'business_id' => $businessId,
                'customer_id' => $data['customer_id'] ?? null,
                'template_id' => $data['template_id'] ?? null,
                'quotation_number' => $quotationNumber,
                'quotation_date' => $data['quotation_date'],
                'valid_until' => $data['valid_until'] ?? null,
                'status' => QuotationStatus::DRAFT,
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
                'subject' => $data['subject'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                GrowFinanceQuotationItemModel::create([
                    'quotation_id' => $quotation->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            return $quotation->load('items', 'customer');
        });
    }

    public function update(GrowFinanceQuotationModel $quotation, array $data): GrowFinanceQuotationModel
    {
        return DB::transaction(function () use ($quotation, $data) {
            $subtotal = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);

            $quotation->update([
                'customer_id' => $data['customer_id'] ?? null,
                'quotation_date' => $data['quotation_date'],
                'valid_until' => $data['valid_until'] ?? null,
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
                'subject' => $data['subject'] ?? null,
            ]);

            // Delete existing items and recreate
            $quotation->items()->delete();

            foreach ($data['items'] as $item) {
                GrowFinanceQuotationItemModel::create([
                    'quotation_id' => $quotation->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            return $quotation->fresh(['items', 'customer']);
        });
    }

    public function send(GrowFinanceQuotationModel $quotation): GrowFinanceQuotationModel
    {
        $quotation->update([
            'status' => QuotationStatus::SENT,
            'sent_at' => now(),
        ]);

        return $quotation;
    }

    public function accept(GrowFinanceQuotationModel $quotation): GrowFinanceQuotationModel
    {
        $quotation->update([
            'status' => QuotationStatus::ACCEPTED,
            'accepted_at' => now(),
        ]);

        return $quotation;
    }

    public function reject(GrowFinanceQuotationModel $quotation, ?string $reason = null): GrowFinanceQuotationModel
    {
        $quotation->update([
            'status' => QuotationStatus::REJECTED,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $quotation;
    }

    public function markExpired(GrowFinanceQuotationModel $quotation): GrowFinanceQuotationModel
    {
        $quotation->update([
            'status' => QuotationStatus::EXPIRED,
        ]);

        return $quotation;
    }

    /**
     * Convert an accepted quotation to an invoice
     */
    public function convertToInvoice(GrowFinanceQuotationModel $quotation, int $businessId): GrowFinanceInvoiceModel
    {
        if ($quotation->status !== QuotationStatus::ACCEPTED->value) {
            throw new \InvalidArgumentException('Only accepted quotations can be converted to invoices.');
        }

        if ($quotation->converted_invoice_id) {
            throw new \InvalidArgumentException('This quotation has already been converted to an invoice.');
        }

        return DB::transaction(function () use ($quotation, $businessId) {
            // Generate invoice number
            $lastInvoice = GrowFinanceInvoiceModel::forBusiness($businessId)->orderBy('id', 'desc')->first();
            $nextNumber = $lastInvoice ? ((int) substr($lastInvoice->invoice_number, 4)) + 1 : 1;
            $invoiceNumber = 'INV-' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);

            // Create invoice from quotation
            $invoice = GrowFinanceInvoiceModel::create([
                'business_id' => $businessId,
                'customer_id' => $quotation->customer_id,
                'template_id' => $quotation->template_id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => now(),
                'due_date' => now()->addDays(30),
                'status' => InvoiceStatus::DRAFT,
                'subtotal' => $quotation->subtotal,
                'tax_amount' => $quotation->tax_amount,
                'discount_amount' => $quotation->discount_amount,
                'total_amount' => $quotation->total_amount,
                'notes' => $quotation->notes,
                'terms' => $quotation->terms,
            ]);

            // Copy items
            foreach ($quotation->items as $item) {
                GrowFinanceInvoiceItemModel::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'tax_rate' => $item->tax_rate,
                    'discount_rate' => $item->discount_rate,
                    'line_total' => $item->line_total,
                ]);
            }

            // Update quotation status
            $quotation->update([
                'status' => QuotationStatus::CONVERTED,
                'converted_invoice_id' => $invoice->id,
            ]);

            return $invoice->load('items', 'customer');
        });
    }

    /**
     * Update expired quotations
     */
    public function updateExpiredQuotations(): int
    {
        return GrowFinanceQuotationModel::query()
            ->whereIn('status', [QuotationStatus::DRAFT->value, QuotationStatus::SENT->value])
            ->whereNotNull('valid_until')
            ->where('valid_until', '<', now())
            ->update(['status' => QuotationStatus::EXPIRED->value]);
    }

    /**
     * Duplicate a quotation
     */
    public function duplicate(GrowFinanceQuotationModel $quotation): GrowFinanceQuotationModel
    {
        return DB::transaction(function () use ($quotation) {
            $quotationNumber = $this->generateQuotationNumber($quotation->business_id);

            $newQuotation = GrowFinanceQuotationModel::create([
                'business_id' => $quotation->business_id,
                'customer_id' => $quotation->customer_id,
                'template_id' => $quotation->template_id,
                'quotation_number' => $quotationNumber,
                'quotation_date' => now(),
                'valid_until' => now()->addDays(30),
                'status' => QuotationStatus::DRAFT,
                'subtotal' => $quotation->subtotal,
                'total_amount' => $quotation->total_amount,
                'notes' => $quotation->notes,
                'terms' => $quotation->terms,
                'subject' => $quotation->subject,
            ]);

            foreach ($quotation->items as $item) {
                GrowFinanceQuotationItemModel::create([
                    'quotation_id' => $newQuotation->id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'line_total' => $item->line_total,
                ]);
            }

            return $newQuotation->load('items', 'customer');
        });
    }
}
