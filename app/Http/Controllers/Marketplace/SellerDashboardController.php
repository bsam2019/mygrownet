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

    public function index(Request $request)
    {
        $seller = $this->sellerService->getByUserId($request->user()->id);

        if (!$seller) {
            return redirect()->route('marketplace.seller.register');
        }

        $stats = [
            'total_products' => $seller->products()->count(),
            'active_products' => $seller->products()->where('status', 'active')->count(),
            'pending_orders' => $seller->orders()->whereIn('status', ['paid', 'processing'])->count(),
            'total_orders' => $seller->total_orders,
            'rating' => $seller->rating,
            'pending_balance' => $this->escrowService->getSellerPendingBalance($seller->id),
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
