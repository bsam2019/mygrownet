<?php

namespace App\Domain\CMS\BizDocs\Services;

use App\Domain\BizDocs\DocumentManagement\Entities\Document;
use App\Domain\BizDocs\DocumentManagement\Entities\DocumentItem;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\DocumentNumber;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\DocumentType;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\Money;
use App\Domain\CMS\BizDocs\Exceptions\DocumentConversionException;
use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\QuotationModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PaymentModel;

class DocumentConversionService
{
    public function invoiceToDocument(InvoiceModel $invoice): Document
    {
        try {
            $this->validateInvoice($invoice);
            $invoice->loadMissing(['company']);

            $currency = $invoice->company?->settings['currency'] ?? 'ZMW';
            $document = Document::create(
                businessId: $invoice->company_id,
                customerId: $invoice->customer_id,
                type: DocumentType::fromString('invoice'),
                number: DocumentNumber::fromString($invoice->invoice_number),
                issueDate: new \DateTimeImmutable($invoice->invoice_date),
                currency: $currency,
                dueDate: $invoice->due_date ? new \DateTimeImmutable($invoice->due_date) : null,
                notes: $invoice->notes,
                terms: $invoice->terms,
                discountType: $invoice->discount_type ?? 'amount',
                discountValue: $invoice->discount_value ?? 0,
                collectTax: true
            );

            foreach ($invoice->items as $index => $item) {
                $document->addItem($this->convertInvoiceItem($item, $index, $currency));
            }

            return $document;
        } catch (\Exception $e) {
            throw DocumentConversionException::conversionFailed('invoice', $e);
        }
    }

    public function quotationToDocument(QuotationModel $quotation): Document
    {
        try {
            $this->validateQuotation($quotation);
            $quotation->loadMissing(['company']);

            $currency = $quotation->company?->settings['currency'] ?? 'ZMW';
            $document = Document::create(
                businessId: $quotation->company_id,
                customerId: $quotation->customer_id,
                type: DocumentType::fromString('quotation'),
                number: DocumentNumber::fromString($quotation->quotation_number),
                issueDate: new \DateTimeImmutable($quotation->quotation_date),
                currency: $currency,
                validityDate: $quotation->valid_until ? new \DateTimeImmutable($quotation->valid_until) : null,
                notes: $quotation->notes,
                terms: $quotation->terms,
                discountType: $quotation->discount_type ?? 'amount',
                discountValue: $quotation->discount_value ?? 0,
                collectTax: true
            );

            foreach ($quotation->items as $index => $item) {
                $document->addItem($this->convertQuotationItem($item, $index, $currency));
            }

            return $document;
        } catch (\Exception $e) {
            throw DocumentConversionException::conversionFailed('quotation', $e);
        }
    }

    public function paymentToDocument(PaymentModel $payment): Document
    {
        try {
            $this->validatePayment($payment);
            $payment->loadMissing(['company']);

            $currency = $payment->company?->settings['currency'] ?? 'ZMW';
            $document = Document::create(
                businessId: $payment->company_id,
                customerId: $payment->customer_id ?? 0,
                type: DocumentType::fromString('receipt'),
                number: DocumentNumber::fromString($payment->payment_number ?? 'RCPT-' . $payment->id),
                issueDate: new \DateTimeImmutable($payment->payment_date),
                currency: $currency,
                notes: $payment->notes,
                collectTax: false
            );

            $document->addItem(DocumentItem::create(
                description: $payment->description ?? 'Payment received',
                quantity: 1,
                unitPrice: Money::fromAmount((int)($payment->amount * 100), $currency),
                taxRate: 0,
                sortOrder: 0
            ));

            return $document;
        } catch (\Exception $e) {
            throw DocumentConversionException::conversionFailed('receipt', $e);
        }
    }

    private function convertInvoiceItem($item, int $index, string $currency = 'ZMW'): DocumentItem
    {
        return DocumentItem::create(
            description: $item->description ?? '',
            quantity: $item->quantity ?? 1,
            unitPrice: Money::fromAmount((int)($item->unit_price * 100), $currency),
            taxRate: $item->tax_rate ?? 0,
            discountAmount: Money::fromAmount((int)(($item->discount_amount ?? 0) * 100), $currency),
            sortOrder: $index,
            dimensions: $item->dimensions ?? null,
            dimensionsValue: $item->dimensions_value ?? 1.0
        );
    }

    private function convertQuotationItem($item, int $index, string $currency = 'ZMW'): DocumentItem
    {
        return DocumentItem::create(
            description: $item->description ?? '',
            quantity: $item->quantity ?? 1,
            unitPrice: Money::fromAmount((int)($item->unit_price * 100), $currency),
            taxRate: $item->tax_rate ?? 0,
            discountAmount: Money::fromAmount((int)(($item->discount_amount ?? 0) * 100), $currency),
            sortOrder: $index,
            dimensions: $item->dimensions ?? null,
            dimensionsValue: $item->dimensions_value ?? 1.0
        );
    }

    private function validateInvoice(InvoiceModel $invoice): void
    {
        if (!$invoice->company_id) {
            throw DocumentConversionException::missingRequiredField('company_id', 'invoice');
        }

        if (!$invoice->customer_id) {
            throw DocumentConversionException::missingRequiredField('customer_id', 'invoice');
        }

        if (!$invoice->invoice_number) {
            throw DocumentConversionException::missingRequiredField('invoice_number', 'invoice');
        }

        if (!$invoice->invoice_date) {
            throw DocumentConversionException::missingRequiredField('invoice_date', 'invoice');
        }
    }

    private function validateQuotation(QuotationModel $quotation): void
    {
        if (!$quotation->company_id) {
            throw DocumentConversionException::missingRequiredField('company_id', 'quotation');
        }

        if (!$quotation->customer_id) {
            throw DocumentConversionException::missingRequiredField('customer_id', 'quotation');
        }

        if (!$quotation->quotation_number) {
            throw DocumentConversionException::missingRequiredField('quotation_number', 'quotation');
        }

        if (!$quotation->quotation_date) {
            throw DocumentConversionException::missingRequiredField('quotation_date', 'quotation');
        }
    }

    private function validatePayment(PaymentModel $payment): void
    {
        if (!$payment->company_id) {
            throw DocumentConversionException::missingRequiredField('company_id', 'payment');
        }

        if (!$payment->amount) {
            throw DocumentConversionException::missingRequiredField('amount', 'payment');
        }

        if (!$payment->payment_date) {
            throw DocumentConversionException::missingRequiredField('payment_date', 'payment');
        }
    }
}
