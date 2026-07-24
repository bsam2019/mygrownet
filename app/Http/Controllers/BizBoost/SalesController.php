<?php

namespace App\Http\Controllers\BizBoost;

use App\Events\BizBoost\SaleRecorded;
use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Repositories\SaleRepositoryInterface;
use App\Domain\BizBoost\Repositories\ProductRepositoryInterface;
use App\Domain\BizBoost\Repositories\CustomerRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SalesController extends Controller
{
    public function __construct(
        private BusinessService $businessService,
        private SaleRepositoryInterface $saleRepo,
        private ProductRepositoryInterface $productRepo,
        private CustomerRepositoryInterface $customerRepo,
    ) {}

    public function index(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);

        $sales = DB::table('bizboost_sales')
            ->where('business_id', $business->id)
            ->leftJoin('bizboost_customers', 'bizboost_sales.customer_id', '=', 'bizboost_customers.id')
            ->select('bizboost_sales.*', 'bizboost_customers.name as customer_name')
            ->when($request->date_from, fn($q, $date) => $q->where('bizboost_sales.sale_date', '>=', $date))
            ->when($request->date_to, fn($q, $date) => $q->where('bizboost_sales.sale_date', '<=', $date))
            ->when($request->product_id, fn($q, $id) => $q->where('bizboost_sales.product_id', $id))
            ->orderByDesc('bizboost_sales.sale_date')
            ->orderByDesc('bizboost_sales.created_at')
            ->paginate(20)
            ->withQueryString();

        $today = now()->toDateString();
        $startOfWeek = now()->startOfWeek()->toDateString();
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        $startOfLastMonth = now()->subMonth()->startOfMonth();
        $endOfLastMonth = now()->subMonth()->endOfMonth();

        $stats = [
            'today' => DB::table('bizboost_sales')->where('business_id', $business->id)->where('sale_date', $today)->sum('total_amount'),
            'this_week' => DB::table('bizboost_sales')->where('business_id', $business->id)->where('sale_date', '>=', $startOfWeek)->sum('total_amount'),
            'this_month' => DB::table('bizboost_sales')->where('business_id', $business->id)->whereBetween('sale_date', [$startOfMonth, $endOfMonth])->sum('total_amount'),
            'last_month' => DB::table('bizboost_sales')->where('business_id', $business->id)->whereBetween('sale_date', [$startOfLastMonth, $endOfLastMonth])->sum('total_amount'),
        ];
        $stats['month_change'] = $stats['last_month'] > 0
            ? round((($stats['this_month'] - $stats['last_month']) / $stats['last_month']) * 100, 1)
            : 0;

        $products = $this->productRepo->findActiveByBusiness($business->id);

        return Inertia::render('BizBoost/Sales/Index', [
            'business' => ['id' => $business->id, 'name' => $business->name, 'currency' => $business->currency],
            'sales' => $sales,
            'stats' => $stats,
            'products' => $products,
            'filters' => $request->only(['search', 'date_from', 'date_to', 'payment_method']),
            'subscriptionTier' => $request->get('subscription_tier', 'free'),
        ]);
    }

    public function create(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $products = $this->productRepo->findActiveByBusiness($business->id);

        $customers = $this->customerRepo->findByBusiness($business->id);

        return Inertia::render('BizBoost/Sales/Create', [
            'business' => ['id' => $business->id, 'name' => $business->name, 'currency' => $business->currency],
            'products' => $products,
            'customers' => $customers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:bizboost_products,id',
            'product_name' => 'required|string|max:255',
            'customer_id' => 'nullable|exists:bizboost_customers,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'sale_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);

        $saleId = DB::table('bizboost_sales')->insertGetId([
            'business_id' => $business->id,
            'product_id' => $validated['product_id'],
            'product_name' => $validated['product_name'],
            'customer_id' => $validated['customer_id'],
            'quantity' => $validated['quantity'],
            'unit_price' => $validated['unit_price'],
            'total_amount' => $validated['quantity'] * $validated['unit_price'],
            'sale_date' => $validated['sale_date'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'],
            'source' => 'manual',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sale = DB::table('bizboost_sales')->where('id', $saleId)->first();
        $customer = $validated['customer_id']
            ? DB::table('bizboost_customers')->where('id', $validated['customer_id'])->first()
            : null;

        broadcast(new SaleRecorded($business->id, [
            'id' => $sale->id,
            'product_name' => $sale->product_name,
            'customer_name' => $customer?->name ?? 'Walk-in customer',
            'total_amount' => $sale->total_amount,
            'quantity' => $sale->quantity,
            'payment_method' => $sale->payment_method,
        ]))->toOthers();

        return redirect()->route('bizboost.sales.index')
            ->with('success', 'Sale recorded successfully.');
    }

    public function reports(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);

        $period = $request->period ?? 'month';
        $startDate = match ($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $salesByDay = DB::table('bizboost_sales')
            ->where('business_id', $business->id)
            ->where('sale_date', '>=', $startDate)
            ->select(DB::raw('DATE(sale_date) as date'), DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topProducts = DB::table('bizboost_sales')
            ->where('business_id', $business->id)
            ->where('sale_date', '>=', $startDate)
            ->select('product_name', DB::raw('SUM(total_amount) as total'), DB::raw('SUM(quantity) as quantity'))
            ->groupBy('product_name')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        $salesByPayment = DB::table('bizboost_sales')
            ->where('business_id', $business->id)
            ->where('sale_date', '>=', $startDate)
            ->whereNotNull('payment_method')
            ->select('payment_method', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        return Inertia::render('BizBoost/Sales/Reports', [
            'salesByDay' => $salesByDay,
            'topProducts' => $topProducts,
            'salesByPayment' => $salesByPayment,
            'period' => $period,
            'startDate' => $startDate->toDateString(),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        DB::table('bizboost_sales')->where('id', $id)->where('business_id', $business->id)->delete();
        return back()->with('success', 'Sale deleted successfully.');
    }
}