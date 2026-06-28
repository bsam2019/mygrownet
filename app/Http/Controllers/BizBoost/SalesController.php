<?php

namespace App\Http\Controllers\BizBoost;

use App\Events\BizBoost\SaleRecorded;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostSaleModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SalesController extends Controller
{
    public function index(Request $request): Response
    {
        $business = $this->getBusiness($request);
        
        $sales = $business->sales()
            ->with(['customer', 'product'])
            ->when($request->date_from, fn($q, $date) => $q->where('sale_date', '>=', $date))
            ->when($request->date_to, fn($q, $date) => $q->where('sale_date', '<=', $date))
            ->when($request->product_id, fn($q, $id) => $q->where('product_id', $id))
            ->orderByDesc('sale_date')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        // Summary stats
        $today = now()->toDateString();
        $startOfWeek = now()->startOfWeek()->toDateString();
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        $startOfLastMonth = now()->subMonth()->startOfMonth();
        $endOfLastMonth = now()->subMonth()->endOfMonth();
        
        $thisMonthTotal = $business->sales()
            ->whereBetween('sale_date', [$startOfMonth, $endOfMonth])
            ->sum('total_amount');
            
        $lastMonthTotal = $business->sales()
            ->whereBetween('sale_date', [$startOfLastMonth, $endOfLastMonth])
            ->sum('total_amount');
        
        $stats = [
            'today' => $business->sales()
                ->where('sale_date', $today)
                ->sum('total_amount'),
            'this_week' => $business->sales()
                ->where('sale_date', '>=', $startOfWeek)
                ->sum('total_amount'),
            'this_month' => $thisMonthTotal,
            'last_month' => $lastMonthTotal,
            'month_change' => $lastMonthTotal > 0 
                ? round((($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100, 1) 
                : 0,
        ];

        $products = $business->products()->where('is_active', true)->get(['id', 'name']);

        return Inertia::render('BizBoost/Sales/Index', [
            'business' => $business->only(['id', 'name', 'currency']),
            'sales' => $sales,
            'stats' => $stats,
            'products' => $products,
            'filters' => $request->only(['search', 'date_from', 'date_to', 'payment_method']),
            'subscriptionTier' => $request->get('subscription_tier', 'free'),
        ]);
    }

    public function create(Request $request): Response
    {
        $business = $this->getBusiness($request);
        
        $products = $business->products()
            ->where('is_active', true)
            ->get(['id', 'name', 'price', 'sale_price']);
        $customers = $business->customers()
            ->where('is_active', true)
            ->get(['id', 'name', 'phone']);

        return Inertia::render('BizBoost/Sales/Create', [
            'business' => $business->only(['id', 'name', 'currency']),
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

        $business = $this->getBusiness($request);

        $sale = $business->sales()->create([
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
        ]);

        // Broadcast real-time event
        $customer = $sale->customer;
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

    public function reports(Request $request): Response
    {
        $business = $this->getBusiness($request);
        
        $period = $request->period ?? 'month';
        $startDate = match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        // Sales by day
        $salesByDay = $business->sales()
            ->where('sale_date', '>=', $startDate)
            ->select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top products
        $topProducts = $business->sales()
            ->where('sale_date', '>=', $startDate)
            ->select(
                'product_name',
                DB::raw('SUM(total_amount) as total'),
                DB::raw('SUM(quantity) as quantity')
            )
            ->groupBy('product_name')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        // Sales by payment method
        $salesByPayment = $business->sales()
            ->where('sale_date', '>=', $startDate)
            ->whereNotNull('payment_method')
            ->select(
                'payment_method',
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as count')
            )
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
        $business = $this->getBusiness($request);
        $sale = $business->sales()->findOrFail($id);
        $sale->delete();

        return back()->with('success', 'Sale deleted successfully.');
    }

    private function getBusiness(Request $request): BizBoostBusinessModel
    {
        return BizBoostBusinessModel::where('user_id', $request->user()->id)
            ->firstOrFail();
    }
}
