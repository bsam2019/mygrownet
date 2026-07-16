<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\QuotationService;
use App\Domain\StockFlow\Services\InventoryService;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaQuotationModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuotationController extends Controller
{
    public function __construct(
        private QuotationService $quotationService,
        private InventoryService $inventoryService,
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $status = $request->get('status');
        $perPage = $request->get('per_page', 15);

        $query = SaQuotationModel::where('sa_company_id', $companyId);

        if ($status) {
            $query->where('status', $status);
        }

        $quotations = $query->with('items')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn($model) => $model->toArray());

        return Inertia::render('StockFlow/Quotations/Index', [
            'quotations' => $quotations,
            'currentStatus' => $status,
        ]);
    }

    public function create(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');

        return Inertia::render('StockFlow/Quotations/Create', [
            'items' => $this->inventoryService->getInStockItems($companyId),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');

        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'quotation_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:quotation_date',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
        ]);

        $this->quotationService->createQuotation($companyId, $validated, $request->user()->id);

        return redirect()->sfRoute('stockflow.quotations.index')->with('success', 'Quotation created successfully.');
    }

    public function show(int $quotationId)
    {
        $quotation = $this->quotationService->getQuotationById($quotationId, session('stockflow_company_id'));

        if (!$quotation) {
            abort(404);
        }

        return Inertia::render('StockFlow/Quotations/Show', [
            'quotation' => $quotation->toArray(),
        ]);
    }

    public function updateStatus(Request $request, int $quotationId)
    {
        $companyId = $request->session()->get('stockflow_company_id');

        $validated = $request->validate([
            'status' => 'required|string|in:sent,accepted,declined,expired,converted',
            'converted_to_sale_id' => 'nullable|integer|exists:sa_sales,id',
        ]);

        $this->quotationService->updateStatus(
            $quotationId,
            $companyId,
            $validated['status'],
            isset($validated['converted_to_sale_id']) ? (int) $validated['converted_to_sale_id'] : null,
        );

        return redirect()->sfRoute('stockflow.quotations.show', $quotationId)
            ->with('success', 'Quotation status updated.');
    }
}
