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
        
        // Check if this is an advanced template (not one of the 5 old templates)
        if (!in_array($template, self::TEMPLATES)) {
            \Log::info('PdfGeneratorService - Detected advanced template', [
                'template' => $template,
                'document_id' => $document->id()->value(),
            ]);
            
            try {
                // Try to load advanced template
                $advancedTemplate = $this->loadAdvancedTemplate($template);
                
                if ($advancedTemplate) {
                    \Log::info('PdfGeneratorService - Rendering advanced template');
                    
                    // Use TemplateRenderService to generate HTML from layout_json
                    $renderService = app(\App\Domain\QuickInvoice\Services\TemplateRenderService::class);
                    
                    $layoutJson = $advancedTemplate['layout_json'];
                    $fieldConfig = $advancedTemplate['field_config'] ?? $this->getDefaultFieldConfig();
                    $branding = [
                        'primary_color' => $documentData['colors']['primary'] ?? $advancedTemplate['primary_color'] ?? '#2563eb',
                        'secondary_color' => $advancedTemplate['secondary_color'] ?? '#1d4ed8',
                        'font_family' => $advancedTemplate['font_family'] ?? 'DejaVu Sans, Arial, sans-serif',
                        'logo_url' => $documentData['business_info']['logo'] ?? null,
                    ];
                    
                    $renderedHtml = $renderService->renderToHtml($layoutJson, $fieldConfig, $documentData, $branding);
                    
                    \Log::info('PdfGeneratorService - HTML rendered successfully', [
                        'html_length' => strlen($renderedHtml),
                    ]);
                    
                    $pdf = Pdf::loadView("pdf.quick-invoice.advanced", [
                        'document' => $documentData,
                        'renderedHtml' => $renderedHtml,
                    ]);
                    
                    \Log::info('PdfGeneratorService - PDF created successfully');
                } else {
                    // Fallback to classic if advanced template not found
                    \Log::warning('PdfGeneratorService - Advanced template not found, falling back to classic', [
                        'template' => $template,
                    ]);
                    $pdf = Pdf::loadView("pdf.quick-invoice.classic", [
                        'document' => $documentData,
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('PdfGeneratorService - Error rendering advanced template', [
                    'template' => $template,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                // Fallback to classic on error
                $pdf = Pdf::loadView("pdf.quick-invoice.classic", [
                    'document' => $documentData,
                ]);
            }
        } else {
            // Use traditional Blade template
            $pdf = Pdf::loadView("pdf.quick-invoice.{$template}", [
                'document' => $documentData,
            ]);
        }

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('isHtml5ParserEnabled', true);

        return $pdf;
    }
    
    /**
     * Load advanced template from database or system templates
     */
    private function loadAdvancedTemplate(string $templateId): ?array
    {
        // Check if it's a custom template (format: custom-123)
        if (str_starts_with($templateId, 'custom-')) {
            $id = (int) str_replace('custom-', '', $templateId);
            $customTemplate = \App\Models\QuickInvoice\CustomTemplate::find($id);
            
            if ($customTemplate) {
                return [
                    'layout_json' => $customTemplate->layout_json,
                    'field_config' => $customTemplate->field_config,
                    'primary_color' => $customTemplate->primary_color,
                    'secondary_color' => $customTemplate->secondary_color,
                    'font_family' => $customTemplate->font_family,
                ];
            }
        }
        
        // Check system advanced templates (from DesignStudioController)
        $systemTemplates = $this->getSystemAdvancedTemplates();
        $template = collect($systemTemplates)->firstWhere('id', $templateId);
        
        if ($template) {
            return [
                'layout_json' => $template['layout_json'],
                'field_config' => $this->getDefaultFieldConfig(),
                'primary_color' => $template['primary_color'],
                'secondary_color' => $template['secondary_color'],
                'font_family' => $template['font_family'],
            ];
        }
        
        return null;
    }
    
    /**
     * Get system advanced templates (same as in DesignStudioController)
     */
    private function getSystemAdvancedTemplates(): array
    {
        // Note: These are DIFFERENT from the old Blade templates
        // Old templates: classic.blade.php, modern.blade.php, etc.
        // These are advanced templates with layout_json
        return [
            [
                'id' => 'advanced-classic',
                'name' => 'Classic Advanced',
                'primary_color' => '#3b82f6',
                'secondary_color' => '#1d4ed8',
                'font_family' => 'Inter, sans-serif',
                'layout_json' => [
                    'blocks' => [
                        ['type' => 'header', 'config' => ['showLogo' => true, 'showCompanyInfo' => true]],
                        ['type' => 'invoice-meta', 'config' => ['showTitle' => true]],
                        ['type' => 'customer-details', 'config' => ['showAddress' => true]],
                        ['type' => 'items-table', 'config' => ['style' => 'striped']],
                        ['type' => 'totals', 'config' => ['alignment' => 'right']],
                    ],
                ],
            ],
            [
                'id' => 'advanced-professional',
                'name' => 'Professional Modern',
                'primary_color' => '#0d9488',
                'secondary_color' => '#134e4a',
                'font_family' => 'Inter, sans-serif',
                'layout_json' => [
                    'blocks' => [
                        ['type' => 'invoice-title-bar', 'config' => ['style' => 'large-title']],
                        ['type' => 'customer-split', 'config' => []],
                        ['type' => 'items-table', 'config' => ['style' => 'striped']],
                        ['type' => 'payment-details', 'config' => []],
                        ['type' => 'totals', 'config' => ['alignment' => 'right']],
                    ],
                ],
            ],
            [
                'id' => 'advanced-minimal',
                'name' => 'Minimal',
                'primary_color' => '#6366f1',
                'secondary_color' => '#4f46e5',
                'font_family' => 'Inter, sans-serif',
                'layout_json' => [
                    'blocks' => [
                        ['type' => 'company-header-centered', 'config' => []],
                        ['type' => 'invoice-meta', 'config' => []],
                        ['type' => 'customer-details', 'config' => []],
                        ['type' => 'items-table-minimal', 'config' => []],
                        ['type' => 'totals', 'config' => ['alignment' => 'right']],
                    ],
                ],
            ],
        ];
    }
    
    /**
     * Get default field configuration
     */
    private function getDefaultFieldConfig(): array
    {
        return [
            'invoice_number' => ['enabled' => true, 'required' => true],
            'invoice_date' => ['enabled' => true, 'required' => true],
            'due_date' => ['enabled' => true, 'required' => false],
            'customer_name' => ['enabled' => true, 'required' => true],
            'customer_address' => ['enabled' => true, 'required' => false],
            'customer_phone' => ['enabled' => true, 'required' => false],
            'customer_email' => ['enabled' => true, 'required' => false],
            'company_name' => ['enabled' => true, 'required' => false],
            'company_address' => ['enabled' => true, 'required' => false],
            'company_phone' => ['enabled' => true, 'required' => false],
            'company_email' => ['enabled' => true, 'required' => false],
            'items_table' => ['enabled' => true, 'required' => true],
            'subtotal' => ['enabled' => true, 'required' => false],
            'tax' => ['enabled' => true, 'required' => false],
            'discount' => ['enabled' => true, 'required' => false],
            'total' => ['enabled' => true, 'required' => true],
            'notes' => ['enabled' => true, 'required' => false],
            'terms' => ['enabled' => true, 'required' => false],
        ];
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
        
        \Log::info('PdfGeneratorService - Getting template', [
            'template_from_document' => $template,
            'valid_templates' => self::TEMPLATES,
            'is_valid' => in_array($template, self::TEMPLATES),
        ]);
        
        // Fallback to classic if template doesn't exist
        if (!in_array($template, self::TEMPLATES)) {
            \Log::warning('PdfGeneratorService - Invalid template, falling back to classic', [
                'invalid_template' => $template,
            ]);
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
