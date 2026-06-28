<?php

declare(strict_types=1);

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfInvoiceService
{
    /**
     * Generate PDF and return as download
     */
    public function download(InvoiceModel $invoice)
    {
        $pdf = $this->generate($invoice);
        
        return $pdf->download("Invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Generate PDF and stream to browser
     */
    public function stream(InvoiceModel $invoice)
    {
        $pdf = $this->generate($invoice);
        
        return $pdf->stream("Invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Generate PDF and save to storage
     */
    public function save(InvoiceModel $invoice): string
    {
        $pdf = $this->generate($invoice);
        
        $filename = "invoices/{$invoice->company_id}/Invoice-{$invoice->invoice_number}.pdf";
        Storage::disk('local')->put($filename, $pdf->output());
        
        return $filename;
    }

    /**
     * Generate PDF instance
     */
    private function generate(InvoiceModel $invoice)
    {
        $invoice->load(['customer', 'items', 'company']);
        
        $data = [
            'invoice' => $invoice,
            'company' => $invoice->company,
            'customer' => $invoice->customer,
            'items' => $invoice->items,
        ];

        return Pdf::loadView('pdf.cms.invoice', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 10)
            ->setOption('margin-right', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10);
    }
}
