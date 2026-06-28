<?php

namespace App\Domain\GrowFinance\Services;

use App\Infrastructure\Persistence\Eloquent\GrowFinanceInvoiceModel;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfInvoiceService
{
    /**
     * Generate PDF for an invoice
     */
    public function generate(GrowFinanceInvoiceModel $invoice, User $business): string
    {
        $invoice->load(['customer', 'items']);

        $data = [
            'invoice' => $invoice,
            'business' => $this->getBusinessDetails($business),
            'items' => $invoice->items,
            'customer' => $invoice->customer,
            'generated_at' => now()->format('F j, Y'),
        ];

        $pdf = Pdf::loadView('pdf.growfinance.invoice', $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->output();
    }

    /**
     * Generate and store PDF, return path
     */
    public function generateAndStore(GrowFinanceInvoiceModel $invoice, User $business): string
    {
        $pdfContent = $this->generate($invoice, $business);

        $filename = "invoices/{$business->id}/{$invoice->invoice_number}.pdf";
        Storage::disk('local')->put($filename, $pdfContent);

        return $filename;
    }

    /**
     * Stream PDF for download
     */
    public function download(GrowFinanceInvoiceModel $invoice, User $business)
    {
        $invoice->load(['customer', 'items']);

        $data = [
            'invoice' => $invoice,
            'business' => $this->getBusinessDetails($business),
            'items' => $invoice->items,
            'customer' => $invoice->customer,
            'generated_at' => now()->format('F j, Y'),
        ];

        $pdf = Pdf::loadView('pdf.growfinance.invoice', $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("Invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Get business details for invoice header
     */
    private function getBusinessDetails(User $business): array
    {
        // Try to get from user's financial profile first
        $profile = $business->financialProfile;

        return [
            'name' => $profile?->business_name ?? $business->name,
            'email' => $business->email,
            'phone' => $profile?->phone ?? $business->phone ?? '',
            'address' => $profile?->address ?? '',
            'tax_number' => $profile?->tax_number ?? '',
            'logo_url' => $profile?->logo_url ?? null,
        ];
    }
}
