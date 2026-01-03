<?php

namespace App\Http\Controllers;

use App\Domain\QuickInvoice\Exceptions\DocumentNotFoundException;
use App\Domain\QuickInvoice\Exceptions\UnauthorizedAccessException;
use App\Domain\QuickInvoice\Services\DocumentService;
use App\Domain\QuickInvoice\Services\PdfGeneratorService;
use App\Domain\QuickInvoice\Services\ShareService;
use App\Domain\QuickInvoice\ValueObjects\TemplateStyle;
use App\Http\Requests\QuickInvoice\CreateDocumentRequest;
use App\Http\Requests\QuickInvoice\SendEmailRequest;
use App\Http\Requests\QuickInvoice\UploadLogoRequest;
use App\Models\QuickInvoiceProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuickInvoiceController extends Controller
{
    public function __construct(
        private readonly DocumentService $documentService,
        private readonly PdfGeneratorService $pdfGenerator,
        private readonly ShareService $shareService
    ) {}

    public function index(): Response
    {
        return Inertia::render('QuickInvoice/Index', [
            'currencies' => $this->getCurrencies(),
            'documentTypes' => $this->getDocumentTypes(),
            'templates' => TemplateStyle::all(),
        ]);
    }

    public function create(Request $request): Response
    {
        $savedProfile = null;
        if (auth()->check()) {
            $profile = QuickInvoiceProfile::where('user_id', auth()->id())->first();
            if ($profile) {
                $savedProfile = $profile->toArray();
            }
        }

        return Inertia::render('QuickInvoice/Create', [
            'documentType' => $request->query('type', 'invoice'),
            'initialTemplate' => $request->query('template', 'classic'),
            'currencies' => $this->getCurrencies(),
            'templates' => TemplateStyle::all(),
            'savedProfile' => $savedProfile,
        ]);
    }

    /**
     * Generate document - saves data only, PDF generated on-demand
     */
    public function generate(CreateDocumentRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['session_id'] = session()->getId();
        $data['user_id'] = auth()->id();

        \Log::info('QuickInvoice generate request', [
            'prepared_by' => $data['prepared_by'] ?? 'NOT SET',
            'signature' => isset($data['signature']) ? 'SET' : 'NOT SET',
        ]);

        // Create and save the document (data only, no PDF storage)
        $document = $this->documentService->createDocument($data);
        $this->documentService->saveDocument($document);

        // Generate share data with download URL (PDF generated on-demand when accessed)
        $shareData = $this->shareService->generateShareData($document);

        return response()->json([
            'success' => true,
            'document' => $document->toArray(),
            'share' => $shareData,
        ]);
    }

    public function uploadLogo(UploadLogoRequest $request): JsonResponse
    {
        $logoUrl = $this->documentService->uploadLogo(
            $request->file('logo'),
            session()->getId()
        );

        return response()->json(['success' => true, 'url' => $logoUrl]);
    }

    public function uploadSignature(Request $request): JsonResponse
    {
        $request->validate([
            'signature' => 'required|image|mimes:png,jpg,jpeg|max:1024',
        ]);

        $signatureUrl = $this->documentService->uploadSignature(
            $request->file('signature'),
            session()->getId()
        );

        return response()->json(['success' => true, 'url' => $signatureUrl]);
    }

    /**
     * Download PDF - generates on-demand
     */
    public function downloadPdf(string $id)
    {
        // Increase execution time for PDF generation
        set_time_limit(120);
        
        try {
            // For Quick Invoice, allow public download access via UUID
            $document = $this->documentService->findDocument($id);
            return $this->pdfGenerator->download($document);
        } catch (DocumentNotFoundException $e) {
            abort(404, 'Document not found');
        } catch (\Exception $e) {
            \Log::error('PDF generation failed', ['id' => $id, 'error' => $e->getMessage()]);
            abort(500, 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * View PDF in browser - generates on-demand
     */
    public function viewPdf(string $id)
    {
        // Increase execution time for PDF generation
        set_time_limit(120);
        
        try {
            // For Quick Invoice, allow public view access via UUID
            $document = $this->documentService->findDocument($id);
            return $this->pdfGenerator->stream($document);
        } catch (DocumentNotFoundException $e) {
            abort(404, 'Document not found');
        } catch (\Exception $e) {
            \Log::error('PDF streaming failed', ['id' => $id, 'error' => $e->getMessage()]);
            abort(500, 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Send document via email - generates PDF on-demand for attachment
     */
    public function sendEmail(SendEmailRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['session_id'] = session()->getId();
        $data['user_id'] = auth()->id();
        
        $document = $this->documentService->createDocument($data);
        
        // Send email with on-demand PDF generation
        $sent = $this->shareService->sendEmail($document);

        if ($sent) {
            $document->markAsSent();
            $this->documentService->saveDocument($document);
        }

        return response()->json([
            'success' => $sent,
            'message' => $sent ? 'Email sent successfully' : 'Failed to send email',
        ]);
    }

    /**
     * Get WhatsApp share link with temporary PDF URL
     */
    public function getWhatsAppLink(string $id): JsonResponse
    {
        try {
            $document = $this->documentService->findDocumentWithAccess(
                $id, auth()->id(), session()->getId()
            );
            
            // Generate temporary URL for WhatsApp sharing
            $tempPdfUrl = $this->pdfGenerator->generateTemporaryUrl($document);
            $whatsappLink = $this->shareService->generateWhatsAppLink($document, $tempPdfUrl);
            
            return response()->json([
                'success' => true,
                'whatsapp_link' => $whatsappLink,
            ]);
        } catch (DocumentNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (UnauthorizedAccessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }
    }

    public function history(): Response
    {
        $documents = auth()->check()
            ? $this->documentService->getDocumentsByUser(auth()->id())
            : $this->documentService->getDocumentsBySession(session()->getId());

        return Inertia::render('QuickInvoice/History', [
            'documents' => array_map(fn($doc) => $doc->toArray(), $documents),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->documentService->deleteDocument($id, auth()->id(), session()->getId());
            return response()->json(['success' => true]);
        } catch (DocumentNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (UnauthorizedAccessException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }
    }

    public function saveProfile(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'address' => 'nullable|string|max:500',
                'phone' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:255',
                'logo' => 'nullable|string|max:500',
                'signature' => 'nullable|string|max:500',
                'tax_number' => 'nullable|string|max:50',
                'default_tax_rate' => 'nullable|numeric|min:0|max:100',
                'default_discount_rate' => 'nullable|numeric|min:0|max:100',
                'default_notes' => 'nullable|string|max:1000',
                'default_terms' => 'nullable|string|max:1000',
            ]);

            $profile = QuickInvoiceProfile::updateOrCreate(
                ['user_id' => auth()->id()],
                $validated
            );

            return response()->json([
                'success' => true,
                'profile' => $profile->toArray(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to save invoice profile', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save profile: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getProfile(): JsonResponse
    {
        $profile = QuickInvoiceProfile::where('user_id', auth()->id())->first();

        return response()->json([
            'success' => true,
            'profile' => $profile?->toArray(),
        ]);
    }

    private function getCurrencies(): array
    {
        return [
            ['code' => 'ZMW', 'symbol' => 'K', 'name' => 'Zambian Kwacha'],
            ['code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar'],
            ['code' => 'EUR', 'symbol' => '€', 'name' => 'Euro'],
            ['code' => 'GBP', 'symbol' => '£', 'name' => 'British Pound'],
            ['code' => 'ZAR', 'symbol' => 'R', 'name' => 'South African Rand'],
        ];
    }

    private function getDocumentTypes(): array
    {
        return [
            ['value' => 'invoice', 'label' => 'Invoice', 'description' => 'Bill for goods or services'],
            ['value' => 'delivery_note', 'label' => 'Delivery Note', 'description' => 'Proof of delivery'],
            ['value' => 'quotation', 'label' => 'Quotation', 'description' => 'Price estimate'],
            ['value' => 'receipt', 'label' => 'Receipt', 'description' => 'Proof of payment'],
        ];
    }
}
