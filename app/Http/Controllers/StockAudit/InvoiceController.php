<?php

namespace App\Http\Controllers\StockAudit;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\InvoiceService;
use App\Domain\StockFlow\Services\InventoryService;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaInvoiceModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService,
        private InventoryService $inventoryService,
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        $status = $request->get('status');
        $perPage = $request->get('per_page', 15);

        $query = SaInvoiceModel::where('sa_company_id', $companyId);

        if ($status) {
            $query->where('status', $status);
        }

        $invoices = $query->with('items')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn($model) => $model->toArray());

        return Inertia::render('StockAudit/Invoices/Index', [
            'invoices' => $invoices,
            'currentStatus' => $status,
        ]);
    }

    public function create(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');

        return Inertia::render('StockAudit/Invoices/Create', [
            'items' => $this->inventoryService->getInStockItems($companyId),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');

        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'sa_quotation_id' => 'nullable|integer|exists:sa_quotations,id',
            'sa_sale_id' => 'nullable|integer|exists:sa_sales,id',
        ]);

        $this->invoiceService->createInvoice($companyId, $validated, $request->user()->id);

        return redirect()->sfRoute('stock-audit.invoices.index')->with('success', 'Invoice created successfully.');
    }

    public function show(int $invoiceId)
    {
        $invoice = $this->invoiceService->getInvoiceById($invoiceId, session('stock_audit_company_id'));

        if (!$invoice) {
            abort(404);
        }

        return Inertia::render('StockAudit/Invoices/Show', [
            'invoice' => $invoice->toArray(),
        ]);
    }

    public function recordPayment(Request $request, int $invoiceId)
    {
        $companyId = $request->session()->get('stock_audit_company_id');

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $this->invoiceService->recordPayment($invoiceId, $companyId, (float) $validated['amount']);

        return redirect()->sfRoute('stock-audit.invoices.show', $invoiceId)
            ->with('success', 'Payment recorded successfully.');
    }

    public function updateStatus(Request $request, int $invoiceId)
    {
        $companyId = $request->session()->get('stock_audit_company_id');

        $validated = $request->validate([
            'status' => 'required|string|in:sent,cancelled',
        ]);

        $this->invoiceService->updateStatus($invoiceId, $companyId, $validated['status']);

        return redirect()->sfRoute('stock-audit.invoices.show', $invoiceId)
            ->with('success', 'Invoice status updated.');
    }
}
