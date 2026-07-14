<?php

namespace App\Http\Controllers\StockAudit;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\PurchasingService;
use App\Domain\StockFlow\Services\InventoryService;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaPurchaseOrderModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaSupplierModel;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $search = $request->get('search');
        $perPage = $request->get('per_page', 15);

        $query = SaPurchaseOrderModel::where('sa_company_id', $companyId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn($model) => $model->toArray());

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

        return redirect()->sfRoute('stock-audit.purchases.index')->with('success', 'Purchase order created successfully.');
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

        return redirect()->sfRoute('stock-audit.purchases.index')->with('success', 'Order received successfully.');
    }

    public function suppliers(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        $search = $request->get('search');
        $perPage = $request->get('per_page', 15);

        $query = SaSupplierModel::where('sa_company_id', $companyId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn($model) => $model->toArray());

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

        return redirect()->sfRoute('stock-audit.suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function updateSupplier(Request $request, int $supplierId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'payment_terms' => 'nullable|string|max:255',
        ]);

        $this->purchasingService->updateSupplier($supplierId, $validated);

        return redirect()->sfRoute('stock-audit.suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroySupplier(int $supplierId)
    {
        $this->purchasingService->deleteSupplier($supplierId);

        return redirect()->sfRoute('stock-audit.suppliers.index')->with('success', 'Supplier deleted successfully.');
    }

    public function report(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');

        $validated = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $repo = app(\App\Domain\StockFlow\Repositories\PurchaseOrderRepositoryInterface::class);
        $purchases = $repo->findByCompanyIdAndDateBetween(
            CompanyId::fromInt($companyId),
            new \DateTimeImmutable($validated['from']),
            new \DateTimeImmutable($validated['to']),
        );

        $summary = [
            'total_orders' => count($purchases),
            'total_cost' => 0,
            'received' => 0,
            'pending' => 0,
            'by_supplier' => [],
        ];

        foreach ($purchases as $po) {
            $summary['total_cost'] += $po->getTotal()->toFloat();
            $status = $po->getStatus()->value();
            if ($status === 'received') $summary['received']++;
            elseif ($status === 'pending' || $status === 'draft') $summary['pending']++;
            $supplier = $po->getSupplierName() ?? 'Unknown';
            $summary['by_supplier'][$supplier] = ($summary['by_supplier'][$supplier] ?? 0) + $po->getTotal()->toFloat();
        }

        return Inertia::render('StockAudit/Purchases/Report', [
            'purchases' => array_map(fn($p) => $p->toArray(), $purchases),
            'summary' => $summary,
            'from' => $validated['from'],
            'to' => $validated['to'],
        ]);
    }

    public function reportPdf(Request $request)
    {
        $companyId = $request->session()->get('stock_audit_company_id');
        $companyModel = \App\Infrastructure\Persistence\Eloquent\StockFlow\SaCompanyModel::find($companyId);
        $companyName = $companyModel?->name ?? 'Company';

        $validated = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $repo = app(\App\Domain\StockFlow\Repositories\PurchaseOrderRepositoryInterface::class);
        $purchases = $repo->findByCompanyIdAndDateBetween(
            CompanyId::fromInt($companyId),
            new \DateTimeImmutable($validated['from']),
            new \DateTimeImmutable($validated['to']),
        );

        $summary = ['total_orders' => count($purchases), 'total_cost' => 0, 'received' => 0, 'pending' => 0, 'by_supplier' => []];
        foreach ($purchases as $po) {
            $summary['total_cost'] += $po->getTotal()->toFloat();
            $status = $po->getStatus()->value();
            if ($status === 'received') $summary['received']++;
            elseif ($status === 'pending' || $status === 'draft') $summary['pending']++;
            $supplier = $po->getSupplierName() ?? 'Unknown';
            $summary['by_supplier'][$supplier] = ($summary['by_supplier'][$supplier] ?? 0) + $po->getTotal()->toFloat();
        }

        $pdf = Pdf::loadView('pdf.stock-audit.purchases-report', [
            'companyName' => $companyName,
            'purchases' => array_map(fn($p) => $p->toArray(), $purchases),
            'summary' => $summary,
            'from' => $validated['from'],
            'to' => $validated['to'],
        ]);

        return $pdf->download("purchases-report-{$validated['from']}-to-{$validated['to']}.pdf");
    }
}
