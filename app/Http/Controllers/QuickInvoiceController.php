<?php

namespace App\Http\Controllers;

use App\Domain\QuickInvoice\Exceptions\DocumentNotFoundException;
use App\Domain\QuickInvoice\Exceptions\UnauthorizedAccessException;
use App\Domain\QuickInvoice\Services\DocumentService;
use App\Domain\QuickInvoice\Services\PdfGeneratorService;
use App\Domain\QuickInvoice\Services\PdfMergerService;
use App\Domain\QuickInvoice\Services\ShareService;
use App\Domain\QuickInvoice\ValueObjects\TemplateStyle;
use App\Http\Requests\QuickInvoice\CreateDocumentRequest;
use App\Http\Requests\QuickInvoice\SendEmailRequest;
use App\Http\Requests\QuickInvoice\UploadLogoRequest;
use App\Models\QuickInvoice\AdminSetting;
use App\Models\QuickInvoice\UsageTracking;
use App\Models\QuickInvoice\UserSubscription;
use App\Models\QuickInvoiceProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class QuickInvoiceController extends Controller
{
    public function __construct(
        private readonly DocumentService $documentService,
        private readonly PdfGeneratorService $pdfGenerator,
        private readonly ShareService $shareService,
        private readonly PdfMergerService $pdfMerger
    ) {}

    public function dashboard(Request $request): Response
    {
        $dashboardController = new \App\Http\Controllers\QuickInvoice\DashboardController();
        return $dashboardController->index($request);
    }

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

        // Only support the 5 original working templates
        $templateParam = $request->query('template', 'classic');
        $validTemplates = ['classic', 'modern', 'minimal', 'professional', 'bold'];
        
        // If invalid template, default to classic
        if (!in_array($templateParam, $validTemplates)) {
            $templateParam = 'classic';
        }

        return Inertia::render('QuickInvoice/Create', [
            'documentType' => $request->query('type', 'invoice'),
            'initialTemplate' => $templateParam,
            'currencies' => $this->getCurrencies(),
            'templates' => TemplateStyle::all(),
            'savedProfile' => $savedProfile,
            'editDocument' => null,
        ]);
    }
    

    /**
     * Edit an existing document
     */
    public function edit(string $id): Response
    {
        try {
            $document = $this->documentService->findDocumentWithAccess(
                $id, 
                auth()->id(), 
                session()->getId()
            );
            
            $savedProfile = null;
            if (auth()->check()) {
                $profile = QuickInvoiceProfile::where('user_id', auth()->id())->first();
                if ($profile) {
                    $savedProfile = $profile->toArray();
                }
            }

            return Inertia::render('QuickInvoice/Create', [
                'documentType' => $document->type()->value,
                'initialTemplate' => $document->template(),
                'currencies' => $this->getCurrencies(),
                'templates' => TemplateStyle::all(),
                'savedProfile' => $savedProfile,
                'editDocument' => $document->toArray(),
            ]);
        } catch (DocumentNotFoundException $e) {
            abort(404, 'Document not found');
        } catch (UnauthorizedAccessException $e) {
            abort(403, 'You do not have permission to edit this document');
        }
    }

    /**
     * Generate document - saves data only, PDF generated on-demand
     */
    public function generate(CreateDocumentRequest $request): JsonResponse
    {
        \Log::info('QuickInvoiceController - generate START', [
            'has_template' => $request->has('template'),
            'template_value' => $request->input('template'),
            'template_type' => gettype($request->input('template')),
            'all_keys' => array_keys($request->all()),
        ]);
        
        $data = $request->validated();
        
        \Log::info('QuickInvoiceController - After validation', [
            'has_template' => isset($data['template']),
            'template_value' => $data['template'] ?? 'NOT SET',
            'template_type' => gettype($data['template'] ?? null),
        ]);
        
        $data['session_id'] = session()->getId();
        $data['user_id'] = auth()->id();

        // Check usage limits if enabled
        if (AdminSetting::isUsageLimitsEnabled() && auth()->check()) {
            $subscription = UserSubscription::getOrCreateFreeSubscription(auth()->id());
            
            if (!$subscription->canCreateDocument()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have reached your monthly document limit. Please upgrade your subscription to create more documents.',
                    'limit_reached' => true,
                    'subscription' => [
                        'tier_name' => $subscription->tier->name,
                        'documents_per_month' => $subscription->tier->documents_per_month,
                        'remaining_documents' => $subscription->getRemainingDocuments(),
                        'usage_percentage' => $subscription->getUsagePercentage(),
                    ],
                ], 429);
            }
        }

        \Log::info('QuickInvoice generate request', [
            'prepared_by' => $data['prepared_by'] ?? 'NOT SET',
            'signature' => isset($data['signature']) ? 'SET' : 'NOT SET',
            'document_id' => $data['document_id'] ?? 'NEW',
            'has_attachments' => $request->hasFile('attachments'),
            'attachment_count' => $request->hasFile('attachments') ? count($request->file('attachments')) : 0,
            'has_library_attachments' => $request->has('library_attachments'),
        ]);

        // Handle attachments if present
        $attachmentPaths = [];
        
        // Handle uploaded files (new attachments)
        if ($request->hasFile('attachments')) {
            \Log::info('Processing uploaded attachments', [
                'count' => count($request->file('attachments')),
            ]);
            
            foreach ($request->file('attachments') as $index => $file) {
                \Log::info('Uploading attachment to S3', [
                    'index' => $index,
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ]);
                
                // Store in S3 (DigitalOcean Spaces)
                $path = $file->store('quick-invoice/attachments/' . session()->getId(), 's3');
                
                \Log::info('Attachment uploaded to S3', [
                    'path' => $path,
                ]);
                
                $attachmentPaths[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                    'source' => 'upload', // Mark as uploaded file
                ];
            }
        }
        
        // Handle library attachments (references only)
        if ($request->has('library_attachments') && auth()->check()) {
            $libraryIds = json_decode($request->input('library_attachments'), true);
            
            if (is_array($libraryIds) && !empty($libraryIds)) {
                \Log::info('Processing library attachments', [
                    'count' => count($libraryIds),
                    'ids' => $libraryIds,
                ]);
                
                // Get library attachments for this user
                $libraryAttachments = \App\Models\QuickInvoiceAttachmentLibrary::where('user_id', auth()->id())
                    ->whereIn('id', $libraryIds)
                    ->get();
                
                foreach ($libraryAttachments as $libAttachment) {
                    $attachmentPaths[] = [
                        'name' => $libAttachment->original_filename,
                        'path' => $libAttachment->path,
                        'size' => $libAttachment->size,
                        'type' => $libAttachment->type,
                        'source' => 'library', // Mark as library reference
                        'library_id' => $libAttachment->id, // Store library ID for reference
                    ];
                }
                
                \Log::info('Library attachments added', [
                    'count' => count($libraryAttachments),
                ]);
            }
        }
        
        if (!empty($attachmentPaths)) {
            $data['attachments'] = $attachmentPaths;
            
            \Log::info('All attachments processed', [
                'attachment_count' => count($attachmentPaths),
                'attachments' => $attachmentPaths,
            ]);
        } else {
            \Log::info('No attachments in request');
        }

        // Check if this is an update to an existing document
        if (!empty($data['document_id'])) {
            try {
                // Verify access to the existing document
                $existingDocument = $this->documentService->findDocumentWithAccess(
                    $data['document_id'],
                    auth()->id(),
                    session()->getId()
                );
                
                // Delete the old document and create a new one with the same ID
                $this->documentService->deleteDocument($data['document_id'], auth()->id(), session()->getId());
                
                // Keep the same document number for updates
                if (empty($data['document_number'])) {
                    $data['document_number'] = $existingDocument->documentNumber()->value();
                }
            } catch (DocumentNotFoundException $e) {
                return response()->json(['success' => false, 'message' => 'Document not found'], 404);
            } catch (UnauthorizedAccessException $e) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
        } else {
            // Auto-generate document number if not provided and user is authenticated
            if (empty($data['document_number']) && auth()->check()) {
                $profile = QuickInvoiceProfile::where('user_id', auth()->id())->first();
                
                if ($profile) {
                    // Map document types to profile fields
                    $typeMap = [
                        'invoice' => 'invoice',
                        'quotation' => 'quotation',
                        'receipt' => 'receipt',
                        'delivery_note' => 'delivery_note',
                    ];
                    
                    $documentType = $typeMap[$data['document_type']] ?? 'invoice';
                    
                    // Generate document number
                    $data['document_number'] = $profile->generateDocumentNumber($documentType);
                    
                    // Increment the counter for next time
                    $profile->incrementDocumentNumber($documentType);
                    
                    \Log::info('Auto-generated document number', [
                        'type' => $documentType,
                        'number' => $data['document_number'],
                    ]);
                }
            }
        }

        // Create and save the document (data only, no PDF storage)
        $document = $this->documentService->createDocument($data);
        $this->documentService->saveDocument($document);

        // Track usage
        UsageTracking::track(
            documentType: $data['document_type'],
            template: $data['template'] ?? 'classic',
            userId: auth()->id(),
            sessionId: session()->getId(),
            integrationSource: 'standalone'
        );

        // Increment user's subscription usage if authenticated
        if (auth()->check()) {
            $subscription = UserSubscription::getCurrentSubscription(auth()->id());
            if ($subscription) {
                $subscription->incrementUsage();
            }
        }

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
        
        // Add cache-busting headers
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        
        try {
            // For Quick Invoice, allow public download access via UUID
            $document = $this->documentService->findDocument($id);
            
            \Log::info('PdfGeneratorService - Generating PDF for download', [
                'document_id' => $id,
                'template' => $document->template(),
            ]);
            
            // Generate main PDF
            $mainPdf = $this->pdfGenerator->output($document);
            
            // Check if document has attachments
            $documentData = $document->toArray();
            
            \Log::info('QuickInvoice downloadPdf - checking attachments', [
                'document_id' => $id,
                'has_attachments' => !empty($documentData['attachments']),
                'attachment_count' => !empty($documentData['attachments']) ? count($documentData['attachments']) : 0,
                'attachments' => $documentData['attachments'] ?? null,
            ]);
            
            if (!empty($documentData['attachments'])) {
                // Load attachment files from S3
                $attachmentFiles = [];
                foreach ($documentData['attachments'] as $attachment) {
                    try {
                        \Log::info('Loading attachment from S3', [
                            'path' => $attachment['path'],
                            'name' => $attachment['name'],
                        ]);
                        
                        // Get file content from S3
                        $fileContent = Storage::disk('s3')->get($attachment['path']);
                        
                        \Log::info('Attachment loaded from S3', [
                            'path' => $attachment['path'],
                            'size' => strlen($fileContent),
                        ]);
                        
                        // Create temporary file
                        $tempPath = tempnam(sys_get_temp_dir(), 'attachment_');
                        file_put_contents($tempPath, $fileContent);
                        
                        // Create UploadedFile instance from temp file
                        $attachmentFiles[] = new \Illuminate\Http\UploadedFile(
                            $tempPath,
                            $attachment['name'],
                            $attachment['type'],
                            null,
                            true // test mode - don't validate
                        );
                        
                        \Log::info('Attachment file created', [
                            'temp_path' => $tempPath,
                            'name' => $attachment['name'],
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to load attachment from S3', [
                            'path' => $attachment['path'],
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                }
                
                // Merge with attachments if any were found
                if (!empty($attachmentFiles)) {
                    \Log::info('Merging PDF with attachments', [
                        'attachment_count' => count($attachmentFiles),
                    ]);
                    
                    $mergedPdf = $this->pdfMerger->mergeWithAttachments($mainPdf, $attachmentFiles);
                    
                    \Log::info('PDF merged successfully', [
                        'merged_size' => strlen($mergedPdf),
                    ]);
                    
                    // Clean up temp files
                    foreach ($attachmentFiles as $file) {
                        @unlink($file->getRealPath());
                    }
                    
                    return response($mergedPdf)
                        ->header('Content-Type', 'application/pdf')
                        ->header('Content-Disposition', 'attachment; filename="' . $document->documentNumber()->value() . '_with_attachments.pdf"');
                } else {
                    \Log::warning('No attachment files loaded', [
                        'document_id' => $id,
                    ]);
                }
            }
            
            // No attachments, return main PDF
            return response($mainPdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $document->documentNumber()->value() . '.pdf"');
                
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
        
        // Add cache-busting headers
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        
        try {
            // For Quick Invoice, allow public view access via UUID
            $document = $this->documentService->findDocument($id);
            
            \Log::info('PdfGeneratorService - Generating PDF for view', [
                'document_id' => $id,
                'template' => $document->template(),
            ]);
            
            // Generate main PDF
            $mainPdf = $this->pdfGenerator->output($document);
            
            // Check if document has attachments
            $documentData = $document->toArray();
            if (!empty($documentData['attachments'])) {
                // Load attachment files from S3
                $attachmentFiles = [];
                foreach ($documentData['attachments'] as $attachment) {
                    try {
                        // Get file content from S3
                        $fileContent = Storage::disk('s3')->get($attachment['path']);
                        
                        // Create temporary file
                        $tempPath = tempnam(sys_get_temp_dir(), 'attachment_');
                        file_put_contents($tempPath, $fileContent);
                        
                        // Create UploadedFile instance from temp file
                        $attachmentFiles[] = new \Illuminate\Http\UploadedFile(
                            $tempPath,
                            $attachment['name'],
                            $attachment['type'],
                            null,
                            true // test mode - don't validate
                        );
                    } catch (\Exception $e) {
                        \Log::warning('Failed to load attachment from S3', [
                            'path' => $attachment['path'],
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                // Merge with attachments if any were found
                if (!empty($attachmentFiles)) {
                    $mergedPdf = $this->pdfMerger->mergeWithAttachments($mainPdf, $attachmentFiles);
                    
                    // Clean up temp files
                    foreach ($attachmentFiles as $file) {
                        @unlink($file->getRealPath());
                    }
                    
                    return response($mergedPdf)
                        ->header('Content-Type', 'application/pdf')
                        ->header('Content-Disposition', 'inline; filename="' . $document->documentNumber()->value() . '_with_attachments.pdf"');
                }
            }
            
            // No attachments, return main PDF
            return response($mainPdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $document->documentNumber()->value() . '.pdf"');
                
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
                'prepared_by' => 'nullable|string|max:255',
                'tax_number' => 'nullable|string|max:50',
                'default_tax_rate' => 'nullable|numeric|min:0|max:100',
                'default_discount_rate' => 'nullable|numeric|min:0|max:100',
                'default_notes' => 'nullable|string|max:1000',
                'default_terms' => 'nullable|string|max:1000',
                // Numbering settings
                'invoice_prefix' => 'nullable|string|max:20',
                'invoice_next_number' => 'nullable|integer|min:1',
                'invoice_number_padding' => 'nullable|integer|min:1|max:10',
                'quotation_prefix' => 'nullable|string|max:20',
                'quotation_next_number' => 'nullable|integer|min:1',
                'quotation_number_padding' => 'nullable|integer|min:1|max:10',
                'receipt_prefix' => 'nullable|string|max:20',
                'receipt_next_number' => 'nullable|integer|min:1',
                'receipt_number_padding' => 'nullable|integer|min:1|max:10',
                'delivery_note_prefix' => 'nullable|string|max:20',
                'delivery_note_next_number' => 'nullable|integer|min:1',
                'delivery_note_number_padding' => 'nullable|integer|min:1|max:10',
                // Template preferences
                'default_template' => 'nullable|string|max:50',
                'default_color' => 'nullable|string|max:7',
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

    /**
     * Get user's attachment library
     */
    public function getAttachmentLibrary(): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $attachments = \App\Models\QuickInvoiceAttachmentLibrary::where('user_id', auth()->id())
            ->orderBy('name')
            ->get()
            ->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'name' => $attachment->name,
                    'original_filename' => $attachment->original_filename,
                    'path' => $attachment->path,
                    'type' => $attachment->type,
                    'size' => $attachment->size,
                    'formatted_size' => $attachment->formatted_size,
                    'description' => $attachment->description,
                    'is_image' => $attachment->isImage(),
                    'is_pdf' => $attachment->isPdf(),
                    'created_at' => $attachment->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'success' => true,
            'attachments' => $attachments,
        ]);
    }

    /**
     * Download attachment from library
     */
    public function downloadLibraryAttachment(int $id)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        try {
            $attachment = \App\Models\QuickInvoiceAttachmentLibrary::where('user_id', auth()->id())
                ->findOrFail($id);

            // Get file from S3
            $fileContent = Storage::disk('s3')->get($attachment->path);

            return response($fileContent)
                ->header('Content-Type', $attachment->type)
                ->header('Content-Disposition', 'attachment; filename="' . $attachment->original_filename . '"');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Attachment not found'], 404);
        } catch (\Exception $e) {
            \Log::error('Failed to download library attachment', [
                'user_id' => auth()->id(),
                'attachment_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to download attachment',
            ], 500);
        }
    }

    /**
     * Save attachment to library
     */
    public function saveToLibrary(Request $request): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $file = $request->file('file');
            
            // Store in S3
            $path = $file->store('quick-invoice/library/' . auth()->id(), 's3');
            
            // Create library entry
            $attachment = \App\Models\QuickInvoiceAttachmentLibrary::create([
                'user_id' => auth()->id(),
                'name' => $request->name,
                'original_filename' => $file->getClientOriginalName(),
                'path' => $path,
                'type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'description' => $request->description,
            ]);

            return response()->json([
                'success' => true,
                'attachment' => [
                    'id' => $attachment->id,
                    'name' => $attachment->name,
                    'original_filename' => $attachment->original_filename,
                    'path' => $attachment->path,
                    'type' => $attachment->type,
                    'size' => $attachment->size,
                    'formatted_size' => $attachment->formatted_size,
                    'description' => $attachment->description,
                    'is_image' => $attachment->isImage(),
                    'is_pdf' => $attachment->isPdf(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to save attachment to library', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save attachment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete attachment from library
     */
    public function deleteFromLibrary(int $id): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        try {
            $attachment = \App\Models\QuickInvoiceAttachmentLibrary::where('user_id', auth()->id())
                ->findOrFail($id);

            // Delete from S3
            Storage::disk('s3')->delete($attachment->path);

            // Delete from database
            $attachment->delete();

            return response()->json(['success' => true]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Attachment not found'], 404);
        } catch (\Exception $e) {
            \Log::error('Failed to delete attachment from library', [
                'user_id' => auth()->id(),
                'attachment_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attachment',
            ], 500);
        }
    }

    /**
     * Update attachment in library
     */
    public function updateLibraryAttachment(int $id, Request $request): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $attachment = \App\Models\QuickInvoiceAttachmentLibrary::where('user_id', auth()->id())
                ->findOrFail($id);

            $attachment->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return response()->json([
                'success' => true,
                'attachment' => [
                    'id' => $attachment->id,
                    'name' => $attachment->name,
                    'description' => $attachment->description,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Attachment not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update attachment',
            ], 500);
        }
    }
}
