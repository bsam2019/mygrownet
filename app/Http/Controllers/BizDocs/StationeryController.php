<?php

namespace App\Http\Controllers\BizDocs;

use App\Application\BizDocs\DTOs\GenerateStationeryDTO;
use App\Application\BizDocs\UseCases\GenerateStationeryUseCase;
use App\Domain\BizDocs\BusinessIdentity\Repositories\BusinessProfileRepositoryInterface;
use App\Infrastructure\BizDocs\Persistence\Eloquent\DocumentTemplateModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StationeryController extends Controller
{
    public function __construct(
        private readonly BusinessProfileRepositoryInterface $businessProfileRepository,
        private readonly GenerateStationeryUseCase $generateStationeryUseCase,
        private readonly \App\Application\BizDocs\Services\FileStorageService $fileStorageService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $businessProfile = $this->businessProfileRepository->findByUserId($user->id);

        if (!$businessProfile) {
            return redirect()->route('bizdocs.setup');
        }

        // Get all templates for selection (using Eloquent directly like DocumentController)
        $industryTemplates = DocumentTemplateModel::where('visibility', 'industry')
            ->get()
            ->toArray();
        
        $customTemplates = DocumentTemplateModel::where('visibility', 'business')
            ->where('owner_id', $user->id)
            ->get()
            ->toArray();

        return Inertia::render('BizDocs/Stationery/Generator', [
            'businessProfile' => $businessProfile->toArray(),
            'industryTemplates' => $industryTemplates,
            'customTemplates' => $customTemplates,
        ]);
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'document_type' => 'required|string|in:invoice,receipt,quotation,delivery_note,proforma_invoice,credit_note,debit_note,purchase_order',
            'template_id' => 'required|integer|exists:bizdocs_document_templates,id',
            'documents_per_page' => 'required|integer|in:1,2,4,6,8,10',
            'starting_number' => 'required|string',
            'page_size' => 'nullable|string|in:A4,A5,custom',
            'row_count' => 'nullable|integer|min:1|max:50',
        ]);

        $user = $request->user();
        $businessProfile = $this->businessProfileRepository->findByUserId($user->id);

        if (!$businessProfile) {
            return back()->withErrors(['error' => 'Business profile not found']);
        }

        $templateModel = DocumentTemplateModel::find($validated['template_id']);
        if (!$templateModel) {
            return back()->withErrors(['error' => 'Template not found']);
        }

        // Generate preview with sample documents based on layout
        $prefix = $this->getDocumentPrefix($validated['document_type']);
        $startNumber = $this->parseDocumentNumber($validated['starting_number']);
        
        // For preview, generate exactly documents_per_page worth of documents to show the layout
        $previewQuantity = $validated['documents_per_page'];
        $documentNumbers = [];
        for ($i = 0; $i < $previewQuantity; $i++) {
            $documentNumbers[] = sprintf('%s-%s-%04d', $prefix, date('Y'), $startNumber + $i);
        }

        // Calculate total pages for preview
        $totalPages = (int) ceil($previewQuantity / $validated['documents_per_page']);

        $data = [
            'businessProfile' => $businessProfile,
            'template' => $templateModel,
            'documentType' => $validated['document_type'],
            'documentNumbers' => $documentNumbers,
            'documentsPerPage' => (int) $validated['documents_per_page'], // Cast to int for strict match
            'pageSize' => $validated['page_size'] ?? 'A4',
            'rowCount' => $validated['row_count'] ?? null,
            'totalPages' => $totalPages,
            'logoPath' => $this->getLogoPath($businessProfile),
            'signaturePath' => $this->getSignaturePath($businessProfile),
            'isPdf' => false, // HTML preview
        ];

        // Select template based on document type
        $viewPath = $this->getTemplateView($validated['document_type']);
        
        // Use simplified template for testing
        if ($validated['document_type'] === 'receipt') {
            $viewPath = 'bizdocs.stationery.receipt-simple';
        }
        
        return view($viewPath, $data);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'document_type' => 'required|string|in:invoice,receipt,quotation,delivery_note,proforma_invoice,credit_note,debit_note,purchase_order',
            'template_id' => 'required|integer|exists:bizdocs_document_templates,id',
            'quantity' => 'required|integer|min:1|max:500',
            'documents_per_page' => 'required|integer|in:1,2,4,6,8,10',
            'starting_number' => 'required|string',
            'page_size' => 'nullable|string|in:A4,A5,custom',
            'row_count' => 'nullable|integer|min:1|max:50',
        ]);

        $user = $request->user();
        $businessProfile = $this->businessProfileRepository->findByUserId($user->id);

        if (!$businessProfile) {
            return back()->withErrors(['error' => 'Business profile not found']);
        }

        try {
            $dto = new GenerateStationeryDTO(
                businessId: $user->id,
                documentType: $validated['document_type'],
                templateId: (int) $validated['template_id'],
                quantity: (int) $validated['quantity'],
                documentsPerPage: (int) $validated['documents_per_page'], // Explicit cast for strict match
                startingNumber: $validated['starting_number'],
                pageSize: $validated['page_size'] ?? 'A4',
                rowCount: $validated['row_count'] ? (int) $validated['row_count'] : null,
            );

            $pdfContent = $this->generateStationeryUseCase->execute($dto);

            // Generate filename
            $filename = sprintf(
                'stationery_%s_%s_%d_docs.pdf',
                $validated['document_type'],
                date('Y-m-d_His'),
                $validated['quantity']
            );

            // Return PDF as download without saving
            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Exception $e) {
            \Log::error('Stationery generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'data' => $validated,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
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
    
    private function getTemplateView(string $documentType): string
    {
        // Use specialized templates for specific document types
        return match($documentType) {
            'receipt' => 'bizdocs.stationery.receipt',
            'invoice', 'quotation', 'proforma_invoice' => 'bizdocs.stationery.template',
            default => 'bizdocs.stationery.template',
        };
    }

    private function parseDocumentNumber(string $documentNumber): int
    {
        $parts = explode('-', $documentNumber);
        return (int) end($parts);
    }

    private function getLogoPath($businessProfile): ?string
    {
        if (!$businessProfile->logo()) {
            return null;
        }
        
        try {
            $logoContent = \Storage::disk($this->fileStorageService->getDisk())->get($businessProfile->logo());
            return 'data:image/png;base64,' . base64_encode($logoContent);
        } catch (\Exception $e) {
            \Log::warning('Failed to load logo for stationery', ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    private function getSignaturePath($businessProfile): ?string
    {
        if (!$businessProfile->signatureImage()) {
            return null;
        }
        
        try {
            $signatureContent = \Storage::disk($this->fileStorageService->getDisk())->get($businessProfile->signatureImage());
            return 'data:image/png;base64,' . base64_encode($signatureContent);
        } catch (\Exception $e) {
            \Log::warning('Failed to load signature for stationery', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
