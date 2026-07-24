<?php

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Repositories\InvoiceRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfInvoiceService
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepo
    ) {}

    /**
     * Generate PDF for an invoice
     */
    public function generate(int $invoiceId, array $businessData): string
    {
        $invoice = $this->invoiceRepo->findById($invoiceId);

        $data = [
            'invoice' => $invoice,
            'business' => $this->getBusinessDetails($businessData),
            'items' => [],
            'customer' => null,
            'generated_at' => now()->format('F j, Y'),
        ];

        $pdf = Pdf::loadView('pdf.growfinance.invoice', $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->output();
    }

    /**
     * Generate and store PDF, return path
     */
    public function generateAndStore(int $invoiceId, array $businessData): string
    {
        $invoice = $this->invoiceRepo->findById($invoiceId);

        $pdfContent = $this->generate($invoiceId, $businessData);

        $filename = "invoices/{$businessData['id']}/{$invoice->invoiceNumber}.pdf";
        Storage::disk('local')->put($filename, $pdfContent);

        return $filename;
    }

    /**
     * Stream PDF for download
     */
    public function download(int $invoiceId, array $businessData)
    {
        $invoice = $this->invoiceRepo->findById($invoiceId);

        $data = [
            'invoice' => $invoice,
            'business' => $this->getBusinessDetails($businessData),
            'items' => [],
            'customer' => null,
            'generated_at' => now()->format('F j, Y'),
        ];

        $pdf = Pdf::loadView('pdf.growfinance.invoice', $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("Invoice-{$invoice->invoiceNumber}.pdf");
    }

    /**
     * Get business details for invoice header
     */
    private function getBusinessDetails(array $businessData): array
    {
        return [
            'name' => $businessData['name'] ?? '',
            'email' => $businessData['email'] ?? '',
            'phone' => $businessData['phone'] ?? '',
            'address' => $businessData['address'] ?? '',
            'tax_number' => $businessData['tax_number'] ?? '',
            'logo_url' => $businessData['logo_url'] ?? null,
        ];
    }
}
