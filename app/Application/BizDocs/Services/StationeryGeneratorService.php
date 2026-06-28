<?php

namespace App\Application\BizDocs\Services;

use App\Domain\BizDocs\BusinessIdentity\Entities\BusinessProfile;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class StationeryGeneratorService
{
    public function __construct(
        private readonly FileStorageService $fileStorageService
    ) {}

    public function generate(
        BusinessProfile $businessProfile,
        $templateModel,
        string $documentType,
        int $quantity,
        int $documentsPerPage,
        string $startingNumber,
        string $pageSize = 'A4',
        ?int $rowCount = null,
    ): string {
        try {
            // DEBUG: Verify type is preserved through the entire chain
            \Log::debug('StationeryGeneratorService::generate - Type check', [
                'documentsPerPage_value' => $documentsPerPage,
                'documentsPerPage_type' => gettype($documentsPerPage),
                'quantity_value' => $quantity,
                'quantity_type' => gettype($quantity),
            ]);
            
            // Increase execution time for large PDF generation
            set_time_limit(300); // 5 minutes
            
            // Increase memory limit for large PDFs
            ini_set('memory_limit', '512M');
            
            \Log::info('Starting stationery generation', [
                'quantity' => $quantity,
                'documents_per_page' => $documentsPerPage,
                'document_type' => $documentType,
            ]);
            
            // Parse starting number (e.g., "INV-2026-0001" -> 1)
            $startNumber = $this->parseDocumentNumber($startingNumber);
            
            // Calculate total pages needed
            $totalPages = (int) ceil($quantity / $documentsPerPage);
            
            \Log::info('Calculated pages', ['total_pages' => $totalPages]);
            
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
                'template' => $templateModel,
                'documentType' => $documentType,
                'documentNumbers' => $documentNumbers,
                'documentsPerPage' => (int) $documentsPerPage, // Explicit cast for template
                'pageSize' => $pageSize,
                'rowCount' => $rowCount,
                'totalPages' => $totalPages,
                'logoPath' => $this->getLogoPath($businessProfile),
                'signaturePath' => $this->getSignaturePath($businessProfile),
                'isPdf' => true,
            ];
            
            \Log::debug('Data prepared for PDF', [
                'documentsPerPage' => $data['documentsPerPage'],
                'documentsPerPage_type' => gettype($data['documentsPerPage']),
            ]);
            
            // Select template based on document type
            $viewPath = $this->getTemplateView($documentType);
            
            \Log::info('Loading PDF template', ['view_path' => $viewPath]);
            
            // Generate PDF with optimized settings
            $pdf = Pdf::loadView($viewPath, $data)
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'Arial',
                    'enable_php' => false, // Disabled - Blade already rendered all PHP
                    'enable_javascript' => false, // Disable for performance
                    'enable_css_float' => true,
                    'enable_automatic_breaks' => true,
                ]);
            
            // Set paper size based on selection
            $paperSize = strtolower($pageSize);
            if ($paperSize === 'custom') {
                $paperSize = 'a4'; // Default to A4 for custom (can be extended later)
            }
            $pdf->setPaper($paperSize, 'portrait');
            
            \Log::info('Generating PDF content');
            
            // Return PDF content directly without saving
            $pdfContent = $pdf->output();
            
            \Log::info('PDF generated successfully', ['size' => strlen($pdfContent)]);
            
            return $pdfContent;
        } catch (\Exception $e) {
            \Log::error('StationeryGeneratorService::generate failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
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
        
        try {
            $disk = $this->fileStorageService->getDisk();
            $logoPath = $businessProfile->logo();
            
            if ($disk === 'public') {
                // Local storage - use public_path
                $fullPath = public_path('storage/' . $logoPath);
                \Log::info('Using local logo path', ['path' => $fullPath, 'exists' => file_exists($fullPath)]);
                return $fullPath;
            }
            
            // Remote storage (DigitalOcean Spaces) - get public URL
            $url = $this->fileStorageService->getUrl($logoPath);
            \Log::info('Using remote logo URL', ['url' => $url]);
            return $url;
        } catch (\Exception $e) {
            \Log::error('Failed to load logo for stationery', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'logo_path' => $businessProfile->logo(),
            ]);
            return null;
        }
    }
    
    private function getSignaturePath(BusinessProfile $businessProfile): ?string
    {
        if (!$businessProfile->signatureImage()) {
            return null;
        }
        
        try {
            $disk = $this->fileStorageService->getDisk();
            $signaturePath = $businessProfile->signatureImage();
            
            if ($disk === 'public') {
                // Local storage - use public_path
                return public_path('storage/' . $signaturePath);
            }
            
            // Remote storage (DigitalOcean Spaces) - get public URL
            return $this->fileStorageService->getUrl($signaturePath);
        } catch (\Exception $e) {
            \Log::warning('Failed to load signature for stationery', [
                'error' => $e->getMessage(),
                'signature_path' => $businessProfile->signatureImage(),
            ]);
            return null;
        }
    }
    
    private function getMimeType(string $filePath, string $content): string
    {
        // Try to detect from file extension first
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
        ];
        
        if (isset($mimeTypes[$extension])) {
            return $mimeTypes[$extension];
        }
        
        // Fallback: detect from content using finfo
        try {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $detectedMime = $finfo->buffer($content);
            
            if ($detectedMime && str_starts_with($detectedMime, 'image/')) {
                return $detectedMime;
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to detect MIME type', ['error' => $e->getMessage()]);
        }
        
        // Default fallback
        return 'image/png';
    }
    
    private function getTemplateView(string $documentType): string
    {
        // Use simplified templates for better PDF generation
        return match($documentType) {
            'receipt' => 'bizdocs.stationery.receipt-simple',
            'invoice', 'quotation', 'proforma_invoice' => 'bizdocs.stationery.template',
            default => 'bizdocs.stationery.template',
        };
    }
}
