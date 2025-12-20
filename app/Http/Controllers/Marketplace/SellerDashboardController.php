<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Domain\Marketplace\Services\SellerService;
use App\Domain\Marketplace\Services\ProductService;
use App\Domain\Marketplace\Services\OrderService;
use App\Domain\Marketplace\Services\EscrowService;
use App\Models\MarketplaceSeller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SellerDashboardController extends Controller
{
    public function __construct(
        private SellerService $sellerService,
        private ProductService $productService,
        private OrderService $orderService,
        private EscrowService $escrowService,
    ) {}

    /**
     * Public landing page for becoming a seller (no auth required)
     */
    public function join()
    {
        // If user is logged in and already a seller, redirect to dashboard
        if (auth()->check()) {
            $existingSeller = $this->sellerService->getByUserId(auth()->id());
            if ($existingSeller) {
                return redirect()->route('marketplace.seller.dashboard');
            }
        }

        return Inertia::render('Marketplace/Seller/Join');
    }

    public function index(Request $request)
    {
        $seller = $this->sellerService->getByUserId($request->user()->id);

        if (!$seller) {
            return redirect()->route('marketplace.seller.register');
        }

        // Basic Stats
        $totalProducts = $seller->products()->count();
        $activeProducts = $seller->products()->where('status', 'active')->count();
        $pendingProducts = $seller->products()->where('status', 'pending')->count();
        $rejectedProducts = $seller->products()->where('status', 'rejected')->count();
        
        // Order Stats
        $pendingOrders = $seller->orders()->whereIn('status', ['paid', 'processing'])->count();
        $completedOrders = $seller->orders()->where('status', 'completed')->count();
        $totalOrders = $seller->total_orders;
        
        // Financial Stats
        $pendingBalance = $this->escrowService->getSellerPendingBalance($seller->id);
        $availableBalance = $seller->available_balance ?? 0;
        $totalRevenue = $seller->orders()->where('status', 'completed')->sum('total');
        $todayRevenue = $seller->orders()->where('status', 'completed')
            ->whereDate('confirmed_at', today())->sum('total');
        $thisWeekRevenue = $seller->orders()->where('status', 'completed')
            ->whereBetween('confirmed_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('total');
        $thisMonthRevenue = $seller->orders()->where('status', 'completed')
            ->whereMonth('confirmed_at', now()->month)->sum('total');
        
        // Calculate average order value
        $avgOrderValue = $completedOrders > 0 ? $totalRevenue / $completedOrders : 0;
        
        // Product Performance
        $lowStockProducts = $seller->products()
            ->where('status', 'active')
            ->where('stock_quantity', '>', 0)
            ->where('stock_quantity', '<=', 5)
            ->count();
        
        $outOfStockProducts = $seller->products()
            ->where('status', 'active')
            ->where('stock_quantity', 0)
            ->count();
        
        $topProducts = $seller->products()
            ->withCount(['orderItems as total_sold' => function ($query) {
                $query->whereHas('order', function ($q) {
                    $q->where('status', 'completed');
                });
            }])
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();
        
        // Sales Chart Data (last 7 days)
        $salesChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $revenue = $seller->orders()
                ->where('status', 'completed')
                ->whereDate('confirmed_at', $date)
                ->sum('total');
            $salesChartData[] = [
                'date' => $date->format('M d'),
                'revenue' => $revenue / 100,
            ];
        }
        
        // Customer Stats
        $totalCustomers = $seller->orders()
            ->distinct('buyer_id')
            ->count('buyer_id');
        
        $repeatCustomers = $seller->orders()
            ->select('buyer_id')
            ->groupBy('buyer_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();
        
        $repeatRate = $totalCustomers > 0 ? ($repeatCustomers / $totalCustomers) * 100 : 0;
        
        // Recent Reviews
        $recentReviews = $seller->reviews()
            ->with('buyer')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $stats = [
            'total_products' => $totalProducts,
            'active_products' => $activeProducts,
            'pending_products' => $pendingProducts,
            'rejected_products' => $rejectedProducts,
            'pending_orders' => $pendingOrders,
            'completed_orders' => $completedOrders,
            'total_orders' => $totalOrders,
            'rating' => $seller->rating,
            'pending_balance' => $pendingBalance,
            'available_balance' => $availableBalance,
            'total_revenue' => $totalRevenue,
            'today_revenue' => $todayRevenue,
            'this_week_revenue' => $thisWeekRevenue,
            'this_month_revenue' => $thisMonthRevenue,
            'avg_order_value' => $avgOrderValue,
            'low_stock_count' => $lowStockProducts,
            'out_of_stock_count' => $outOfStockProducts,
            'total_customers' => $totalCustomers,
            'repeat_customers' => $repeatCustomers,
            'repeat_rate' => $repeatRate,
        ];

        $recentOrders = $seller->orders()
            ->with(['items.product', 'buyer'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return Inertia::render('Marketplace/Seller/Dashboard', [
            'seller' => $seller,
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
            'salesChartData' => $salesChartData,
            'recentReviews' => $recentReviews,
        ]);
    }

    public function register()
    {
        $existingSeller = $this->sellerService->getByUserId(auth()->id());

        if ($existingSeller) {
            return redirect()->route('marketplace.seller.dashboard');
        }

        return Inertia::render('Marketplace/Seller/Register', [
            'provinces' => $this->sellerService->getProvinces(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|in:individual,registered',
            'province' => 'required|string',
            'district' => 'required|string',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string|max:1000',
            'nrc_front' => 'required|image|max:5120',
            'nrc_back' => 'required|image|max:5120',
            'business_cert' => 'nullable|file|max:5120',
        ]);

        // Upload KYC documents
        $kycDocuments = [];
        
        if ($request->hasFile('nrc_front')) {
            $kycDocuments['nrc_front'] = $request->file('nrc_front')
                ->store('marketplace/kyc', 'public');
        }
        
        if ($request->hasFile('nrc_back')) {
            $kycDocuments['nrc_back'] = $request->file('nrc_back')
                ->store('marketplace/kyc', 'public');
        }
        
        if ($request->hasFile('business_cert')) {
            $kycDocuments['business_cert'] = $request->file('business_cert')
                ->store('marketplace/kyc', 'public');
        }

        $seller = $this->sellerService->register($request->user()->id, [
            'business_name' => $validated['business_name'],
            'business_type' => $validated['business_type'],
            'province' => $validated['province'],
            'district' => $validated['district'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'description' => $validated['description'],
            'kyc_documents' => $kycDocuments,
        ]);

        return redirect()->route('marketplace.seller.dashboard')
            ->with('success', 'Registration submitted! We will review your documents and notify you.');
    }

    public function profile(Request $request)
    {
        $seller = $this->sellerService->getByUserId($request->user()->id);

        if (!$seller) {
            return redirect()->route('marketplace.seller.register');
        }

        return Inertia::render('Marketplace/Seller/Profile', [
            'seller' => $seller,
            'provinces' => $this->sellerService->getProvinces(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $seller = $this->sellerService->getByUserId($request->user()->id);

        if (!$seller) {
            abort(404);
        }

        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|max:2048',
        ]);

        $data = [
            'business_name' => $validated['business_name'],
            'description' => $validated['description'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
        ];

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($seller->logo_path) {
                Storage::disk('public')->delete($seller->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('marketplace/logos', 'public');
        }

        $this->sellerService->updateProfile($seller->id, $data);

        return back()->with('success', 'Profile updated.');
    }
}
