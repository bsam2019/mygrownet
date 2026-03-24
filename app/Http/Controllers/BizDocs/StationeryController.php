<?php

namespace App\Http\Controllers\BizDocs;

use App\Application\BizDocs\DTOs\GenerateStationeryDTO;
use App\Application\BizDocs\UseCases\GenerateStationeryUseCase;
use App\Domain\BizDocs\BusinessIdentity\Repositories\BusinessProfileRepositoryInterface;
use App\Domain\BizDocs\TemplateManagement\Repositories\TemplateRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StationeryController extends Controller
{
    public function __construct(
        private readonly BusinessProfileRepositoryInterface $businessProfileRepository,
        private readonly TemplateRepositoryInterface $templateRepository,
        private readonly GenerateStationeryUseCase $generateStationeryUseCase,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $businessProfile = $this->businessProfileRepository->findByUserId($user->id);

        if (!$businessProfile) {
            return redirect()->route('bizdocs.setup');
        }

        // Get all templates for selection
        $templates = $this->templateRepository->findByVisibility('industry');
        $customTemplates = $this->templateRepository->findByBusinessId($businessProfile->userId());

        return Inertia::render('BizDocs/Stationery/Generator', [
            'businessProfile' => $businessProfile->toArray(),
            'industryTemplates' => array_map(fn($t) => $t->toArray(), $templates),
            'customTemplates' => array_map(fn($t) => $t->toArray(), $customTemplates),
        ]);
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
}
