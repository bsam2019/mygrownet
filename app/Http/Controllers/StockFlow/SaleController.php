<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\SalesService;
use App\Domain\StockFlow\Services\InventoryService;
use App\Domain\StockFlow\Services\CashRegisterService;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaSaleModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaleController extends Controller
{
    public function __construct(
        private SalesService $salesService,
        private InventoryService $inventoryService,
        private CashRegisterService $cashRegisterService,
    ) {}

    public function index(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $search = $request->get('search');
        $perPage = $request->get('per_page', 15);

        $query = SaSaleModel::where('sa_company_id', $companyId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                  ->orWhere('payment_method', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $sales = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn($model) => $model->toArray());

        $todayTotal = $this->salesService->getTodayTotal($companyId);

        return Inertia::render('StockFlow/Sales/Index', [
            'sales' => $sales,
            'todayTotal' => $todayTotal,
        ]);
    }

    public function create(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');

        return Inertia::render('StockFlow/Sales/Create', [
            'items' => $this->inventoryService->getInStockItems($companyId),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');

        $validated = $request->validate([
            'sale_date' => 'required|date',
            'payment_method' => 'required|string|in:cash,mobile_money,card,credit,transfer',
            'items' => 'required|array|min:1',
            'items.*.sa_item_id' => 'required|exists:sa_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'amount_tendered' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $this->salesService->createSale($companyId, $validated, $request->user()->id);

        return redirect()->sfRoute('stock-audit.sales.index')->with('success', 'Sale created successfully.');
    }

    public function show(int $saleId)
    {
        $sale = $this->salesService->getSaleById($saleId, session('stockflow_company_id'));

        if (!$sale) {
            abort(404);
        }

        return Inertia::render('StockFlow/Sales/Show', [
            'sale' => $sale->toArray(),
        ]);
    }

    public function byDate(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');

        $validated = $request->validate([
            'date' => 'required|date',
        ]);

        $repo = app(\App\Domain\StockFlow\Repositories\SaleRepositoryInterface::class);
        $result = $repo->findByDate(
            \App\Domain\StockFlow\ValueObjects\CompanyId::fromInt($companyId),
            new \DateTimeImmutable($validated['date']),
        );

        return response()->json($result);
    }

    public function report(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');

        $validated = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $repo = app(\App\Domain\StockFlow\Repositories\SaleRepositoryInterface::class);
        $sales = $repo->findByCompanyIdAndDateBetween(
            \App\Domain\StockFlow\ValueObjects\CompanyId::fromInt($companyId),
            new \DateTimeImmutable($validated['from']),
            new \DateTimeImmutable($validated['to']),
        );

        $summary = [
            'total_sales' => 0,
            'total_transactions' => count($sales),
            'by_method' => [],
            'cash_sales' => 0,
        ];

        foreach ($sales as $sale) {
            $summary['total_sales'] += $sale->getTotal()->toFloat();
            $method = $sale->getPaymentMethod()->value();
            $summary['by_method'][$method] = ($summary['by_method'][$method] ?? 0) + $sale->getTotal()->toFloat();
            if ($sale->isCashPayment()) {
                $summary['cash_sales'] += $sale->getTotal()->toFloat();
            }
        }

        return Inertia::render('StockFlow/Sales/Report', [
            'sales' => array_map(fn($s) => $s->toArray(), $sales),
            'summary' => $summary,
            'from' => $validated['from'],
            'to' => $validated['to'],
        ]);
    }

    public function reportPdf(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        $companyName = $this->getCompanyName($companyId);

        $validated = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $repo = app(\App\Domain\StockFlow\Repositories\SaleRepositoryInterface::class);
        $sales = $repo->findByCompanyIdAndDateBetween(
            CompanyId::fromInt($companyId),
            new \DateTimeImmutable($validated['from']),
            new \DateTimeImmutable($validated['to']),
        );

        $summary = ['total_sales' => 0, 'total_transactions' => count($sales), 'by_method' => [], 'cash_sales' => 0];
        foreach ($sales as $sale) {
            $summary['total_sales'] += $sale->getTotal()->toFloat();
            $method = $sale->getPaymentMethod()->value();
            $summary['by_method'][$method] = ($summary['by_method'][$method] ?? 0) + $sale->getTotal()->toFloat();
            if ($sale->isCashPayment()) $summary['cash_sales'] += $sale->getTotal()->toFloat();
        }

        $pdf = Pdf::loadView('pdf.stock-audit.sales-report', [
            'companyName' => $companyName,
            'sales' => array_map(fn($s) => $s->toArray(), $sales),
            'summary' => $summary,
            'from' => $validated['from'],
            'to' => $validated['to'],
        ]);

        return $pdf->download("sales-report-{$validated['from']}-to-{$validated['to']}.pdf");
    }

    private function getCompanyName(int $companyId): string
    {
        $model = \App\Infrastructure\Persistence\Eloquent\StockFlow\SaCompanyModel::find($companyId);
        return $model?->name ?? 'Company';
    }
}
