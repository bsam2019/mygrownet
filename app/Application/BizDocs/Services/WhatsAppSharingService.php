<?php

namespace App\Application\BizDocs\Services;

use App\Domain\BizDocs\CustomerManagement\Entities\Customer;
use App\Domain\BizDocs\DocumentManagement\Entities\Document;

class WhatsAppSharingService
{
    public function __construct(
        private readonly PdfGenerationService $pdfGenerationService
    ) {
    }

    public function generateWhatsAppLink(Document $document, Customer $customer, string $pdfUrl): string
    {
        $phone = $this->formatPhoneNumber($customer->phone());
        $message = $this->generateMessage($document, $pdfUrl);

        return "https://wa.me/{$phone}?text=" . urlencode($message);
    }

    private function formatPhoneNumber(?string $phone): string
    {
        if (!$phone) {
            throw new \InvalidArgumentException('Customer phone number is required for WhatsApp sharing');
        }

        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Add country code if not present (assuming Zambia +260)
        if (!str_starts_with($phone, '260')) {
            // Remove leading zero if present
            $phone = ltrim($phone, '0');
            $phone = '260' . $phone;
        }

        return $phone;
    }

    private function generateMessage(Document $document, string $pdfUrl): string
    {
        $documentType = ucfirst(str_replace('_', ' ', $document->type()->value()));
        $totals = $document->calculateTotals();
        $amount = $document->currency() . ' ' . number_format($totals['grand_total']->amount() / 100, 2);

        $message = "Hello,\n\n";
        $message .= "Please find your {$documentType} #{$document->number()->value()} attached.\n\n";
        $message .= "Total: {$amount}\n\n";
        $message .= "Download: {$pdfUrl}\n\n";
        $message .= "Thank you for your business!";

        return $message;
    }
}
