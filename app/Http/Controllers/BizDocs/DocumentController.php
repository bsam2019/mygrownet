<?php

namespace App\Http\Controllers\BizDocs;

use App\Application\BizDocs\DTOs\CreateDocumentDTO;
use App\Application\BizDocs\DTOs\RecordPaymentDTO;
use App\Application\BizDocs\Services\PdfGenerationService;
use App\Application\BizDocs\Services\WhatsAppSharingService;
use App\Application\BizDocs\UseCases\Document\CreateDocumentUseCase;
use App\Application\BizDocs\UseCases\Document\FinalizeDocumentUseCase;
use App\Domain\BizDocs\BusinessIdentity\Repositories\BusinessProfileRepositoryInterface;
use App\Domain\BizDocs\CustomerManagement\Repositories\CustomerRepositoryInterface;
use App\Domain\BizDocs\DocumentManagement\Repositories\DocumentRepositoryInterface;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\DocumentType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentRepositoryInterface $documentRepository,
        private readonly BusinessProfileRepositoryInterface $businessProfileRepository,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly CreateDocumentUseCase $createDocumentUseCase,
        private readonly FinalizeDocumentUseCase $finalizeDocumentUseCase,
        private readonly \App\Application\BizDocs\UseCases\RecordPaymentUseCase $recordPaymentUseCase,
        private readonly \App\Application\BizDocs\UseCases\CancelDocumentUseCase $cancelDocumentUseCase,
        private readonly \App\Application\BizDocs\UseCases\VoidDocumentUseCase $voidDocumentUseCase,
        private readonly \App\Application\BizDocs\UseCases\ConvertQuotationToInvoiceUseCase $convertQuotationUseCase,
        private readonly \App\Application\BizDocs\UseCases\DuplicateDocumentUseCase $duplicateDocumentUseCase,
        private readonly \App\Application\BizDocs\Services\DocumentStatusHistoryService $statusHistoryService,
        private readonly PdfGenerationService $pdfGenerationService,
        private readonly WhatsAppSharingService $whatsAppSharingService,
        private readonly \App\Application\BizDocs\Services\FileStorageService $fileStorageService
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $businessProfile = $this->businessProfileRepository->findByUserId($user->id);

        if (!$businessProfile) {
            return redirect()->route('bizdocs.setup');
        }

        $type = $request->query('type');
        $documentType = $type ? DocumentType::fromString($type) : null;

        $documents = $this->documentRepository->findByBusiness(
            $businessProfile->id(),
            $documentType,
            page: (int) $request->query('page', 1),
            perPage: 20
        );

        $totalCount = $this->documentRepository->countByBusiness(
            $businessProfile->id(),
            $documentType
        );

        // Serialize documents and add customer names
        $documentsArray = array_map(function($doc) {
            $customer = $this->customerRepository->findById($doc->customerId());
            $docArray = $doc->toArray();
            $docArray['customerName'] = $customer?->name() ?? 'Unknown';
            return $docArray;
        }, $documents);

        return Inertia::render('BizDocs/Documents/List', [
            'documents' => $documentsArray,
            'totalCount' => $totalCount,
            'currentType' => $type,
            'businessProfile' => $businessProfile->toArray(),
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $businessProfile = $this->businessProfileRepository->findByUserId($user->id);

        if (!$businessProfile) {
            return redirect()->route('bizdocs.setup');
        }

        $type = $request->query('type', 'invoice');
        $customers = $this->customerRepository->findByBusiness($businessProfile->id(), perPage: 100);

        // Convert customers to arrays for frontend
        $customersArray = array_map(fn($customer) => [
            'id' => $customer->id(),
            'name' => $customer->name(),
            'address' => $customer->address(),
            'phone' => $customer->phone(),
            'email' => $customer->email(),
        ], $customers);

        // Get all templates (templates are generic and work for any document type)
        $templates = \App\Infrastructure\BizDocs\Persistence\Eloquent\DocumentTemplateModel::where(function($query) use ($businessProfile) {
                $query->where('visibility', 'industry')
                      ->orWhere(function($q) use ($businessProfile) {
                          $q->where('visibility', 'business')
                            ->where('owner_id', $businessProfile->id());
                      });
            })
            ->orderBy('is_default', 'desc')
            ->orderBy('visibility', 'asc')
            ->orderBy('name', 'asc')
            ->get()
            ->toArray();

        // Get user's preferred template (not document-type specific anymore)
        $preference = \DB::table('bizdocs_user_template_preferences')
            ->where('business_id', $businessProfile->id())
            ->where('document_type', $type)
            ->first();

        $defaultTemplateId = $preference?->template_id;

        // If no preference, use the first default template
        if (!$defaultTemplateId && count($templates) > 0) {
            $defaultTemplate = collect($templates)->firstWhere('is_default', true);
            $defaultTemplateId = $defaultTemplate['id'] ?? $templates[0]['id'] ?? null;
        }

        return Inertia::render('BizDocs/Documents/Create', [
            'documentType' => $type,
            'businessProfile' => $businessProfile->toArray(),
            'customers' => $customersArray,
            'templates' => $templates,
            'defaultTemplateId' => $defaultTemplateId,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $businessProfile = $this->businessProfileRepository->findByUserId($user->id);

        if (!$businessProfile) {
            return response()->json(['error' => 'Business profile not found'], 404);
        }

        $validated = $request->validate([
            'customer_id' => 'required|integer|exists:bizdocs_customers,id',
            'document_type' => 'required|string|in:invoice,receipt,quotation,delivery_note,proforma_invoice,credit_note,debit_note,purchase_order',
            'template_id' => 'nullable|integer|exists:bizdocs_document_templates,id',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            'validity_date' => 'nullable|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'payment_instructions' => 'nullable|string',
            'discount_type' => 'nullable|string|in:amount,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'collect_tax' => 'nullable|boolean',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.dimensions' => 'nullable|string',
            'items.*.dimensions_value' => 'nullable|numeric|min:0.01',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
        ]);

        // Inject default tax rate from business profile into each item
        $items = array_map(function ($item) use ($businessProfile) {
            $item['tax_rate'] = $businessProfile->defaultTaxRate();
            return $item;
        }, $validated['items']);

        try {
            $dto = CreateDocumentDTO::fromArray([
                'business_id' => $businessProfile->id(),
                'customer_id' => $validated['customer_id'],
                'document_type' => $validated['document_type'],
                'template_id' => $validated['template_id'] ?? null,
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'] ?? null,
                'validity_date' => $validated['validity_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'payment_instructions' => $validated['payment_instructions'] ?? null,
                'currency' => $businessProfile->defaultCurrency(),
                'discount_type' => $validated['discount_type'] ?? 'amount',
                'discount_value' => $validated['discount_value'] ?? 0,
                'collect_tax' => $validated['collect_tax'] ?? true,
                'items' => $items,
            ]);

            $document = $this->createDocumentUseCase->execute($dto);

            return redirect()
                ->route('bizdocs.documents.show', $document->id())
                ->with('success', 'Document created successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Request $request, int $id)
    {
        $document = $this->documentRepository->findById($id);

        if (!$document) {
            abort(404);
        }

        $user = $request->user();
        $businessProfile = $this->businessProfileRepository->findByUserId($user->id);
        $customer = $this->customerRepository->findById($document->customerId());

        // Serialize document with payment information
        $documentArray = $document->toArray();
        $documentArray['totalPaid'] = $document->calculateTotalPaid()->amount() / 100;
        $documentArray['remainingBalance'] = $document->calculateRemainingBalance()->amount() / 100;
        $documentArray['payments'] = array_map(function($payment) {
            return [
                'id' => $payment->id(),
                'paymentDate' => $payment->paymentDate()->format('Y-m-d'),
                'amount' => $payment->amount()->amount() / 100,
                'paymentMethod' => $payment->paymentMethod()->value(),
                'paymentMethodLabel' => $payment->paymentMethod()->label(),
                'referenceNumber' => $payment->referenceNumber(),
                'notes' => $payment->notes(),
                'receiptId' => $payment->receiptId(),
            ];
        }, $document->payments());

        return Inertia::render('BizDocs/Documents/Show', [
            'document' => $documentArray,
            'businessProfile' => $businessProfile->toArray(),
            'customer' => $customer->toArray(),
        ]);
    }

    public function finalize(int $id)
    {
        try {
            $userId = auth()->id();
            $document = $this->finalizeDocumentUseCase->execute($id, $userId);

            return redirect()
                ->route('bizdocs.documents.show', $document->id())
                ->with('success', 'Document finalized successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function generatePdf(int $id)
    {
        try {
            $document = $this->documentRepository->findById($id);

            if (!$document) {
                abort(404);
            }

            // Generate PDF
            $path = $this->pdfGenerationService->generatePdf($document);

            // Update document with PDF path
            $document->setPdfPath($path);
            $this->documentRepository->save($document);

            return redirect()
                ->route('bizdocs.documents.show', $document->id())
                ->with('success', 'PDF generated successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function downloadPdf(int $id)
    {
        \Log::info('Download PDF requested', ['document_id' => $id]);

        try {
            $document = $this->documentRepository->findById($id);

            if (!$document) {
                \Log::error('Document not found', ['document_id' => $id]);
                abort(404);
            }

            $businessProfile = $this->businessProfileRepository->findById($document->businessId());
            $customer = $this->customerRepository->findById($document->customerId());
            
            if (!$businessProfile || !$customer) {
                throw new \RuntimeException('Business profile or customer not found');
            }
            
            $totalsObjects = $document->calculateTotals();
            
            // Convert Money objects to plain integers for template compatibility
            $totals = [
                'subtotal' => $totalsObjects['subtotal']->amount(),
                'taxTotal' => $totalsObjects['tax_total']->amount(),
                'discountTotal' => $totalsObjects['discount_total']->amount(),
                'grandTotal' => $totalsObjects['grand_total']->amount(),
            ];

            // Get image paths/data for PDF
            $logoPath = null;
            $signaturePath = null;

            // Convert images to base64 for DomPDF
            if ($businessProfile->logo()) {
                try {
                    $logoContent = \Storage::disk($this->fileStorageService->getDisk())->get($businessProfile->logo());
                    $logoPath = 'data:image/png;base64,' . base64_encode($logoContent);
                } catch (\Exception $e) {
                    \Log::warning('Failed to load logo', ['error' => $e->getMessage()]);
                }
            }

            if ($businessProfile->signatureImage()) {
                try {
                    $signatureContent = \Storage::disk($this->fileStorageService->getDisk())->get($businessProfile->signatureImage());
                    $signaturePath = 'data:image/png;base64,' . base64_encode($signatureContent);
                } catch (\Exception $e) {
                    \Log::warning('Failed to load signature', ['error' => $e->getMessage()]);
                }
            }
            
            // Convert items to simple arrays for template compatibility
            $items = array_map(function($item) {
                return (object)[
                    'description' => $item->description(),
                    'dimensions' => $item->dimensions(),
                    'dimensionsValue' => $item->dimensionsValue(),
                    'quantity' => $item->quantity(),
                    'unitPrice' => $item->unitPrice()->amount(),
                    'taxRate' => $item->taxRate(),
                    'discountAmount' => $item->discountAmount()->amount(),
                ];
            }, $document->items());

            $data = [
                'document' => $document,
                'businessProfile' => $businessProfile,
                'customer' => $customer,
                'totals' => $totals,
                'items' => $items,
                'logoPath' => $logoPath,
                'signaturePath' => $signaturePath,
                'isPdf' => true, // Flag to use PDF-compatible CSS
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
                        // Don't wrap - pass entities directly so templates can access all methods
                    }
                }
            }

            // Generate PDF using DomPDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($viewPath, $data)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'DejaVu Sans',
                    'enable_php' => true,
                ]);

            $pdfContent = $pdf->output();

            if (empty($pdfContent)) {
                throw new \RuntimeException('Generated PDF is empty');
            }

            // Return as download
            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $document->type()->value() . '_' . $document->number()->value() . '.pdf"');
        } catch (\Exception $e) {
            \Log::error('PDF download failed', [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'error' => 'Failed to generate PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function previewPdf(int $id)
    {
        try {
            $document = $this->documentRepository->findById($id);

            if (!$document) {
                abort(404);
            }

            $businessProfile = $this->businessProfileRepository->findById($document->businessId());
            $customer = $this->customerRepository->findById($document->customerId());
            
            if (!$businessProfile || !$customer) {
                throw new \RuntimeException('Business profile or customer not found');
            }
            
            $totalsObjects = $document->calculateTotals();
            
            // Convert Money objects to plain integers for template compatibility
            $totals = [
                'subtotal' => $totalsObjects['subtotal']->amount(),
                'taxTotal' => $totalsObjects['tax_total']->amount(),
                'discountTotal' => $totalsObjects['discount_total']->amount(),
                'grandTotal' => $totalsObjects['grand_total']->amount(),
            ];

            // Get image paths/data for preview
            $logoPath = null;
            $signaturePath = null;

            // Convert images to base64 for preview
            if ($businessProfile->logo()) {
                try {
                    $logoContent = \Storage::disk($this->fileStorageService->getDisk())->get($businessProfile->logo());
                    $logoPath = 'data:image/png;base64,' . base64_encode($logoContent);
                } catch (\Exception $e) {
                    \Log::warning('Failed to load logo', ['error' => $e->getMessage()]);
                }
            }

            if ($businessProfile->signatureImage()) {
                try {
                    $signatureContent = \Storage::disk($this->fileStorageService->getDisk())->get($businessProfile->signatureImage());
                    $signaturePath = 'data:image/png;base64,' . base64_encode($signatureContent);
                } catch (\Exception $e) {
                    \Log::warning('Failed to load signature', ['error' => $e->getMessage()]);
                }
            }
            
            // Convert items to simple arrays for template compatibility
            $items = array_map(function($item) {
                return (object)[
                    'description' => $item->description(),
                    'dimensions' => $item->dimensions(),
                    'dimensionsValue' => $item->dimensionsValue(),
                    'quantity' => $item->quantity(),
                    'unitPrice' => $item->unitPrice()->amount(),
                    'taxRate' => $item->taxRate(),
                    'discountAmount' => $item->discountAmount()->amount(),
                ];
            }, $document->items());

            $data = [
                'document' => $document,
                'businessProfile' => $businessProfile,
                'customer' => $customer,
                'totals' => $totals,
                'items' => $items,
                'logoPath' => $logoPath,
                'signaturePath' => $signaturePath,
                'isPdf' => false, // Flag for HTML preview (supports modern CSS)
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
                        // Don't wrap - pass entities directly so templates can access all methods
                    }
                }
            }

            // Return HTML preview instead of PDF
            return response(view($viewPath, $data)->render())
                ->header('Content-Type', 'text/html');
        } catch (\Exception $e) {
            \Log::error('PDF preview failed', [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'error' => 'Failed to generate preview: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function testPdf(int $id)
    {
        $document = $this->documentRepository->findById($id);

        if (!$document) {
            return response()->json(['error' => 'Document not found'], 404);
        }

        $businessProfile = $this->businessProfileRepository->findById($document->businessId());
        $customer = $this->customerRepository->findById($document->customerId());
        $totals = $document->calculateTotals();

        return response()->json([
            'document' => [
                'id' => $document->id(),
                'type' => $document->type()->value(),
                'number' => $document->number()->value(),
                'items_count' => count($document->items()),
                'items' => array_map(function($item) {
                    return [
                        'description' => $item->description(),
                        'quantity' => $item->quantity(),
                        'unit_price' => $item->unitPrice()->amount() / 100,
                        'tax_rate' => $item->taxRate(),
                    ];
                }, $document->items()),
            ],
            'business_profile' => [
                'id' => $businessProfile->id(),
                'name' => $businessProfile->businessName(),
            ],
            'customer' => [
                'id' => $customer->id(),
                'name' => $customer->name(),
            ],
            'totals' => [
                'subtotal' => $totals['subtotal']->amount() / 100,
                'tax_total' => $totals['tax_total']->amount() / 100,
                'discount_total' => $totals['discount_total']->amount() / 100,
                'grand_total' => $totals['grand_total']->amount() / 100,
            ],
        ]);
    }

    public function debugPdf(int $id)
    {
        try {
            $document = $this->documentRepository->findById($id);
            
            if (!$document) {
                return response()->json(['error' => 'Document not found'], 404);
            }

            $businessProfile = $this->businessProfileRepository->findById($document->businessId());
            $customer = $this->customerRepository->findById($document->customerId());
            
            if (!$businessProfile) {
                return response()->json(['error' => 'Business profile not found'], 404);
            }
            
            if (!$customer) {
                return response()->json(['error' => 'Customer not found'], 404);
            }
            
            $totals = $document->calculateTotals();

            // Test data structure
            $data = [
                'document' => $document,
                'businessProfile' => $businessProfile,
                'customer' => $customer,
                'totals' => $totals,
                'logoPath' => null,
                'signaturePath' => null,
            ];

            // Generate PDF using DomPDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('bizdocs.pdf.document', $data)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'DejaVu Sans',
                    'enable_php' => true,
                ]);

            $output = $pdf->output();
            
            return response()->json([
                'success' => true,
                'pdf_size' => strlen($output),
                'is_empty' => empty($output),
                'first_bytes' => substr($output, 0, 100),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error occurred',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    public function sharePdf(int $id)
    {
        $document = $this->documentRepository->findById($id);

        if (!$document) {
            abort(404);
        }

        $customer = $this->customerRepository->findById($document->customerId());

        // Generate PDF on-demand and store temporarily
        $path = $this->pdfGenerationService->generatePdf($document);
        
        // Update document with PDF path
        $document->setPdfPath($path);
        $this->documentRepository->save($document);

        // Get signed URL
        $pdfUrl = $this->pdfGenerationService->getSignedUrl($path);

        // Generate WhatsApp link
        try {
            $whatsappLink = $this->whatsAppSharingService->generateWhatsAppLink(
                $document,
                $customer,
                $pdfUrl
            );

            return response()->json([
                'success' => true,
                'whatsappLink' => $whatsappLink,
                'pdfUrl' => $pdfUrl,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function recordPayment(Request $request, int $id)
    {
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,mobile_money,bank_transfer,cheque,card,other',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'generate_receipt' => 'boolean',
        ]);

        try {
            \Log::info('Recording payment', [
                'document_id' => $id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
            ]);

            $dto = RecordPaymentDTO::fromArray([
                'document_id' => $id,
                'payment_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'generate_receipt' => $validated['generate_receipt'] ?? true,
            ]);

            $result = $this->recordPaymentUseCase->execute($dto);

            \Log::info('Payment recorded successfully', ['result' => $result]);

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            \Log::error('Payment recording failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function cancel(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $dto = \App\Application\BizDocs\DTOs\CancelDocumentDTO::fromArray([
                'document_id' => $id,
                'reason' => $validated['reason'],
            ]);

            $result = $this->cancelDocumentUseCase->execute($dto);

            return response()->json([
                'success' => true,
                'message' => 'Document cancelled successfully',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            \Log::error('Document cancellation failed', [
                'error' => $e->getMessage(),
                'document_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function void(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $dto = \App\Application\BizDocs\DTOs\CancelDocumentDTO::fromArray([
                'document_id' => $id,
                'reason' => $validated['reason'],
            ]);

            $result = $this->voidDocumentUseCase->execute($dto);

            return response()->json([
                'success' => true,
                'message' => 'Document voided successfully',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            \Log::error('Document void failed', [
                'error' => $e->getMessage(),
                'document_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function edit(Request $request, int $id)
    {
        $user = $request->user();
        $businessProfile = $this->businessProfileRepository->findByUserId($user->id);

        if (!$businessProfile) {
            return redirect()->route('bizdocs.setup');
        }

        $document = $this->documentRepository->findById($id);

        if (!$document) {
            abort(404);
        }

        // Only allow editing draft documents
        if ($document->status()->value() !== 'draft') {
            return redirect()
                ->route('bizdocs.documents.show', $id)
                ->with('error', 'Only draft documents can be edited');
        }

        $customers = $this->customerRepository->findByBusiness($businessProfile->id(), perPage: 100);

        // Convert customers to arrays for frontend
        $customersArray = array_map(fn($customer) => [
            'id' => $customer->id(),
            'name' => $customer->name(),
            'address' => $customer->address(),
            'phone' => $customer->phone(),
            'email' => $customer->email(),
        ], $customers);

        return Inertia::render('BizDocs/Documents/Edit', [
            'document' => $document->toArray(),
            'documentType' => $document->type()->value(),
            'businessProfile' => $businessProfile->toArray(),
            'customers' => $customersArray,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $user = $request->user();
        $businessProfile = $this->businessProfileRepository->findByUserId($user->id);

        if (!$businessProfile) {
            return response()->json(['error' => 'Business profile not found'], 404);
        }

        $document = $this->documentRepository->findById($id);

        if (!$document) {
            return response()->json(['error' => 'Document not found'], 404);
        }

        // Only allow editing draft documents
        if ($document->status()->value() !== 'draft') {
            return response()->json(['error' => 'Only draft documents can be edited'], 400);
        }

        $validated = $request->validate([
            'customer_id' => 'required|integer|exists:bizdocs_customers,id',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            'validity_date' => 'nullable|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'payment_instructions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            // Update document using domain methods
            // For now, we'll delete and recreate (simpler approach)
            // In a full implementation, you'd add update methods to the domain entity
            
            $this->documentRepository->delete($id);

            $items = array_map(function ($item) use ($businessProfile) {
                $item['tax_rate'] = $businessProfile->defaultTaxRate();
                return $item;
            }, $validated['items']);

            $dto = \App\Application\BizDocs\DTOs\CreateDocumentDTO::fromArray([
                'business_id' => $businessProfile->id(),
                'customer_id' => $validated['customer_id'],
                'document_type' => $document->type()->value(),
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'] ?? null,
                'validity_date' => $validated['validity_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'payment_instructions' => $validated['payment_instructions'] ?? null,
                'currency' => $businessProfile->defaultCurrency(),
                'items' => $items,
            ]);

            $newDocument = $this->createDocumentUseCase->execute($dto);

            return redirect()
                ->route('bizdocs.documents.show', $newDocument->id())
                ->with('success', 'Document updated successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function convertToInvoice(Request $request, int $id)
    {
        $validated = $request->validate([
            'issue_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            'notes' => 'nullable|string',
            'payment_instructions' => 'nullable|string',
        ]);

        try {
            $dto = \App\Application\BizDocs\DTOs\ConvertQuotationDTO::fromArray([
                'quotation_id' => $id,
                'issue_date' => $validated['issue_date'] ?? null,
                'due_date' => $validated['due_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'payment_instructions' => $validated['payment_instructions'] ?? null,
            ]);

            $result = $this->convertQuotationUseCase->execute($dto);

            return redirect()
                ->route('bizdocs.documents.show', $result['invoice']['id'])
                ->with('success', 'Quotation converted to invoice successfully');
        } catch (\Exception $e) {
            \Log::error('Quotation conversion failed', [
                'error' => $e->getMessage(),
                'quotation_id' => $id,
            ]);

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function duplicate(int $id)
    {
        try {
            $result = $this->duplicateDocumentUseCase->execute($id);

            return redirect()
                ->route('bizdocs.documents.show', $result['duplicate']['id'])
                ->with('success', 'Document duplicated successfully');
        } catch (\Exception $e) {
            \Log::error('Document duplication failed', [
                'error' => $e->getMessage(),
                'document_id' => $id,
            ]);

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function statusHistory(int $id)
    {
        try {
            $history = $this->statusHistoryService->getHistory($id);

            return response()->json([
                'success' => true,
                'history' => $history,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function templateGallery(Request $request)
    {
        $user = $request->user();
        $businessProfile = $this->businessProfileRepository->findByUserId($user->id);

        if (!$businessProfile) {
            return redirect()->route('bizdocs.setup');
        }

        $documentType = $request->query('type', 'invoice');

        // Get ALL templates - they can be used for any document type
        $templates = \App\Infrastructure\BizDocs\Persistence\Eloquent\DocumentTemplateModel::where(function($query) use ($businessProfile) {
                $query->where('visibility', 'industry')
                      ->orWhere(function($q) use ($businessProfile) {
                          $q->where('visibility', 'business')
                            ->where('owner_id', $businessProfile->id());
                      });
            })
            ->orderBy('is_default', 'desc')
            ->orderBy('visibility', 'asc')
            ->orderBy('name', 'asc')
            ->get()
            ->toArray();

        // Get user's current template preference for this document type
        $preference = \DB::table('bizdocs_user_template_preferences')
            ->where('business_id', $businessProfile->id())
            ->where('document_type', $documentType)
            ->first();

        return Inertia::render('BizDocs/Templates/Gallery', [
            'templates' => $templates,
            'documentType' => $documentType,
            'currentTemplateId' => $preference?->template_id,
        ]);
    }

    public function setDefaultTemplate(Request $request, int $id)
    {
        $user = $request->user();
        $businessProfile = $this->businessProfileRepository->findByUserId($user->id);

        if (!$businessProfile) {
            return response()->json(['error' => 'Business profile not found'], 404);
        }

        $validated = $request->validate([
            'document_type' => 'required|string|in:invoice,receipt,quotation,delivery_note,proforma_invoice,credit_note,debit_note,purchase_order',
        ]);

        // Verify template exists
        $template = \App\Infrastructure\BizDocs\Persistence\Eloquent\DocumentTemplateModel::find($id);
        if (!$template) {
            return response()->json(['error' => 'Template not found'], 404);
        }

        // Upsert preference
        \DB::table('bizdocs_user_template_preferences')->updateOrInsert(
            [
                'business_id' => $businessProfile->id(),
                'document_type' => $validated['document_type'],
            ],
            [
                'template_id' => $id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return redirect()->back()->with('success', 'Template set as default');
    }

    public function templatePreview(Request $request, int $id)
    {
        $template = \App\Infrastructure\BizDocs\Persistence\Eloquent\DocumentTemplateModel::find($id);
        
        if (!$template) {
            abort(404);
        }

        $user = $request->user();
        $businessProfile = $this->businessProfileRepository->findByUserId($user->id);

        // Cache key based on template ID and business profile ID
        $cacheKey = "template_preview_{$id}_{$businessProfile->id()}";
        
        // Check if we have a cached version (cache for 1 hour)
        $cachedHtml = \Cache::remember($cacheKey, 3600, function() use ($template, $businessProfile) {
            // Get image paths/data for preview
            $logoPath = null;
            $signaturePath = null;

            // Convert images to base64 for preview (only once, then cached)
            if ($businessProfile->logo()) {
                try {
                    $logoContent = \Storage::disk($this->fileStorageService->getDisk())->get($businessProfile->logo());
                    $logoPath = 'data:image/png;base64,' . base64_encode($logoContent);
                } catch (\Exception $e) {
                    \Log::warning('Failed to load logo for preview', ['error' => $e->getMessage()]);
                }
            }

            if ($businessProfile->signatureImage()) {
                try {
                    $signatureContent = \Storage::disk($this->fileStorageService->getDisk())->get($businessProfile->signatureImage());
                    $signaturePath = 'data:image/png;base64,' . base64_encode($signatureContent);
                } catch (\Exception $e) {
                    \Log::warning('Failed to load signature for preview', ['error' => $e->getMessage()]);
                }
            }

            // Create sample data objects that mimic the entity structure
            // Use objects with callable properties to simulate methods
            $sampleBusinessProfile = new class($businessProfile, $logoPath, $signaturePath) {
                public function __construct(private $profile, private $logo, private $signature) {}
                public function businessName() { return $this->profile->businessName(); }
                public function address() { return $this->profile->address(); }
                public function phone() { return $this->profile->phone(); }
                public function email() { return $this->profile->email(); }
                public function tpin() { return $this->profile->tpin(); }
                public function defaultCurrency() { return $this->profile->defaultCurrency(); }
                public function logo() { return $this->logo; }
                public function signatureImage() { return $this->signature; }
                public function preparedBy() { return $this->profile->preparedBy(); }
                public function bankName() { return $this->profile->bankName(); }
                public function bankAccount() { return $this->profile->bankAccount(); }
                public function bankBranch() { return $this->profile->bankBranch(); }
                public function website() { return $this->profile->website(); }
            };

            $sampleCustomer = new class {
                public function name() { return 'Sample Customer'; }
                public function address() { return '123 Main Street, Lusaka'; }
                public function phone() { return '+260 97X XXX XXX'; }
                public function email() { return 'customer@example.com'; }
                public function tpin() { return '1234567890'; }
            };

            $sampleDocument = new class($template) {
                public function __construct(private $template) {}
                public function number() { 
                    return new class { 
                        public function value() { return 'SAMPLE-001'; } 
                    }; 
                }
                public function issueDate() { return now(); }
                public function dueDate() { return now()->addDays(30); }
                public function type() { 
                    return new class($this->template) { 
                        public function __construct(private $template) {}
                        public function value() { return $this->template->document_type; } 
                    }; 
                }
                public function collectTax() { return true; }
                public function notes() { return 'Thank you for your business. We appreciate your continued partnership.'; }
                public function paymentInstructions() { return 'Payment can be made via bank transfer or mobile money. Please include the invoice number as reference.'; }
                public function terms() { return 'Payment is due within 30 days. Late payments may incur additional charges. All sales are final unless otherwise stated.'; }
            };

            $sampleItems = [
                (object)[
                    'description' => 'Sample Product/Service 1',
                    'dimensions' => '2×3',
                    'dimensionsValue' => 6.0,
                    'quantity' => 2,
                    'unitPrice' => 50000,
                    'taxRate' => 16,
                    'discountAmount' => 0,
                ],
                (object)[
                    'description' => 'Sample Product/Service 2',
                    'dimensions' => null,
                    'dimensionsValue' => 1.0,
                    'quantity' => 1,
                    'unitPrice' => 100000,
                    'taxRate' => 16,
                    'discountAmount' => 5000,
                ],
            ];

            $sampleTotals = [
                'subtotal' => 200000,
                'taxTotal' => 30400,
                'discountTotal' => 5000,
                'grandTotal' => 225400,
            ];

            // Create sample data for preview
            $sampleData = [
                'businessProfile' => $sampleBusinessProfile,
                'customer' => $sampleCustomer,
                'document' => $sampleDocument,
                'items' => $sampleItems,
                'totals' => $sampleTotals,
                'logoPath' => $logoPath,
                'signaturePath' => $signaturePath,
                'template' => $template,
            ];

            // Determine which template layout to use
            $viewPath = 'bizdocs.pdf.template-preview';
            
            if (!empty($template->layout_file)) {
                $specificView = 'bizdocs.pdf.templates.' . $template->layout_file;
                if (view()->exists($specificView)) {
                    $viewPath = $specificView;
                }
            }

            // Generate and return HTML
            return view($viewPath, $sampleData)->render();
        });

        // Return cached HTML with aggressive caching headers
        return response($cachedHtml)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=3600, immutable') // Browser cache for 1 hour, immutable
            ->header('Expires', now()->addHour()->toRfc7231String())
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Vary', 'Accept-Encoding'); // Enable compression
    }
}
