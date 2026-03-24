<?php

namespace App\Application\BizDocs\Services;

use App\Domain\BizDocs\BusinessIdentity\Repositories\BusinessProfileRepositoryInterface;
use App\Domain\BizDocs\CustomerManagement\Repositories\CustomerRepositoryInterface;
use App\Domain\BizDocs\DocumentManagement\Entities\Document;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfGenerationService
{
    public function __construct(
        private readonly BusinessProfileRepositoryInterface $businessProfileRepository,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly FileStorageService $fileStorageService
    ) {
    }

    public function generatePdf(Document $document): string
    {
        $businessProfile = $this->businessProfileRepository->findById($document->businessId());
        $customer = $this->customerRepository->findById($document->customerId());

        if (!$businessProfile || !$customer) {
            throw new \RuntimeException('Business profile or customer not found');
        }

        $totals = $document->calculateTotals();

        // Get image URLs or paths for PDF
        $logoPath = null;
        $signaturePath = null;

        if ($businessProfile->logo()) {
            $logoPath = $this->getImageForPdf($businessProfile->logo());
        }

        if ($businessProfile->signatureImage()) {
            $signaturePath = $this->getImageForPdf($businessProfile->signatureImage());
        }

        $data = [
            'document' => $document,
            'businessProfile' => $businessProfile,
            'customer' => $customer,
            'totals' => $totals,
            'logoPath' => $logoPath,
            'signaturePath' => $signaturePath,
        ];

        // Determine which template to use
        $viewPath = 'bizdocs.pdf.document';
        
        // If document has a template with layout_file, use it
        if ($document->templateId()) {
            $template = \App\Infrastructure\BizDocs\Persistence\Eloquent\DocumentTemplateModel::find($document->templateId());
            if ($template && !empty($template->layout_file)) {
                $specificView = 'bizdocs.pdf.templates.' . $template->layout_file;
                if (view()->exists($specificView)) {
                    $viewPath = $specificView;
                    $data['template'] = $template;
                    
                    // Use TemplateDataWrapper to allow both property and method access
                    $data['document'] = new TemplateDataWrapper([
                        'number' => $document->number()->value(),
                        'type' => $document->type()->value(),
                        'issueDate' => $document->issueDate(),
                        'dueDate' => $document->dueDate(),
                        'validityDate' => $document->validityDate(),
                    ]);
                    
                    $data['customer'] = new TemplateDataWrapper([
                        'name' => $customer->name(),
                        'address' => $customer->address(),
                        'phone' => $customer->phone(),
                        'email' => $customer->email(),
                        'tpin' => $customer->tpin(),
                    ]);
                    
                    $data['businessProfile'] = new TemplateDataWrapper([
                        'businessName' => $businessProfile->businessName(),
                        'address' => $businessProfile->address(),
                        'phone' => $businessProfile->phone(),
                        'email' => $businessProfile->email(),
                        'tpin' => $businessProfile->tpin(),
                        'defaultCurrency' => $businessProfile->defaultCurrency(),
                        'logo' => $businessProfile->logo(),
                        'signatureImage' => $businessProfile->signatureImage(),
                    ]);
                    
                    $data['items'] = array_map(function($item) {
                        return new TemplateDataWrapper([
                            'description' => $item->description(),
                            'quantity' => $item->quantity(),
                            'unitPrice' => $item->unitPrice()->amount(),
                            'taxRate' => $item->taxRate(),
                            'discountAmount' => $item->discountAmount()->amount(),
                        ]);
                    }, $document->items());
                    
                    // Wrap totals as well
                    $data['totals'] = new TemplateDataWrapper([
                        'subtotal' => $totals['subtotal']->amount(),
                        'taxTotal' => $totals['tax_total']->amount(),
                        'discountTotal' => $totals['discount_total']->amount(),
                        'grandTotal' => $totals['grand_total']->amount(),
                    ]);
                }
            }
        }

        // Generate PDF using DomPDF
        $pdf = Pdf::loadView($viewPath, $data)
            ->setPaper('a4', 'portrait');

        // Create directory if it doesn't exist
        $directory = "documents/{$businessProfile->id()}";
        Storage::makeDirectory($directory);

        // Generate filename
        $filename = "{$document->type()->value()}_{$document->number()->value()}.pdf";
        $path = "{$directory}/{$filename}";

        // Save PDF
        Storage::put($path, $pdf->output());

        return $path;
    }

    /**
     * Generate PDF content without saving to disk (for direct download)
     */
    public function generatePdfContent(Document $document): string
    {
        \Log::info('Generating PDF content', [
            'document_id' => $document->id(),
            'document_type' => $document->type()->value(),
            'document_number' => $document->number()->value(),
            'items_count' => count($document->items()),
        ]);

        $businessProfile = $this->businessProfileRepository->findById($document->businessId());
        $customer = $this->customerRepository->findById($document->customerId());

        if (!$businessProfile || !$customer) {
            \Log::error('Business profile or customer not found', [
                'business_id' => $document->businessId(),
                'customer_id' => $document->customerId(),
                'business_profile_found' => $businessProfile !== null,
                'customer_found' => $customer !== null,
            ]);
            throw new \RuntimeException('Business profile or customer not found');
        }

        \Log::info('Business profile and customer found', [
            'business_name' => $businessProfile->businessName(),
            'customer_name' => $customer->name(),
        ]);

        $totals = $document->calculateTotals();

        \Log::info('Totals calculated', [
            'subtotal' => $totals['subtotal']->amount() / 100,
            'tax_total' => $totals['tax_total']->amount() / 100,
            'grand_total' => $totals['grand_total']->amount() / 100,
        ]);

        // Get image URLs or paths for PDF
        $logoPath = null;
        $signaturePath = null;

        if ($businessProfile->logo()) {
            $logoPath = $this->getImageForPdf($businessProfile->logo());
            \Log::info('Logo path set', ['logo_path' => $logoPath]);
        }

        if ($businessProfile->signatureImage()) {
            $signaturePath = $this->getImageForPdf($businessProfile->signatureImage());
            \Log::info('Signature path set', ['signature_path' => $signaturePath]);
        }

        $data = [
            'document' => $document,
            'businessProfile' => $businessProfile,
            'customer' => $customer,
            'totals' => $totals,
            'logoPath' => $logoPath,
            'signaturePath' => $signaturePath,
        ];

        \Log::info('Generating PDF with DomPDF');

        try {
            // Generate PDF using DomPDF
            $pdf = Pdf::loadView('bizdocs.pdf.document', $data)
                ->setPaper('a4', 'portrait');

            $output = $pdf->output();
            
            \Log::info('PDF generated successfully', ['size' => strlen($output)]);

            return $output;
        } catch (\Exception $e) {
            \Log::error('PDF generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get image path suitable for PDF generation
     * If using remote storage, download temporarily
     */
    public function getImageForPdf(string $storagePath): ?string
    {
        $disk = $this->fileStorageService->getDisk();

        if ($disk === 'public') {
            // Local storage - use public_path
            return public_path('storage/' . $storagePath);
        }

        // Remote storage (DigitalOcean Spaces) - get public URL
        // DomPDF can handle remote URLs if they're publicly accessible
        return $this->fileStorageService->getUrl($storagePath);
    }

    public function getSignedUrl(string $path, int $expiresInHours = 24): string
    {
        return Storage::temporaryUrl(
            $path,
            now()->addHours($expiresInHours)
        );
    }
}

