<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\SaleService;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Repositories\CustomerRepositoryInterface;
use App\Domain\BizBoost\Repositories\ProductRepositoryInterface;
use App\Domain\BizBoost\Repositories\LocationRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaleController extends Controller
{
    public function __construct(
        private SaleService $saleService,
        private BusinessService $businessService,
        private CustomerRepositoryInterface $customerRepo,
        private ProductRepositoryInterface $productRepo,
        private LocationRepositoryInterface $locationRepo,
    ) {}

    public function index(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $filters = $request->only(['search', 'date_from', 'date_to', 'payment_method', 'status', 'customer_id', 'sort']);
        $sales = $this->saleService->getSales($business->id, $filters);

        return Inertia::render('BizBoost/Sales/Index', [
            'sales' => $sales,
            'filters' => $filters,
            'stats' => $this->saleService->getSaleStats($business->id, $filters),
        ]);
    }

    public function create(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);

        return Inertia::render('BizBoost/Sales/Create', [
            'customers' => $this->customerRepo->findByBusiness($business->id),
            'products' => $this->productRepo->findActiveByBusiness($business->id),
            'locations' => $this->locationRepo->findByBusiness($business->id),
            'paymentMethods' => config('modules.bizboost.payment_methods', ['cash', 'card', 'mobile_money', 'bank_transfer', 'other']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|integer|exists:biz_boost_customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:biz_boost_products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,mobile_money,bank_transfer,other',
            'payment_status' => 'required|in:pending,paid,partial,refunded',
            'amount_paid' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'location_id' => 'nullable|integer|exists:biz_boost_locations,id',
            'sale_date' => 'required|date',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $sale = $this->saleService->createSale($business->id, $validated, $this->customerRepo);

        return redirect()->route('bizboost.sales.index')
            ->with('success', 'Sale #' . $sale->invoiceNumber . ' recorded successfully.');
    }

    public function show(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $sale = $this->saleService->getSaleById($business->id, $id);

        if (!$sale) {
            abort(404);
        }

        return Inertia::render('BizBoost/Sales/Show', [
            'sale' => $sale->toArray(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:pending,paid,partial,refunded',
            'amount_paid' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->saleService->updateSalePayment($business->id, $id, $validated);

        return back()->with('success', 'Sale updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->saleService->deleteSale($business->id, $id);

        return redirect()->route('bizboost.sales.index')
            ->with('success', 'Sale deleted successfully.');
    }

    public function receipt(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $sale = $this->saleService->getSaleById($business->id, $id);

        if (!$sale) {
            abort(404);
        }

        return Inertia::render('BizBoost/Sales/Receipt', [
            'sale' => $sale->toArray(),
        ]);
    }
}