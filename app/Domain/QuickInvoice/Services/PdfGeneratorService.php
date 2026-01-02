<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\Services;

use App\Domain\QuickInvoice\Entities\Document;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfGeneratorService
{
    private const TEMPLATES = ['classic', 'modern', 'minimal', 'professional', 'bold'];

    public function generate(Document $document): string
    {
        $template = $this->getTemplateName($document);
        $documentData = $this->prepareDocumentData($document);
        
        $pdf = Pdf::loadView("pdf.quick-invoice.{$template}", [
            'document' => $documentData,
        ]);

        $pdf->setPaper('A4', 'portrait');
        
        // Enable remote images for DomPDF
        $pdf->setOption('isRemoteEnabled', true);

        $filename = $this->generateFilename($document);
        $path = "quick-invoice/pdfs/{$filename}";
        
        Storage::disk('public')->put($path, $pdf->output());

        return Storage::disk('public')->url($path);
    }

    public function download(Document $document)
    {
        $template = $this->getTemplateName($document);
        $documentData = $this->prepareDocumentData($document);
        
        $pdf = Pdf::loadView("pdf.quick-invoice.{$template}", [
            'document' => $documentData,
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf->download($this->generateFilename($document));
    }

    public function stream(Document $document)
    {
        $template = $this->getTemplateName($document);
        $documentData = $this->prepareDocumentData($document);
        
        $pdf = Pdf::loadView("pdf.quick-invoice.{$template}", [
            'document' => $documentData,
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf->stream($this->generateFilename($document));
    }

    /**
     * Prepare document data with base64 encoded images for PDF rendering
     */
    private function prepareDocumentData(Document $document): array
    {
        $data = $document->toArray();
        
        // Convert logo URL to base64 for PDF rendering
        if (!empty($data['business_info']['logo'])) {
            $data['business_info']['logo'] = $this->convertImageToBase64($data['business_info']['logo']);
        }
        
        // Convert signature URL to base64 for PDF rendering
        if (!empty($data['signature'])) {
            $data['signature'] = $this->convertImageToBase64($data['signature']);
        }
        
        return $data;
    }

    /**
     * Convert image URL to base64 data URI for PDF embedding
     */
    private function convertImageToBase64(?string $imageUrl): ?string
    {
        if (empty($imageUrl)) {
            return null;
        }

        try {
            // Check if it's already a base64 string
            if (str_starts_with($imageUrl, 'data:image')) {
                return $imageUrl;
            }

            // Try to get the file from storage
            $path = $this->getStoragePathFromUrl($imageUrl);
            
            if ($path && Storage::disk('public')->exists($path)) {
                $contents = Storage::disk('public')->get($path);
                $mimeType = $this->getMimeType($path);
                return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
            }

            // Fallback: try to fetch from URL directly
            if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                $contents = @file_get_contents($imageUrl);
                if ($contents !== false) {
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->buffer($contents) ?: 'image/png';
                    return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
                }
            }

            // Try as absolute path
            $absolutePath = public_path(str_replace('/storage/', 'storage/', parse_url($imageUrl, PHP_URL_PATH) ?? ''));
            if (file_exists($absolutePath)) {
                $contents = file_get_contents($absolutePath);
                $mimeType = mime_content_type($absolutePath) ?: 'image/png';
                return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
            }

            return null;
        } catch (\Exception $e) {
            \Log::warning('Failed to convert image to base64: ' . $e->getMessage(), ['url' => $imageUrl]);
            return null;
        }
    }

    /**
     * Extract storage path from URL
     */
    private function getStoragePathFromUrl(string $url): ?string
    {
        // Handle URLs like http://127.0.0.1:8001/storage/quick-invoice/logos/...
        if (preg_match('#/storage/(.+)$#', $url, $matches)) {
            return $matches[1];
        }
        
        // Handle relative paths
        if (str_starts_with($url, 'quick-invoice/')) {
            return $url;
        }
        
        return null;
    }

    /**
     * Get MIME type from file path
     */
    private function getMimeType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'image/png',
        };
    }

    private function getTemplateName(Document $document): string
    {
        $template = $document->template()->value;
        
        // Fallback to classic if template doesn't exist
        if (!in_array($template, self::TEMPLATES)) {
            return 'classic';
        }

        return $template;
    }

    private function generateFilename(Document $document): string
    {
        $type = str_replace('_', '-', $document->type()->value);
        $number = $document->documentNumber()->value();
        return "{$type}-{$number}.pdf";
    }
}
