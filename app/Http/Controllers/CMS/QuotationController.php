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
            ->withQueryString();

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

        return Inertia::render('CMS/Quotations/Create', [
            'customers' => $customers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:cms_customers,id',
            'quotation_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:quotation_date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'items.*.discount_rate' => 'nullable|numeric|min:0',
            'items.*.line_total' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $userId = $request->user()->cmsUser->id;

        $quotation = $this->quotationService->createQuotation($validated, $companyId, $userId);

        return redirect()->route('cms.quotations.show', $quotation->id)
            ->with('success', 'Quotation created successfully');
    }

    public function show(int $id)
    {
        $quotation = QuotationModel::with(['customer', 'items', 'createdBy.user', 'convertedToJob'])
            ->findOrFail($id);

        return Inertia::render('CMS/Quotations/Show', [
            'quotation' => $quotation,
        ]);
    }

    public function send(Request $request, int $id)
    {
        $userId = $request->user()->cmsUser->id;
        
        $quotation = $this->quotationService->sendQuotation($id, $userId);

        return back()->with('success', 'Quotation marked as sent');
    }

    public function convertToJob(Request $request, int $id)
    {
        $userId = $request->user()->cmsUser->id;
        
        $job = $this->quotationService->convertToJob($id, $userId);

        return redirect()->route('cms.jobs.show', $job->id)
            ->with('success', 'Quotation converted to job successfully');
    }
}
