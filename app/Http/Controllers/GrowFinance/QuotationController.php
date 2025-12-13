<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\QuotationService;
use App\Domain\GrowFinance\ValueObjects\QuotationStatus;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceCustomerModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceQuotationModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceInvoiceTemplateModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuotationController extends Controller
{
    public function __construct(
        private QuotationService $quotationService
    ) {}

    public function index(Request $request): Response
    {
        $businessId = $request->user()->id;
        $status = $request->get('status');

        $query = GrowFinanceQuotationModel::forBusiness($businessId)->with('customer');

        if ($status) {
            $query->where('status', $status);
        }

        $quotations = $query->latest('quotation_date')->paginate(20);

        // Get stats
        $stats = [
            'total' => GrowFinanceQuotationModel::forBusiness($businessId)->count(),
            'draft' => GrowFinanceQuotationModel::forBusiness($businessId)->status('draft')->count(),
            'sent' => GrowFinanceQuotationModel::forBusiness($businessId)->status('sent')->count(),
            'accepted' => GrowFinanceQuotationModel::forBusiness($businessId)->status('accepted')->count(),
            'converted' => GrowFinanceQuotationModel::forBusiness($businessId)->status('converted')->count(),
            'pending_value' => GrowFinanceQuotationModel::forBusiness($businessId)->pending()->sum('total_amount'),
        ];

        return Inertia::render('GrowFinance/Quotations/Index', [
            'quotations' => $quotations,
            'currentStatus' => $status,
            'stats' => $stats,
        ]);
    }

    public function create(Request $request): Response
    {
        $businessId = $request->user()->id;

        $customers = GrowFinanceCustomerModel::forBusiness($businessId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);

        $templates = GrowFinanceInvoiceTemplateModel::forBusiness($businessId)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name', 'is_default', 'colors']);

        $defaultTemplate = $templates->firstWhere('is_default', true);

        return Inertia::render('GrowFinance/Quotations/Create', [
            'customers' => $customers,
            'templates' => $templates,
            'defaultTemplateId' => $defaultTemplate?->id,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:growfinance_customers,id',
            'quotation_date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:quotation_date',
            'subject' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'terms' => 'nullable|string|max:1000',
            'template_id' => 'nullable|exists:growfinance_invoice_templates,id',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $businessId = $request->user()->id;

        // Get default template if not provided
        if (empty($validated['template_id'])) {
            $defaultTemplate = GrowFinanceInvoiceTemplateModel::forBusiness($businessId)
                ->where('is_default', true)
                ->first();
            $validated['template_id'] = $defaultTemplate?->id;
        }

        $this->quotationService->create($businessId, $validated);

        return redirect()->route('growfinance.quotations.index')
            ->with('success', 'Quotation created successfully!');
    }

    public function show(Request $request, int $id): Response
    {
        $businessId = $request->user()->id;

        $quotation = GrowFinanceQuotationModel::forBusiness($businessId)
            ->with(['customer', 'items', 'convertedInvoice'])
            ->findOrFail($id);

        $templates = GrowFinanceInvoiceTemplateModel::forBusiness($businessId)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name', 'is_default', 'colors']);

        $currentTemplate = null;
        if ($quotation->template_id) {
            $currentTemplate = $templates->firstWhere('id', $quotation->template_id);
        }
        if (!$currentTemplate) {
            $currentTemplate = $templates->firstWhere('is_default', true);
        }

        return Inertia::render('GrowFinance/Quotations/Show', [
            'quotation' => $quotation,
            'templates' => $templates,
            'currentTemplate' => $currentTemplate,
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $businessId = $request->user()->id;

        $quotation = GrowFinanceQuotationModel::forBusiness($businessId)
            ->with('items')
            ->findOrFail($id);

        // Only allow editing draft or sent quotations
        if (!in_array($quotation->status, [QuotationStatus::DRAFT->value, QuotationStatus::SENT->value])) {
            return redirect()->route('growfinance.quotations.show', $id)
                ->with('error', 'This quotation cannot be edited.');
        }

        $customers = GrowFinanceCustomerModel::forBusiness($businessId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);

        $templates = GrowFinanceInvoiceTemplateModel::forBusiness($businessId)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name', 'is_default', 'colors']);

        return Inertia::render('GrowFinance/Quotations/Edit', [
            'quotation' => $quotation,
            'customers' => $customers,
            'templates' => $templates,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:growfinance_customers,id',
            'quotation_date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:quotation_date',
            'subject' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'terms' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $businessId = $request->user()->id;
        $quotation = GrowFinanceQuotationModel::forBusiness($businessId)->findOrFail($id);

        if (!in_array($quotation->status, [QuotationStatus::DRAFT->value, QuotationStatus::SENT->value])) {
            return back()->with('error', 'This quotation cannot be edited.');
        }

        $this->quotationService->update($quotation, $validated);

        return redirect()->route('growfinance.quotations.index')
            ->with('success', 'Quotation updated successfully!');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $businessId = $request->user()->id;
        $quotation = GrowFinanceQuotationModel::forBusiness($businessId)->findOrFail($id);

        // Only allow deleting draft quotations
        if ($quotation->status !== QuotationStatus::DRAFT->value) {
            return back()->with('error', 'Only draft quotations can be deleted.');
        }

        $quotation->items()->delete();
        $quotation->delete();

        return back()->with('success', 'Quotation deleted successfully!');
    }

    public function send(Request $request, int $id): RedirectResponse
    {
        $businessId = $request->user()->id;
        $quotation = GrowFinanceQuotationModel::forBusiness($businessId)->findOrFail($id);

        if (!in_array($quotation->status, [QuotationStatus::DRAFT->value, QuotationStatus::SENT->value])) {
            return back()->with('error', 'This quotation cannot be sent.');
        }

        $this->quotationService->send($quotation);

        return back()->with('success', 'Quotation sent successfully!');
    }

    public function accept(Request $request, int $id): RedirectResponse
    {
        $businessId = $request->user()->id;
        $quotation = GrowFinanceQuotationModel::forBusiness($businessId)->findOrFail($id);

        if ($quotation->status !== QuotationStatus::SENT->value) {
            return back()->with('error', 'Only sent quotations can be accepted.');
        }

        $this->quotationService->accept($quotation);

        return back()->with('success', 'Quotation accepted! You can now convert it to an invoice.');
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $businessId = $request->user()->id;
        $quotation = GrowFinanceQuotationModel::forBusiness($businessId)->findOrFail($id);

        if ($quotation->status !== QuotationStatus::SENT->value) {
            return back()->with('error', 'Only sent quotations can be rejected.');
        }

        $this->quotationService->reject($quotation, $validated['reason'] ?? null);

        return back()->with('success', 'Quotation rejected.');
    }

    public function convertToInvoice(Request $request, int $id): RedirectResponse
    {
        $businessId = $request->user()->id;
        $quotation = GrowFinanceQuotationModel::forBusiness($businessId)
            ->with('items')
            ->findOrFail($id);

        if ($quotation->status !== QuotationStatus::ACCEPTED->value) {
            return back()->with('error', 'Only accepted quotations can be converted to invoices.');
        }

        try {
            $invoice = $this->quotationService->convertToInvoice($quotation, $businessId);
            
            return redirect()->route('growfinance.invoices.show', $invoice->id)
                ->with('success', 'Quotation converted to invoice successfully!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function duplicate(Request $request, int $id): RedirectResponse
    {
        $businessId = $request->user()->id;
        $quotation = GrowFinanceQuotationModel::forBusiness($businessId)
            ->with('items')
            ->findOrFail($id);

        $newQuotation = $this->quotationService->duplicate($quotation);

        return redirect()->route('growfinance.quotations.edit', $newQuotation->id)
            ->with('success', 'Quotation duplicated! You can now edit the new quotation.');
    }
}
