<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\Services;

use App\Domain\QuickInvoice\Entities\Document;
use App\Mail\QuickInvoiceMail;
use Illuminate\Support\Facades\Mail;

class ShareService
{
    public function __construct(
        private readonly PdfGeneratorService $pdfGenerator
    ) {}

    public function generateWhatsAppLink(Document $document, string $pdfUrl): string
    {
        $clientPhone = $document->clientInfo()->phone();
        
        // Clean phone number (remove spaces, dashes, etc.)
        $phone = preg_replace('/[^0-9]/', '', $clientPhone ?? '');
        
        // Add country code if not present (default to Zambia +260)
        if (strlen($phone) === 9) {
            $phone = '260' . $phone;
        } elseif (str_starts_with($phone, '0')) {
            $phone = '260' . substr($phone, 1);
        }

        $message = $this->buildWhatsAppMessage($document, $pdfUrl);
        $encodedMessage = urlencode($message);

        return "https://wa.me/{$phone}?text={$encodedMessage}";
    }

    private function buildWhatsAppMessage(Document $document, string $pdfUrl): string
    {
        $type = $document->type()->label();
        $number = $document->documentNumber();
        $businessName = $document->businessInfo()->name();
        $total = $document->total()->format();
        $clientName = $document->clientInfo()->name();

        $message = "Hello {$clientName},\n\n";
        $message .= "Please find your {$type} from {$businessName}.\n\n";
        $message .= "📄 {$type} #{$number}\n";
        $message .= "💰 Total: {$total}\n";
        
        if ($document->dueDate()) {
            $message .= "📅 Due: {$document->dueDate()->format('d M Y')}\n";
        }

        $message .= "\n📎 Download: {$pdfUrl}\n\n";
        $message .= "Thank you for your business!\n";
        $message .= "— {$businessName}";

        return $message;
    }

    public function sendEmail(Document $document, string $pdfUrl, ?string $customMessage = null): bool
    {
        $clientEmail = $document->clientInfo()->email();
        
        if (!$clientEmail) {
            return false;
        }

        try {
            Mail::to($clientEmail)->send(new QuickInvoiceMail($document, $pdfUrl, $customMessage));
            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    public function generateShareData(Document $document, string $pdfUrl): array
    {
        return [
            'pdf_url' => $pdfUrl,
            'whatsapp_link' => $this->generateWhatsAppLink($document, $pdfUrl),
            'can_email' => !empty($document->clientInfo()->email()),
            'document_number' => $document->documentNumber(),
            'document_type' => $document->type()->label(),
            'total' => $document->total()->format(),
        ];
    }
}
