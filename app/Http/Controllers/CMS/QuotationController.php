<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Core\Services\QuotationService;
use App\Infrastructure\Persistence\Eloquent\CMS\QuotationModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CustomerModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuotationController extends Controller
{
    public function __construct(
        private QuotationService $quotationService
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $query = QuotationModel::with(['customer', 'createdBy.user'])
            ->where('company_id', $companyId);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('quotation_number', 'like', "%{$request->search}%")
                  ->orWhereHas('customer', function ($q) use ($request) {
                      $q->where('name', 'like', "%{$request->search}%");
                  });
            });
        }

        $quotations = $query->orderBy('quotation_date', 'desc')
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($quotation) => [
                'id' => $quotation->id,
                'quotation_number' => $quotation->quotation_number,
                'quotation_date' => $quotation->quotation_date,
                'expiry_date' => $quotation->expiry_date,
                'status' => $quotation->status,
                'total_amount' => $quotation->total_amount,
                'customer' => [
                    'id' => $quotation->customer->id,
                    'name' => $quotation->customer->name,
                ],
                'createdBy' => [
                    'user' => [
                        'name' => $quotation->createdBy->user->name,
                    ],
                ],
            ]);

        $summary = [
            'total_quotations' => QuotationModel::where('company_id', $companyId)->count(),
            'draft_count' => QuotationModel::where('company_id', $companyId)->where('status', 'draft')->count(),
            'sent_count' => QuotationModel::where('company_id', $companyId)->where('status', 'sent')->count(),
            'accepted_count' => QuotationModel::where('company_id', $companyId)->where('status', 'accepted')->count(),
            'total_value' => QuotationModel::where('company_id', $companyId)->sum('total_amount'),
        ];

        return Inertia::render('CMS/Quotations/Index', [
            'quotations' => $quotations,
            'summary' => $summary,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function create(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $customers = CustomerModel::where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Get document defaults from company settings
        $settingsService = app(\App\Domain\CMS\Core\Services\CompanySettingsService::class);
        $defaults = $settingsService->getDocumentDefaults($companyId, 'quotation');

        return Inertia::render('CMS/Quotations/Create', [
            'customers'        => $customers,
            'defaultNotes'     => $defaults['notes'] ?? '',
            'defaultTerms'     => $defaults['terms'] ?? '',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'              => 'required|exists:cms_customers,id',
            'quotation_date'           => 'required|date',
            'expiry_date'              => 'nullable|date|after:quotation_date',
            'items'                    => 'required|array|min:1',
            'items.*.description'      => 'required|string',
            'items.*.quantity'         => 'required|numeric|min:0',
            'items.*.unit_price'       => 'required|numeric|min:0',
            'items.*.tax_rate'         => 'nullable|numeric|min:0',
            'items.*.discount_rate'    => 'nullable|numeric|min:0',
            'items.*.line_total'       => 'required|numeric|min:0',
            'items.*.dimensions'       => 'nullable|string|max:100',
            'items.*.dimensions_value' => 'nullable|numeric|min:0',
            'tax_amount'               => 'nullable|numeric|min:0',
            'discount_amount'          => 'nullable|numeric|min:0',
            'notes'                    => 'nullable|string',
            'terms'                    => 'nullable|string',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $userId = $request->user()->cmsUser->id;

        $quotation = $this->quotationService->createQuotation($validated, $companyId, $userId);

        return redirect()->route('cms.quotations.show', $quotation->id)
            ->with('success', 'Quotation created successfully');
    }

    public function show(Request $request, int $id)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $quotation = QuotationModel::with([
            'customer', 'items', 'createdBy.user',
            'convertedToJob', 'measurement.items',
        ])
            ->where('company_id', $companyId)
            ->findOrFail($id);

        // Profit summary if linked to a measurement
        $profitSummary = null;
        if ($quotation->measurement) {
            $measurementService = app(\App\Domain\CMS\Core\Services\MeasurementService::class);
            $profitSummary = $measurementService->getProfitSummary($quotation->measurement);
        }

        return Inertia::render('CMS/Quotations/Show', [
            'quotation'     => $quotation,
            'profitSummary' => $profitSummary,
        ]);
    }

    public function send(Request $request, int $id)
    {
        $userId = $request->user()->cmsUser->id;
        
        $quotation = $this->quotationService->sendQuotation($id, $userId);

        return back()->with('success', 'Quotation marked as sent');
    }

    /**
     * Send quotation via email with PDF attachment — generates PDF on demand.
     */
    public function sendViaEmail(Request $request, int $id)
    {
        $validated = $request->validate([
            'email'   => 'required|email',
            'message' => 'nullable|string|max:1000',
        ]);

        $cmsUser   = $request->user()->cmsUser;
        $quotation = QuotationModel::with(['customer', 'items', 'company'])
            ->where('company_id', $cmsUser->company_id)
            ->findOrFail($id);

        // Generate PDF on demand (not stored)
        $pdfContent = $this->generateQuotationPdf($quotation);
        $tmpPath    = storage_path("app/temp/quotation-{$quotation->quotation_number}.pdf");

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        file_put_contents($tmpPath, $pdfContent);

        $emailService = app(\App\Services\CMS\EmailService::class);
        $sent = $emailService->sendEmail(
            company:       $quotation->company,
            to:            $validated['email'],
            subject:       "Quotation {$quotation->quotation_number} from {$quotation->company->name}",
            view:          'emails.cms.quotation-sent',
            data:          [
                'quotation'      => $quotation,
                'company'        => $quotation->company,
                'customer'       => $quotation->customer,
                'recipient_name' => $quotation->customer->name,
                'custom_message' => $validated['message'] ?? null,
            ],
            emailType:     'quotation',
            referenceType: 'quotation',
            referenceId:   $quotation->id,
            attachmentPath: $tmpPath
        );

        // Clean up temp file
        if (file_exists($tmpPath)) unlink($tmpPath);

        // Mark as sent
        $this->quotationService->sendQuotation($id, $cmsUser->id);

        if ($sent) {
            return back()->with('success', 'Quotation sent via email successfully');
        }
        return back()->with('warning', 'Quotation marked as sent but email delivery failed');
    }

    /**
     * Get a WhatsApp share link — PDF is served via the download route (generated on demand).
     */
    public function whatsappLink(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $cmsUser   = $request->user()->cmsUser;
        $quotation = QuotationModel::with(['customer', 'company'])
            ->where('company_id', $cmsUser->company_id)
            ->findOrFail($id);

        // Mark as sent
        $this->quotationService->sendQuotation($id, $cmsUser->id);

        // Build a signed download URL valid for 24 hours
        $downloadUrl = \URL::signedRoute(
            'cms.quotations.pdf.signed',
            ['id' => $id],
            now()->addHours(24)
        );

        $phone   = preg_replace('/[^0-9]/', '', $quotation->customer->phone ?? '');
        $message = urlencode(
            "Hello {$quotation->customer->name},\n\n" .
            "Please find your quotation {$quotation->quotation_number} from {$quotation->company->name}.\n\n" .
            "Total: K" . number_format($quotation->total_amount, 2) . "\n\n" .
            "Download PDF: {$downloadUrl}"
        );

        $whatsappUrl = $phone
            ? "https://wa.me/{$phone}?text={$message}"
            : "https://wa.me/?text={$message}";

        return response()->json(['url' => $whatsappUrl]);
    }

    /**
     * Signed PDF download (used in WhatsApp links — no auth required).
     */
    public function downloadPdfSigned(Request $request, int $id)
    {
        if (!$request->hasValidSignature()) abort(403);

        $quotation = QuotationModel::with(['customer', 'items', 'company'])->findOrFail($id);
        $pdfContent = $this->generateQuotationPdf($quotation);

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="quotation-' . $quotation->quotation_number . '.pdf"');
    }

    /**
     * Generate PDF content — centralised so all send paths use the same logic.
     */
    private function generateQuotationPdf(QuotationModel $quotation): string
    {
        $quotation->loadMissing(['customer', 'items', 'company']);

        if ($quotation->company->hasBizDocsModule()) {
            try {
                $adapter = app(\App\Domain\CMS\BizDocs\Contracts\DocumentGeneratorInterface::class);
                return $adapter->generateQuotationPdf($quotation);
            } catch (\Exception $e) {
                \Log::error('BizDocs quotation PDF failed', ['id' => $quotation->id, 'error' => $e->getMessage()]);
            }
        }

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('cms.pdf.quotation-basic', ['quotation' => $quotation])
            ->setPaper('a4', 'portrait')
            ->output();
    }

    public function convertToJob(Request $request, int $id)
    {
        $userId = $request->user()->cmsUser->id;
        
        $job = $this->quotationService->convertToJob($id, $userId);

        return redirect()->route('cms.jobs.show', $job->id)
            ->with('success', 'Quotation converted to job successfully');
    }

    /**
     * Download quotation as PDF (authenticated, on demand — not stored).
     */
    public function downloadPdf(Request $request, int $id)
    {
        $companyId  = $request->user()->cmsUser->company_id;
        $quotation  = QuotationModel::with(['customer', 'items', 'company'])
            ->where('company_id', $companyId)->findOrFail($id);
        $pdfContent = $this->generateQuotationPdf($quotation);

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="quotation-' . $quotation->quotation_number . '.pdf"');
    }

    /**
     * Preview quotation PDF in browser (authenticated, on demand).
     */
    public function previewPdf(Request $request, int $id)
    {
        $companyId  = $request->user()->cmsUser->company_id;
        $quotation  = QuotationModel::with(['customer', 'items', 'company'])
            ->where('company_id', $companyId)->findOrFail($id);
        $pdfContent = $this->generateQuotationPdf($quotation);

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="quotation-' . $quotation->quotation_number . '.pdf"');
    }
}
