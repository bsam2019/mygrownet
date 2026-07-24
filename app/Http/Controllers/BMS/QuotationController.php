<?php

namespace App\Http\Controllers\BMS;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BMS\Concerns\HasBmsAccess;
use App\Domain\BMS\Core\Services\QuotationService;
use App\Domain\BMS\Repositories\QuotationRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\QuotationModel;
use App\Infrastructure\Persistence\Eloquent\BMS\CustomerModel;
use App\Infrastructure\Persistence\Eloquent\BMS\BranchModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuotationController extends Controller
{
    use HasBmsAccess;

    public function __construct(
        private QuotationService $quotationService,
        private QuotationRepositoryInterface $quotationRepo
    ) {}

    public function index(Request $request)
    {
        $companyId = $this->getCompanyId($request);

        $query = QuotationModel::with(['customer', 'createdBy.user', 'branch'])
            ->where('company_id', $companyId)
            ->forBranch($request->branch_id);

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
                'customer' => ['id' => $quotation->customer->id, 'name' => $quotation->customer->name],
                'createdBy' => ['user' => ['name' => $quotation->createdBy->user->name]],
            ]);

        $summary = $this->quotationRepo->getSummary($companyId);

        $branches = BranchModel::where('company_id', $companyId)
            ->where('is_active', true)
            ->get(['id', 'branch_name']);

        return Inertia::render('BMS/Quotations/Index', [
            'quotations' => $quotations,
            'summary' => $summary,
            'filters' => $request->only(['status', 'search', 'branch_id']),
            'branches' => $branches,
        ]);
    }

    public function create(Request $request)
    {
        $companyId = $this->getCompanyId($request);

        $customers = CustomerModel::where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $settingsService = app(\App\Domain\BMS\Core\Services\CompanySettingsService::class);
        $defaults = $settingsService->getDocumentDefaults($companyId, 'quotation');

        return Inertia::render('BMS/Quotations/Create', [
            'customers'    => $customers,
            'defaultNotes' => $defaults['notes'] ?? '',
            'defaultTerms' => $defaults['terms'] ?? '',
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

        $companyId = $this->getCompanyId($request);
        $userId = $request->user()->cmsUser->id;

        $quotation = $this->quotationService->createQuotation($validated, $companyId, $userId);

        return redirect()->route('bms.quotations.show', $quotation->id)
            ->with('success', 'Quotation created successfully');
    }

    public function show(Request $request, int $id)
    {
        $companyId = $this->getCompanyId($request);

        $quotation = QuotationModel::with([
            'customer', 'items', 'createdBy.user',
            'convertedToJob', 'measurement.items',
        ])
            ->where('company_id', $companyId)
            ->findOrFail($id);

        $profitSummary = null;
        if ($quotation->measurement) {
            $measurementService = app(\App\Domain\BMS\Core\Services\MeasurementService::class);
            $profitSummary = $measurementService->getProfitSummary($quotation->measurement);
        }

        return Inertia::render('BMS/Quotations/Show', [
            'quotation'     => $quotation,
            'profitSummary' => $profitSummary,
        ]);
    }

    public function send(Request $request, int $id)
    {
        $this->quotationService->sendQuotation($id, $request->user()->cmsUser->id);
        return back()->with('success', 'Quotation marked as sent');
    }

    public function sendViaEmail(Request $request, int $id)
    {
        $validated = $request->validate([
            'email'   => 'required|email',
            'message' => 'nullable|string|max:1000',
        ]);

        $cmsUser = $request->user()->cmsUser;
        $quotation = QuotationModel::with(['customer', 'items', 'company'])
            ->where('company_id', $cmsUser->company_id)
            ->findOrFail($id);

        $pdfContent = $this->generateQuotationPdf($quotation);
        $tmpPath = storage_path("app/temp/quotation-{$quotation->quotation_number}.pdf");

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        file_put_contents($tmpPath, $pdfContent);

        $emailService = app(\App\Services\BMS\EmailService::class);
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

        if (file_exists($tmpPath)) unlink($tmpPath);

        $this->quotationService->sendQuotation($id, $cmsUser->id);

        if ($sent) {
            return back()->with('success', 'Quotation sent via email successfully');
        }
        return back()->with('warning', 'Quotation marked as sent but email delivery failed');
    }

    public function whatsappLink(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $cmsUser = $request->user()->cmsUser;
        $quotation = QuotationModel::with(['customer', 'company'])
            ->where('company_id', $cmsUser->company_id)
            ->findOrFail($id);

        $this->quotationService->sendQuotation($id, $cmsUser->id);

        $downloadUrl = \URL::signedRoute('cms.quotations.pdf.signed', ['id' => $id], now()->addHours(24));
        $phone = preg_replace('/[^0-9]/', '', $quotation->customer->phone ?? '');
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

    public function downloadPdfSigned(Request $request, int $id)
    {
        if (!$request->hasValidSignature()) abort(403);

        $quotation = QuotationModel::with(['customer', 'items', 'company'])->findOrFail($id);
        $pdfContent = $this->generateQuotationPdf($quotation);

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="quotation-' . $quotation->quotation_number . '.pdf"');
    }

    private function generateQuotationPdf(QuotationModel $quotation): string
    {
        $quotation->loadMissing(['customer', 'items', 'company']);

        if ($quotation->company->hasBizDocsModule()) {
            try {
                $adapter = app(\App\Domain\BMS\BizDocs\Contracts\DocumentGeneratorInterface::class);
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
        $job = $this->quotationService->convertToJob($id, $request->user()->cmsUser->id);
        return redirect()->route('bms.jobs.show', $job->id)
            ->with('success', 'Quotation converted to job successfully');
    }

    public function downloadPdf(Request $request, int $id)
    {
        $companyId = $this->getCompanyId($request);
        $quotation = QuotationModel::with(['customer', 'items', 'company'])
            ->where('company_id', $companyId)->findOrFail($id);
        $pdfContent = $this->generateQuotationPdf($quotation);

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="quotation-' . $quotation->quotation_number . '.pdf"');
    }

    public function previewPdf(Request $request, int $id)
    {
        $companyId = $this->getCompanyId($request);
        $quotation = QuotationModel::with(['customer', 'items', 'company'])
            ->where('company_id', $companyId)->findOrFail($id);
        $pdfContent = $this->generateQuotationPdf($quotation);

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="quotation-' . $quotation->quotation_number . '.pdf"');
    }
}
