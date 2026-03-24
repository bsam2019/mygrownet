<?php

namespace App\Application\BizDocs\Services;

use App\Domain\BizDocs\BusinessIdentity\Entities\BusinessProfile;
use App\Domain\BizDocs\TemplateManagement\Entities\DocumentTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class StationeryGeneratorService
{
    public function generate(
        BusinessProfile $businessProfile,
        DocumentTemplate $template,
        string $documentType,
        int $quantity,
        int $documentsPerPage,
        string $startingNumber,
    ): string {
        // Parse starting number (e.g., "INV-2026-0001" -> 1)
        $startNumber = $this->parseDocumentNumber($startingNumber);
        
        // Calculate total pages needed
        $totalPages = (int) ceil($quantity / $documentsPerPage);
        
        // Generate document numbers
        $documentNumbers = [];
        for ($i = 0; $i < $quantity; $i++) {
            $documentNumbers[] = $this->formatDocumentNumber(
                $documentType,
                $startNumber + $i,
                date('Y')
            );
        }
        
        // Prepare data for PDF
        $data = [
            'businessProfile' => $businessProfile,
            'template' => $template,
            'documentType' => $documentType,
            'documentNumbers' => $documentNumbers,
            'documentsPerPage' => $documentsPerPage,
            'totalPages' => $totalPages,
            'logoPath' => $this->getLogoPath($businessProfile),
            'signaturePath' => $this->getSignaturePath($businessProfile),
            'isPdf' => true,
        ];
        
        // Generate PDF
        $pdf = Pdf::loadView('bizdocs.stationery.template', $data);
        $pdf->setPaper('a4', 'portrait');
        
        // Save PDF
        $filename = sprintf(
            'stationery_%s_%s_%d_docs.pdf',
            $documentType,
            date('Y-m-d_His'),
            $quantity
        );
        
        $path = "bizdocs/stationery/{$businessProfile->userId()}/{$filename}";
        Storage::disk('public')->put($path, $pdf->output());
        
        return $path;
    }
    
    private function parseDocumentNumber(string $documentNumber): int
    {
        // Extract number from format like "INV-2026-0001"
        $parts = explode('-', $documentNumber);
        return (int) end($parts);
    }
    
    private function formatDocumentNumber(string $type, int $number, string $year): string
    {
        $prefix = $this->getDocumentPrefix($type);
        return sprintf('%s-%s-%04d', $prefix, $year, $number);
    }
    
    private function getDocumentPrefix(string $type): string
    {
        return match($type) {
            'invoice' => 'INV',
            'receipt' => 'RCPT',
            'quotation' => 'QTN',
            'delivery_note' => 'DN',
            'proforma_invoice' => 'PI',
            'credit_note' => 'CN',
            'debit_note' => 'DBN',
            'purchase_order' => 'PO',
            default => 'DOC',
        };
    }
    
    private function getLogoPath(BusinessProfile $businessProfile): ?string
    {
        if (!$businessProfile->logo()) {
            return null;
        }
        
        // Check if it's a full URL (from Spaces)
        if (filter_var($businessProfile->logo(), FILTER_VALIDATE_URL)) {
            return $businessProfile->logo();
        }
        
        // Local storage path
        return Storage::disk('public')->path($businessProfile->logo());
    }
    
    private function getSignaturePath(BusinessProfile $businessProfile): ?string
    {
        if (!$businessProfile->signatureImage()) {
            return null;
        }
        
        // Check if it's a full URL (from Spaces)
        if (filter_var($businessProfile->signatureImage(), FILTER_VALIDATE_URL)) {
            return $businessProfile->signatureImage();
        }
        
        // Local storage path
        return Storage::disk('public')->path($businessProfile->signatureImage());
    }
}
