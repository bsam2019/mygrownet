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
            'documents_per_page' => 'required|integer|in:1,2,4',
            'starting_number' => 'required|string',
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

        // Generate preview with just 2 sample documents
        $prefix = $this->getDocumentPrefix($validated['document_type']);
        $startNumber = $this->parseDocumentNumber($validated['starting_number']);
        
        $documentNumbers = [
            sprintf('%s-%s-%04d', $prefix, date('Y'), $startNumber),
            sprintf('%s-%s-%04d', $prefix, date('Y'), $startNumber + 1),
        ];

        $data = [
            'businessProfile' => $businessProfile,
            'template' => $templateModel,
            'documentType' => $validated['document_type'],
            'documentNumbers' => $documentNumbers,
            'documentsPerPage' => $validated['documents_per_page'],
            'totalPages' => 1,
            'logoPath' => $this->getLogoPath($businessProfile),
            'signaturePath' => $this->getSignaturePath($businessProfile),
            'isPdf' => false, // HTML preview
        ];

        return view('bizdocs.stationery.template', $data);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'document_type' => 'required|string|in:invoice,receipt,quotation,delivery_note,proforma_invoice,credit_note,debit_note,purchase_order',
            'template_id' => 'required|integer|exists:bizdocs_document_templates,id',
            'quantity' => 'required|integer|min:1|max:500',
            'documents_per_page' => 'required|integer|in:1,2,4',
            'starting_number' => 'required|string',
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
                templateId: $validated['template_id'],
                quantity: $validated['quantity'],
                documentsPerPage: $validated['documents_per_page'],
                startingNumber: $validated['starting_number'],
            );

            $pdfPath = $this->generateStationeryUseCase->execute($dto);

            return response()->json([
                'success' => true,
                'message' => 'Stationery generated successfully',
                'download_url' => url("storage/{$pdfPath}"),
                'pdf_path' => $pdfPath,
            ]);
        } catch (\Exception $e) {
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
        
        if (filter_var($businessProfile->logo(), FILTER_VALIDATE_URL)) {
            return $businessProfile->logo();
        }
        
        return \Storage::disk('public')->path($businessProfile->logo());
    }
    
    private function getSignaturePath($businessProfile): ?string
    {
        if (!$businessProfile->signatureImage()) {
            return null;
        }
        
        if (filter_var($businessProfile->signatureImage(), FILTER_VALIDATE_URL)) {
            return $businessProfile->signatureImage();
        }
        
        return \Storage::disk('public')->path($businessProfile->signatureImage());
    }
}
