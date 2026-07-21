<?php

namespace App\Domain\BMS\BizDocs\Contracts;

use App\Infrastructure\Persistence\Eloquent\BMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\BMS\MaterialPurchaseOrderModel;
use App\Infrastructure\Persistence\Eloquent\BMS\QuotationModel;
use App\Infrastructure\Persistence\Eloquent\BMS\PaymentModel;

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
     * Generate PDF for a purchase order
     */
    public function generatePurchaseOrderPdf(MaterialPurchaseOrderModel $purchaseOrder): string;

    /**
     * Generate print stationery
     *
     * @param array $config Configuration for stationery generation
     * @return string PDF content
     */
    public function generateStationery(array $config): string;
}
