<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\Services;

use App\Domain\QuickInvoice\Entities\Document;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

/**
 * PDF Generator Service - Generates PDFs on-demand (no storage)
 * 
 * This service generates PDFs dynamically when requested rather than
 * storing them, saving storage resources and ensuring documents
 * always reflect the latest template designs.
 */
class PdfGeneratorService
{
    private const TEMPLATES = ['classic', 'modern', 'minimal', 'professional', 'bold'];

    /**
     * Generate PDF and return as downloadable response
     */
    public function download(Document $document)
    {
        $pdf = $this->createPdf($document);
        return $pdf->download($this->generateFilename($document));
    }

    /**
     * Generate PDF and return as inline stream (for viewing in browser)
     */
    public function stream(Document $document)
    {
        $pdf = $this->createPdf($document);
        return $pdf->stream($this->generateFilename($document));
    }

    /**
     * Generate PDF and return raw output (for email attachments)
     */
    public function output(Document $document): string
    {
        $pdf = $this->createPdf($document);
        return $pdf->output();
    }

    /**
     * Generate a temporary URL for sharing (creates temp file, auto-deleted)
     * Used for WhatsApp sharing where we need a public URL
     */
    public function generateTemporaryUrl(Document $document): string
    {
        $pdf = $this->createPdf($document);
        $filename = $this->generateFilename($document);
        $path = "quick-invoice/temp/{$filename}";
        
        // Store temporarily
        Storage::disk('public')->put($path, $pdf->output());
        
        // Schedule cleanup (delete after 1 hour)
        $this->scheduleCleanup($path, 60);
        
        return Storage::disk('public')->url($path);
    }

    /**
     * Create the PDF instance
     */
    private function createPdf(Document $document)
    {
        $template = $this->getTemplateName($document);
        $documentData = $this->prepareDocumentData($document);
        
        $pdf = Pdf::loadView("pdf.quick-invoice.{$template}", [
            'document' => $documentData,
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('isHtml5ParserEnabled', true);

        return $pdf;
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

            // Try direct storage path (for paths like "quick-invoice/logos/...")
            $directPath = ltrim(parse_url($imageUrl, PHP_URL_PATH) ?? '', '/');
            $directPath = preg_replace('#^storage/#', '', $directPath);
            if ($directPath && Storage::disk('public')->exists($directPath)) {
                $contents = Storage::disk('public')->get($directPath);
                $mimeType = $this->getMimeType($directPath);
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

            // Try as absolute path using storage_path directly
            $storagePath = storage_path('app/public/' . $directPath);
            if (file_exists($storagePath)) {
                $contents = file_get_contents($storagePath);
                $mimeType = mime_content_type($storagePath) ?: 'image/png';
                return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
            }

            // Try public path as last resort
            $publicPath = public_path('storage/' . $directPath);
            if (file_exists($publicPath)) {
                $contents = file_get_contents($publicPath);
                $mimeType = mime_content_type($publicPath) ?: 'image/png';
                return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
            }

            \Log::warning('Image not found for base64 conversion', ['url' => $imageUrl, 'tried_path' => $directPath]);
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

    /**
     * Schedule file cleanup after specified minutes
     */
    private function scheduleCleanup(string $path, int $minutes): void
    {
        // Use Laravel's scheduler or queue to delete the file
        // For now, we'll use a simple delayed job approach
        dispatch(function () use ($path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        })->delay(now()->addMinutes($minutes));
    }

    /**
     * Clean up old temporary PDFs (can be called from scheduler)
     */
    public static function cleanupTempFiles(): int
    {
        $deleted = 0;
        $files = Storage::disk('public')->files('quick-invoice/temp');
        
        foreach ($files as $file) {
            $lastModified = Storage::disk('public')->lastModified($file);
            // Delete files older than 1 hour
            if ($lastModified < now()->subHour()->timestamp) {
                Storage::disk('public')->delete($file);
                $deleted++;
            }
        }
        
        return $deleted;
    }
}
