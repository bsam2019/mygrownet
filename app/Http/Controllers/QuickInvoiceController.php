<?php

namespace App\Http\Controllers;

use App\Domain\QuickInvoice\Exceptions\DocumentNotFoundException;
use App\Domain\QuickInvoice\Exceptions\UnauthorizedAccessException;
use App\Domain\QuickInvoice\Services\AttachmentLibraryService;
use App\Domain\QuickInvoice\Services\DocumentService;
use App\Domain\QuickInvoice\Services\PdfGeneratorService;
use App\Domain\QuickInvoice\Services\PdfMergerService;
use App\Domain\QuickInvoice\Services\ProfileService;
use App\Domain\QuickInvoice\Services\ShareService;
use App\Domain\QuickInvoice\Services\SubscriptionService;
use App\Domain\QuickInvoice\Repositories\AdminSettingRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\UsageTrackingRepositoryInterface;
use App\Domain\QuickInvoice\ValueObjects\TemplateStyle;
use App\Http\Requests\QuickInvoice\CreateDocumentRequest;
use App\Http\Requests\QuickInvoice\SendEmailRequest;
use App\Http\Requests\QuickInvoice\UploadLogoRequest;
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
        private readonly PdfMergerService $pdfMerger,
        private readonly ProfileService $profileService,
        private readonly SubscriptionService $subscriptionService,
        private readonly AttachmentLibraryService $attachmentLibraryService,
        private readonly AdminSettingRepositoryInterface $adminSetting,
        private readonly UsageTrackingRepositoryInterface $usageTracking,
    ) {}

    public function dashboard(Request $request): Response
    {
        $dashboardController = app(\App\Http\Controllers\QuickInvoice\DashboardController::class);
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
            $profile = $this->profileService->getProfile(auth()->id());
            if ($profile) {
                $savedProfile = $profile->toArray();
            }
        }

        $templateParam = $request->query('template', 'classic');
        $validTemplates = ['classic', 'modern', 'minimal', 'professional', 'bold'];

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
                $profile = $this->profileService->getProfile(auth()->id());
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

    public function generate(CreateDocumentRequest $request): JsonResponse
    {
        $data = $request->validated();

        $data['session_id'] = session()->getId();
        $data['user_id'] = auth()->id();

        if ($this->adminSetting->isUsageLimitsEnabled() && auth()->check()) {
            $limitInfo = $this->subscriptionService->hasReachedLimit(auth()->id());

            if ($limitInfo !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have reached your monthly document limit. Please upgrade your subscription to create more documents.',
                    'limit_reached' => true,
                    'subscription' => $limitInfo,
                ], 429);
            }
        }

        $attachmentPaths = [];

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('quick-invoice/attachments/' . session()->getId(), 's3');

                $attachmentPaths[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                    'source' => 'upload',
                ];
            }
        }

        if ($request->has('library_attachments') && auth()->check()) {
            $libraryIds = json_decode($request->input('library_attachments'), true);

            if (is_array($libraryIds) && !empty($libraryIds)) {
                $libraryAttachments = $this->attachmentLibraryService->getByIds(auth()->id(), $libraryIds);

                foreach ($libraryAttachments as $libAttachment) {
                    $attachmentPaths[] = [
                        'name' => $libAttachment['original_filename'],
                        'path' => $libAttachment['path'],
                        'size' => $libAttachment['size'],
                        'type' => $libAttachment['type'],
                        'source' => 'library',
                        'library_id' => $libAttachment['id'],
                    ];
                }
            }
        }

        if (!empty($attachmentPaths)) {
            $data['attachments'] = $attachmentPaths;
        }

        if (!empty($data['document_id'])) {
            try {
                $existingDocument = $this->documentService->findDocumentWithAccess(
                    $data['document_id'],
                    auth()->id(),
                    session()->getId()
                );

                $this->documentService->deleteDocument($data['document_id'], auth()->id(), session()->getId());

                if (empty($data['document_number'])) {
                    $data['document_number'] = $existingDocument->documentNumber()->value();
                }
            } catch (DocumentNotFoundException $e) {
                return response()->json(['success' => false, 'message' => 'Document not found'], 404);
            } catch (UnauthorizedAccessException $e) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
        } else {
            if (empty($data['document_number']) && auth()->check()) {
                $number = $this->profileService->generateDocumentNumber(auth()->id(), $data['document_type']);

                if ($number) {
                    $data['document_number'] = $number;
                }
            }
        }

        $document = $this->documentService->createDocument($data);
        $this->documentService->saveDocument($document);

        $this->usageTracking->track(
            $data['document_type'],
            $data['template'] ?? 'classic',
            auth()->id(),
            session()->getId(),
            'standalone'
        );

        if (auth()->check()) {
            $this->subscriptionService->trackUsage(auth()->id());
        }

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

    public function downloadPdf(string $id)
    {
        set_time_limit(120);

        try {
            $document = $this->documentService->findDocument($id);

            $mainPdf = $this->pdfGenerator->output($document);

            $documentData = $document->toArray();

            if (!empty($documentData['attachments'])) {
                $attachmentFiles = [];
                foreach ($documentData['attachments'] as $attachment) {
                    try {
                        $fileContent = Storage::disk('s3')->get($attachment['path']);

                        $tempPath = tempnam(sys_get_temp_dir(), 'attachment_');
                        file_put_contents($tempPath, $fileContent);

                        $attachmentFiles[] = new \Illuminate\Http\UploadedFile(
                            $tempPath,
                            $attachment['name'],
                            $attachment['type'],
                            null,
                            true
                        );
                    } catch (\Exception $e) {
                        \Log::error('Failed to load attachment from S3', ['path' => $attachment['path'], 'error' => $e->getMessage()]);
                    }
                }

                if (!empty($attachmentFiles)) {
                    $mergedPdf = $this->pdfMerger->mergeWithAttachments($mainPdf, $attachmentFiles);

                    foreach ($attachmentFiles as $file) {
                        @unlink($file->getRealPath());
                    }

                    return response($mergedPdf)
                        ->header('Content-Type', 'application/pdf')
                        ->header('Content-Disposition', 'attachment; filename="' . $document->documentNumber()->value() . '_with_attachments.pdf"')
                        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                        ->header('Pragma', 'no-cache');
                }
            }

            return response($mainPdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $document->documentNumber()->value() . '.pdf"')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache');

        } catch (DocumentNotFoundException $e) {
            abort(404, 'Document not found');
        } catch (\Exception $e) {
            \Log::error('PDF generation failed', ['id' => $id, 'error' => $e->getMessage()]);
            abort(500, 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    public function viewPdf(string $id)
    {
        set_time_limit(120);

        try {
            $document = $this->documentService->findDocument($id);

            $mainPdf = $this->pdfGenerator->output($document);

            $documentData = $document->toArray();
            if (!empty($documentData['attachments'])) {
                $attachmentFiles = [];
                foreach ($documentData['attachments'] as $attachment) {
                    try {
                        $fileContent = Storage::disk('s3')->get($attachment['path']);

                        $tempPath = tempnam(sys_get_temp_dir(), 'attachment_');
                        file_put_contents($tempPath, $fileContent);

                        $attachmentFiles[] = new \Illuminate\Http\UploadedFile(
                            $tempPath,
                            $attachment['name'],
                            $attachment['type'],
                            null,
                            true
                        );
                    } catch (\Exception $e) {
                        \Log::warning('Failed to load attachment from S3', ['path' => $attachment['path'], 'error' => $e->getMessage()]);
                    }
                }

                if (!empty($attachmentFiles)) {
                    $mergedPdf = $this->pdfMerger->mergeWithAttachments($mainPdf, $attachmentFiles);

                    foreach ($attachmentFiles as $file) {
                        @unlink($file->getRealPath());
                    }

                    return response($mergedPdf)
                        ->header('Content-Type', 'application/pdf')
                        ->header('Content-Disposition', 'inline; filename="' . $document->documentNumber()->value() . '_with_attachments.pdf"')
                        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                        ->header('Pragma', 'no-cache');
                }
            }

            return response($mainPdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $document->documentNumber()->value() . '.pdf"')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache');

        } catch (DocumentNotFoundException $e) {
            abort(404, 'Document not found');
        } catch (\Exception $e) {
            \Log::error('PDF streaming failed', ['id' => $id, 'error' => $e->getMessage()]);
            abort(500, 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    public function sendEmail(SendEmailRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['session_id'] = session()->getId();
        $data['user_id'] = auth()->id();

        $document = $this->documentService->createDocument($data);

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

    public function getWhatsAppLink(string $id): JsonResponse
    {
        try {
            $document = $this->documentService->findDocumentWithAccess(
                $id, auth()->id(), session()->getId()
            );

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
                'default_template' => 'nullable|string|max:50',
                'default_color' => 'nullable|string|max:7',
            ]);

            $profile = $this->profileService->saveProfile(auth()->id(), $validated);

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
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save profile: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getProfile(): JsonResponse
    {
        $profile = $this->profileService->getProfile(auth()->id());

        return response()->json([
            'success' => true,
            'profile' => $profile?->toArray(),
        ]);
    }

    public function getAttachmentLibrary(): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $attachments = $this->attachmentLibraryService->getAttachments(auth()->id());

        return response()->json([
            'success' => true,
            'attachments' => $attachments,
        ]);
    }

    public function downloadLibraryAttachment(int $id)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        try {
            $download = $this->attachmentLibraryService->downloadAttachment($id, auth()->id());

            if (!$download) {
                return response()->json(['success' => false, 'message' => 'Attachment not found'], 404);
            }

            return response($download['content'])
                ->header('Content-Type', $download['type'])
                ->header('Content-Disposition', 'attachment; filename="' . $download['filename'] . '"');
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

    public function saveToLibrary(Request $request): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $attachment = $this->attachmentLibraryService->saveAttachment(
                auth()->id(),
                $request->name,
                $request->file('file'),
                $request->description
            );

            return response()->json([
                'success' => true,
                'attachment' => $attachment,
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

    public function deleteFromLibrary(int $id): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        try {
            $deleted = $this->attachmentLibraryService->deleteAttachment($id, auth()->id());

            if (!$deleted) {
                return response()->json(['success' => false, 'message' => 'Attachment not found'], 404);
            }

            return response()->json(['success' => true]);
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
            $attachment = $this->attachmentLibraryService->updateAttachment($id, auth()->id(), [
                'name' => $request->name,
                'description' => $request->description,
            ]);

            if (!$attachment) {
                return response()->json(['success' => false, 'message' => 'Attachment not found'], 404);
            }

            return response()->json([
                'success' => true,
                'attachment' => $attachment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update attachment',
            ], 500);
        }
    }

    public function subscriptionPlans(): Response
    {
        $user = auth()->user();

        return Inertia::render('QuickInvoice/Subscription', [
            'plans' => $this->subscriptionService->getPlans(),
            'currentSubscription' => $user ? $this->subscriptionService->getUserSubscriptionStatus($user->id) : null,
        ]);
    }

    public function checkout(int $tierId): \Inertia\Response|\Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        try {
            $checkout = $this->subscriptionService->initiateUpgrade($user->id, $tierId);
            return Inertia::render('QuickInvoice/Checkout', [
                'checkout' => $checkout,
            ]);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('quick-invoice.subscription.plans')
                ->with('error', $e->getMessage());
        }
    }

    public function upgrade(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'tier_id' => 'required|exists:quick_invoice_subscription_tiers,id',
            'payment_method' => 'required|in:wallet',
        ]);

        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        try {
            $this->subscriptionService->completePayment($user->id, (int) $request->tier_id, $request->payment_method);
            return redirect()->route('quick-invoice.subscription.plans')
                ->with('success', 'Subscription upgraded successfully!');
        } catch (\RuntimeException $e) {
            return redirect()->route('quick-invoice.subscription.checkout', $request->tier_id)
                ->with('error', $e->getMessage());
        }
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