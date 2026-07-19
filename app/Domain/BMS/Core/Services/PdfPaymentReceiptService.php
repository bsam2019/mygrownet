<?php

declare(strict_types=1);

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\PaymentModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfPaymentReceiptService
{
    /**
     * Generate and download payment receipt PDF
     */
    public function downloadReceipt(int $paymentId): \Illuminate\Http\Response
    {
        $payment = PaymentModel::with(['company', 'customer', 'allocations.invoice', 'receivedBy.user'])
            ->findOrFail($paymentId);

        $pdf = $this->generatePdf($payment);

        return $pdf->download("receipt-{$payment->payment_number}.pdf");
    }

    /**
     * Generate and stream payment receipt PDF (preview)
     */
    public function streamReceipt(int $paymentId): \Illuminate\Http\Response
    {
        $payment = PaymentModel::with(['company', 'customer', 'allocations.invoice', 'receivedBy.user'])
            ->findOrFail($paymentId);

        $pdf = $this->generatePdf($payment);

        return $pdf->stream("receipt-{$payment->payment_number}.pdf");
    }

    /**
     * Generate and save payment receipt PDF to storage
     */
    public function saveReceipt(int $paymentId): string
    {
        $payment = PaymentModel::with(['company', 'customer', 'allocations.invoice', 'receivedBy.user'])
            ->findOrFail($paymentId);

        $pdf = $this->generatePdf($payment);

        $path = "cms/companies/{$payment->company_id}/receipts/receipt-{$payment->payment_number}.pdf";
        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Generate PDF from payment data
     */
    private function generatePdf(PaymentModel $payment): \Barryvdh\DomPDF\PDF
    {
        return Pdf::loadView('pdf.cms.receipt', [
            'payment' => $payment,
            'company' => $payment->company,
            'customer' => $payment->customer,
            'allocations' => $payment->allocations,
        ])->setPaper('a4');
    }
}
