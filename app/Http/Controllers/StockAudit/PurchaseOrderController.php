<?php

namespace App\Http\Controllers\StockAudit;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\PurchasingService;
use App\Domain\StockFlow\Services\InventoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    public function __construct(
        private PurchasingService $purchasingService,
        private InventoryService $inventoryService,
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        $orders = $this->purchasingService->getOrdersForCompany($companyId);

        return Inertia::render('StockAudit/Purchases/Index', [
            'orders' => $orders,
        ]);
    }

    public function create(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');

        return Inertia::render('StockAudit/Purchases/Create', [
            'suppliers' => $this->purchasingService->getSuppliersForCompany($companyId),
            'items' => $this->inventoryService->getItemsForCompany($companyId),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');

        $validated = $request->validate([
            'sa_supplier_id' => 'nullable|exists:sa_suppliers,id',
            'order_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.sa_item_id' => 'required|exists:sa_items,id',
            'items.*.quantity_ordered' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $this->purchasingService->createPurchaseOrder($companyId, $validated);

        return redirect()->sfRoute('stock-audit.purchases.index');
    }

    public function show(int $purchaseOrderId)
    {
        $order = $this->purchasingService->getOrderById($purchaseOrderId, session('stock_audit_company_id'));

        if (!$order) {
            abort(404);
        }

        return Inertia::render('StockAudit/Purchases/Show', [
            'order' => $order->toArray(),
        ]);
    }

    public function receive(Request $request, int $purchaseOrderId)
    {
        $companyId = $request->session()->get('stock_audit_company_id');

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.sa_item_id' => 'required|exists:sa_items,id',
            'items.*.quantity_received' => 'required|numeric|min:0',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        $this->purchasingService->receiveOrder($purchaseOrderId, $companyId, $validated['items'], $request->user()->id);

        return redirect()->sfRoute('stock-audit.purchases.index');
    }

    public function suppliers(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        $suppliers = $this->purchasingService->getSuppliersForCompany($companyId);

        return Inertia::render('StockAudit/Suppliers/Index', [
            'suppliers' => $suppliers,
        ]);
    }

    public function storeSupplier(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'payment_terms' => 'nullable|string|max:255',
        ]);

        $this->purchasingService->createSupplier($companyId, $validated);

        return redirect()->sfRoute('stock-audit.suppliers.index');
    }

    public function destroySupplier(int $supplierId)
    {
        $this->purchasingService->deleteSupplier($supplierId);

        return redirect()->sfRoute('stock-audit.suppliers.index');
    }
}
