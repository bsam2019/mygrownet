<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\Invoice;
use App\Domain\GrowFinance\Entities\InvoiceItem;
use App\Domain\GrowFinance\Entities\Quotation;
use App\Domain\GrowFinance\Entities\QuotationItem;
use App\Domain\GrowFinance\Repositories\InvoiceItemRepositoryInterface;
use App\Domain\GrowFinance\Repositories\InvoiceRepositoryInterface;
use App\Domain\GrowFinance\Repositories\QuotationItemRepositoryInterface;
use App\Domain\GrowFinance\Repositories\QuotationRepositoryInterface;
use App\Domain\GrowFinance\ValueObjects\InvoiceStatus;
use App\Domain\GrowFinance\ValueObjects\QuotationStatus;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class QuotationService
{
    public function __construct(
        private QuotationRepositoryInterface $quotationRepo,
        private QuotationItemRepositoryInterface $quotationItemRepo,
        private InvoiceRepositoryInterface $invoiceRepo,
        private InvoiceItemRepositoryInterface $invoiceItemRepo,
    ) {}

    public function generateQuotationNumber(int $businessId): string
    {
        $quotations = $this->quotationRepo->findByBusiness($businessId);
        $maxNumber = 0;

        foreach ($quotations as $q) {
            $num = (int) substr($q->quotationNumber ?? 'QUO-0', 4);
            if ($num > $maxNumber) {
                $maxNumber = $num;
            }
        }

        $nextNumber = $maxNumber + 1;

        return 'QUO-' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function create(int $businessId, array $data): array
    {
        return DB::transaction(function () use ($businessId, $data) {
            $quotationNumber = $this->generateQuotationNumber($businessId);
            $subtotal = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);

            $quotation = new Quotation(
                id: null,
                businessId: $businessId,
                customerId: $data['customer_id'] ?? null,
                templateId: $data['template_id'] ?? null,
                quotationNumber: $quotationNumber,
                quotationDate: isset($data['quotation_date']) ? new DateTimeImmutable($data['quotation_date']) : new DateTimeImmutable(),
                validUntil: isset($data['valid_until']) ? new DateTimeImmutable($data['valid_until']) : null,
                status: QuotationStatus::DRAFT,
                subtotal: $subtotal,
                taxAmount: 0,
                discountAmount: 0,
                totalAmount: $subtotal,
                notes: $data['notes'] ?? null,
                terms: $data['terms'] ?? null,
                subject: $data['subject'] ?? null,
                convertedInvoiceId: null,
                sentAt: null,
                acceptedAt: null,
                rejectedAt: null,
                rejectionReason: null,
                createdAt: null,
                updatedAt: null,
            );

            $saved = $this->quotationRepo->save($quotation);

            foreach ($data['items'] as $item) {
                $qi = new QuotationItem(
                    id: null,
                    quotationId: $saved->id,
                    description: $item['description'],
                    quantity: $item['quantity'],
                    unitPrice: $item['unit_price'],
                    taxRate: $item['tax_rate'] ?? 0,
                    discountRate: $item['discount_rate'] ?? 0,
                    lineTotal: $item['quantity'] * $item['unit_price'],
                    createdAt: null,
                    updatedAt: null,
                );
                $this->quotationItemRepo->save($qi);
            }

            return $saved->toArray();
        });
    }

    public function update(int $quotationId, array $data): array
    {
        return DB::transaction(function () use ($quotationId, $data) {
            $quotation = $this->quotationRepo->findById($quotationId);
            $subtotal = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);

            $updated = new Quotation(
                id: $quotationId,
                businessId: $quotation->businessId,
                customerId: $data['customer_id'] ?? $quotation->customerId,
                templateId: $data['template_id'] ?? $quotation->templateId,
                quotationNumber: $quotation->quotationNumber,
                quotationDate: isset($data['quotation_date']) ? new DateTimeImmutable($data['quotation_date']) : $quotation->quotationDate,
                validUntil: isset($data['valid_until']) ? new DateTimeImmutable($data['valid_until']) : $quotation->validUntil,
                status: $quotation->status,
                subtotal: $subtotal,
                taxAmount: $quotation->taxAmount,
                discountAmount: $quotation->discountAmount,
                totalAmount: $subtotal,
                notes: $data['notes'] ?? $quotation->notes,
                terms: $data['terms'] ?? $quotation->terms,
                subject: $data['subject'] ?? $quotation->subject,
                convertedInvoiceId: $quotation->convertedInvoiceId,
                sentAt: $quotation->sentAt,
                acceptedAt: $quotation->acceptedAt,
                rejectedAt: $quotation->rejectedAt,
                rejectionReason: $quotation->rejectionReason,
                createdAt: $quotation->createdAt,
                updatedAt: null,
            );

            $saved = $this->quotationRepo->save($updated);

            DB::table('growfinance_quotation_items')
                ->where('quotation_id', $quotationId)
                ->delete();

            foreach ($data['items'] as $item) {
                $qi = new QuotationItem(
                    id: null,
                    quotationId: $quotationId,
                    description: $item['description'],
                    quantity: $item['quantity'],
                    unitPrice: $item['unit_price'],
                    taxRate: $item['tax_rate'] ?? 0,
                    discountRate: $item['discount_rate'] ?? 0,
                    lineTotal: $item['quantity'] * $item['unit_price'],
                    createdAt: null,
                    updatedAt: null,
                );
                $this->quotationItemRepo->save($qi);
            }

            return $saved->toArray();
        });
    }

    public function send(int $quotationId): array
    {
        $quotation = $this->quotationRepo->findById($quotationId);

        $updated = new Quotation(
            id: $quotationId,
            businessId: $quotation->businessId,
            customerId: $quotation->customerId,
            templateId: $quotation->templateId,
            quotationNumber: $quotation->quotationNumber,
            quotationDate: $quotation->quotationDate,
            validUntil: $quotation->validUntil,
            status: QuotationStatus::SENT,
            subtotal: $quotation->subtotal,
            taxAmount: $quotation->taxAmount,
            discountAmount: $quotation->discountAmount,
            totalAmount: $quotation->totalAmount,
            notes: $quotation->notes,
            terms: $quotation->terms,
            subject: $quotation->subject,
            convertedInvoiceId: $quotation->convertedInvoiceId,
            sentAt: new DateTimeImmutable(),
            acceptedAt: $quotation->acceptedAt,
            rejectedAt: $quotation->rejectedAt,
            rejectionReason: $quotation->rejectionReason,
            createdAt: $quotation->createdAt,
            updatedAt: null,
        );

        return $this->quotationRepo->save($updated)->toArray();
    }

    public function accept(int $quotationId): array
    {
        $quotation = $this->quotationRepo->findById($quotationId);

        $updated = new Quotation(
            id: $quotationId,
            businessId: $quotation->businessId,
            customerId: $quotation->customerId,
            templateId: $quotation->templateId,
            quotationNumber: $quotation->quotationNumber,
            quotationDate: $quotation->quotationDate,
            validUntil: $quotation->validUntil,
            status: QuotationStatus::ACCEPTED,
            subtotal: $quotation->subtotal,
            taxAmount: $quotation->taxAmount,
            discountAmount: $quotation->discountAmount,
            totalAmount: $quotation->totalAmount,
            notes: $quotation->notes,
            terms: $quotation->terms,
            subject: $quotation->subject,
            convertedInvoiceId: $quotation->convertedInvoiceId,
            sentAt: $quotation->sentAt,
            acceptedAt: new DateTimeImmutable(),
            rejectedAt: $quotation->rejectedAt,
            rejectionReason: $quotation->rejectionReason,
            createdAt: $quotation->createdAt,
            updatedAt: null,
        );

        return $this->quotationRepo->save($updated)->toArray();
    }

    public function reject(int $quotationId, ?string $reason = null): array
    {
        $quotation = $this->quotationRepo->findById($quotationId);

        $updated = new Quotation(
            id: $quotationId,
            businessId: $quotation->businessId,
            customerId: $quotation->customerId,
            templateId: $quotation->templateId,
            quotationNumber: $quotation->quotationNumber,
            quotationDate: $quotation->quotationDate,
            validUntil: $quotation->validUntil,
            status: QuotationStatus::REJECTED,
            subtotal: $quotation->subtotal,
            taxAmount: $quotation->taxAmount,
            discountAmount: $quotation->discountAmount,
            totalAmount: $quotation->totalAmount,
            notes: $quotation->notes,
            terms: $quotation->terms,
            subject: $quotation->subject,
            convertedInvoiceId: $quotation->convertedInvoiceId,
            sentAt: $quotation->sentAt,
            acceptedAt: $quotation->acceptedAt,
            rejectedAt: new DateTimeImmutable(),
            rejectionReason: $reason,
            createdAt: $quotation->createdAt,
            updatedAt: null,
        );

        return $this->quotationRepo->save($updated)->toArray();
    }

    public function markExpired(int $quotationId): array
    {
        $quotation = $this->quotationRepo->findById($quotationId);

        $updated = new Quotation(
            id: $quotationId,
            businessId: $quotation->businessId,
            customerId: $quotation->customerId,
            templateId: $quotation->templateId,
            quotationNumber: $quotation->quotationNumber,
            quotationDate: $quotation->quotationDate,
            validUntil: $quotation->validUntil,
            status: QuotationStatus::EXPIRED,
            subtotal: $quotation->subtotal,
            taxAmount: $quotation->taxAmount,
            discountAmount: $quotation->discountAmount,
            totalAmount: $quotation->totalAmount,
            notes: $quotation->notes,
            terms: $quotation->terms,
            subject: $quotation->subject,
            convertedInvoiceId: $quotation->convertedInvoiceId,
            sentAt: $quotation->sentAt,
            acceptedAt: $quotation->acceptedAt,
            rejectedAt: $quotation->rejectedAt,
            rejectionReason: $quotation->rejectionReason,
            createdAt: $quotation->createdAt,
            updatedAt: null,
        );

        return $this->quotationRepo->save($updated)->toArray();
    }

    /**
     * Convert an accepted quotation to an invoice
     */
    public function convertToInvoice(int $quotationId, int $businessId): array
    {
        $quotation = $this->quotationRepo->findById($quotationId);

        if ($quotation->status !== QuotationStatus::ACCEPTED) {
            throw new \InvalidArgumentException('Only accepted quotations can be converted to invoices.');
        }

        if ($quotation->convertedInvoiceId) {
            throw new \InvalidArgumentException('This quotation has already been converted to an invoice.');
        }

        return DB::transaction(function () use ($quotation, $businessId, $quotationId) {
            $invoices = $this->invoiceRepo->findByBusiness($businessId);
            $maxNumber = 0;

            foreach ($invoices as $inv) {
                $num = (int) substr($inv->invoiceNumber ?? 'INV-0', 4);
                if ($num > $maxNumber) {
                    $maxNumber = $num;
                }
            }

            $nextNumber = $maxNumber + 1;
            $invoiceNumber = 'INV-' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);

            $invoice = new Invoice(
                id: null,
                businessId: $businessId,
                customerId: $quotation->customerId,
                templateId: $quotation->templateId,
                invoiceNumber: $invoiceNumber,
                invoiceDate: new DateTimeImmutable(),
                dueDate: (new DateTimeImmutable())->modify('+30 days'),
                status: InvoiceStatus::DRAFT,
                subtotal: $quotation->subtotal,
                taxAmount: $quotation->taxAmount,
                discountAmount: $quotation->discountAmount,
                totalAmount: $quotation->totalAmount,
                amountPaid: 0,
                notes: $quotation->notes,
                terms: $quotation->terms,
                createdAt: null,
                updatedAt: null,
            );

            $saved = $this->invoiceRepo->save($invoice);

            $items = $this->quotationItemRepo->findByQuotation($quotationId);

            foreach ($items as $item) {
                $ii = new InvoiceItem(
                    id: null,
                    invoiceId: $saved->id,
                    description: $item->description,
                    quantity: $item->quantity,
                    unitPrice: $item->unitPrice,
                    taxRate: $item->taxRate,
                    discountRate: $item->discountRate,
                    lineTotal: $item->lineTotal,
                    createdAt: null,
                    updatedAt: null,
                );
                $this->invoiceItemRepo->save($ii);
            }

            $updatedQuotation = new Quotation(
                id: $quotationId,
                businessId: $quotation->businessId,
                customerId: $quotation->customerId,
                templateId: $quotation->templateId,
                quotationNumber: $quotation->quotationNumber,
                quotationDate: $quotation->quotationDate,
                validUntil: $quotation->validUntil,
                status: QuotationStatus::CONVERTED,
                subtotal: $quotation->subtotal,
                taxAmount: $quotation->taxAmount,
                discountAmount: $quotation->discountAmount,
                totalAmount: $quotation->totalAmount,
                notes: $quotation->notes,
                terms: $quotation->terms,
                subject: $quotation->subject,
                convertedInvoiceId: $saved->id,
                sentAt: $quotation->sentAt,
                acceptedAt: $quotation->acceptedAt,
                rejectedAt: $quotation->rejectedAt,
                rejectionReason: $quotation->rejectionReason,
                createdAt: $quotation->createdAt,
                updatedAt: null,
            );

            $this->quotationRepo->save($updatedQuotation);

            return $saved->toArray();
        });
    }

    /**
     * Update expired quotations
     */
    public function updateExpiredQuotations(): int
    {
        return DB::table('growfinance_quotations')
            ->whereIn('status', [QuotationStatus::DRAFT->value, QuotationStatus::SENT->value])
            ->whereNotNull('valid_until')
            ->where('valid_until', '<', now())
            ->update(['status' => QuotationStatus::EXPIRED->value]);
    }

    /**
     * Duplicate a quotation
     */
    public function duplicate(int $quotationId): array
    {
        return DB::transaction(function () use ($quotationId) {
            $quotation = $this->quotationRepo->findById($quotationId);
            $quotationNumber = $this->generateQuotationNumber($quotation->businessId);

            $newQuotation = new Quotation(
                id: null,
                businessId: $quotation->businessId,
                customerId: $quotation->customerId,
                templateId: $quotation->templateId,
                quotationNumber: $quotationNumber,
                quotationDate: new DateTimeImmutable(),
                validUntil: (new DateTimeImmutable())->modify('+30 days'),
                status: QuotationStatus::DRAFT,
                subtotal: $quotation->subtotal,
                taxAmount: $quotation->taxAmount,
                discountAmount: $quotation->discountAmount,
                totalAmount: $quotation->totalAmount,
                notes: $quotation->notes,
                terms: $quotation->terms,
                subject: $quotation->subject,
                convertedInvoiceId: null,
                sentAt: null,
                acceptedAt: null,
                rejectedAt: null,
                rejectionReason: null,
                createdAt: null,
                updatedAt: null,
            );

            $saved = $this->quotationRepo->save($newQuotation);

            $items = $this->quotationItemRepo->findByQuotation($quotationId);

            foreach ($items as $item) {
                $qi = new QuotationItem(
                    id: null,
                    quotationId: $saved->id,
                    description: $item->description,
                    quantity: $item->quantity,
                    unitPrice: $item->unitPrice,
                    taxRate: $item->taxRate,
                    discountRate: $item->discountRate,
                    lineTotal: $item->lineTotal,
                    createdAt: null,
                    updatedAt: null,
                );
                $this->quotationItemRepo->save($qi);
            }

            return $saved->toArray();
        });
    }
}
