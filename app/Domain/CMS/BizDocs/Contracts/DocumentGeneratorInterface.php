<?php

namespace App\Domain\CMS\BizDocs\Contracts;

use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\QuotationModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PaymentModel;

interface DocumentGeneratorInterface
{
    /**
     * Generate PDF for an invoice
     *
     * @param InvoiceModel $invoice
     * @return string PDF content
     */
    public function generateInvoicePdf(InvoiceModel $invoice): string;

    /**
     * Generate PDF for a quotation
     *
     * @param QuotationModel $quotation
     * @return string PDF content
     */
    public function generateQuotationPdf(QuotationModel $quotation): string;

    /**
     * Generate PDF receipt for a payment
     *
     * @param PaymentModel $payment
     * @return string PDF content
     */
    public function generateReceiptPdf(PaymentModel $payment): string;

    /**
     * Generate print stationery
     *
     * @param array $config Configuration for stationery generation
     * @return string PDF content
     */
    public function generateStationery(array $config): string;
}
