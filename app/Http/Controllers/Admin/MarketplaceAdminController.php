<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceSeller;
use App\Models\MarketplaceProduct;
use App\Models\MarketplaceOrder;
use App\Models\MarketplaceDispute;
use App\Models\MarketplaceReview;
use App\Models\MarketplaceCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MarketplaceAdminController extends Controller
{
    /**
     * Admin Dashboard
     */
    public function dashboard()
    {
        $stats = [
            'pending_sellers' => MarketplaceSeller::where('kyc_status', 'pending')->count(),
            'pending_products' => MarketplaceProduct::where('status', 'pending')->count(),
            'active_sellers' => MarketplaceSeller::where('is_active', true)->count(),
            'total_products' => MarketplaceProduct::count(),
            'total_orders' => MarketplaceOrder::count(),
            'open_disputes' => MarketplaceDispute::where('status', 'open')->count(),
            'total_reviews' => MarketplaceReview::count(),
            'total_revenue' => MarketplaceOrder::where('status', 'completed')->sum('total'),
        ];

        $recentSellers = MarketplaceSeller::where('kyc_status', 'pending')
            ->with('user')
            ->latest()
            ->limit(5)
            ->get();

        $recentProducts = MarketplaceProduct::where('status', 'pending')
            ->with('seller')
            ->latest()
            ->limit(5)
            ->get();

        $recentDisputes = MarketplaceDispute::whereIn('status', ['open', 'investigating'])
            ->with(['order', 'buyer', 'seller'])
            ->latest()
            ->limit(5)
            ->get();

        return Inertia::render('Admin/Marketplace/Dashboard', [
            'stats' => $stats,
            'recentSellers' => $recentSellers,
            'recentProducts' => $recentProducts,
            'recentDisputes' => $recentDisputes,
        ]);
    }

    /**
     * Seller Management
     */
    public function sellers(Request $request)
    {
        $query = MarketplaceSeller::with('user');

        // Filter by status
        if ($request->has('status')) {
            $query->where('kyc_status', $request->status);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $sellers = $query->latest()->paginate(20);

        return Inertia::render('Admin/Marketplace/Sellers/Index', [
            'sellers' => $sellers,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function sellerShow(int $id)
    {
        $seller = MarketplaceSeller::with(['user', 'products', 'orders'])
            ->findOrFail($id);

        $stats = [
            'total_products' => $seller->products()->count(),
            'active_products' => $seller->products()->where('status', 'active')->count(),
            'total_orders' => $seller->orders()->count(),
            'completed_orders' => $seller->orders()->where('status', 'completed')->count(),
            'total_revenue' => $seller->orders()->where('status', 'completed')->sum('total'),
            'disputes' => $seller->disputes()->count(),
        ];

        return Inertia::render('Admin/Marketplace/Sellers/Show', [
            'seller' => $seller,
            'stats' => $stats,
        ]);
    }

    public function approveSeller(int $id)
    {
        $seller = MarketplaceSeller::findOrFail($id);
        
        $seller->update([
            'kyc_status' => 'approved',
            'is_active' => true,
        ]);

        // TODO: Send notification to seller

        return back()->with('success', 'Seller approved successfully.');
    }

    public function rejectSeller(Request $request, int $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $seller = MarketplaceSeller::findOrFail($id);
        
        $seller->update([
            'kyc_status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        // TODO: Send notification to seller

        return back()->with('success', 'Seller rejected.');
    }

    public function suspendSeller(int $id)
    {
        $seller = MarketplaceSeller::findOrFail($id);
        
        $seller->update(['is_active' => false]);

        // TODO: Send notification to seller

        return back()->with('success', 'Seller suspended.');
    }

    public function activateSeller(int $id)
    {
        $seller = MarketplaceSeller::findOrFail($id);
        
        $seller->update(['is_active' => true]);

        // TODO: Send notification to seller

        return back()->with('success', 'Seller activated.');
    }

    /**
     * Product Moderation
     */
    public function products(Request $request)
    {
        $query = MarketplaceProduct::with('seller');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $products = $query->latest()->paginate(20);

        return Inertia::render('Admin/Marketplace/Products/Index', [
            'products' => $products,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function productShow(int $id)
    {
        $product = MarketplaceProduct::with(['seller.user', 'category'])
            ->findOrFail($id);

        return Inertia::render('Admin/Marketplace/Products/Show', [
            'product' => $product,
            'rejectionCategories' => self::getRejectionCategories(),
        ]);
    }

    public function approveProduct(int $id)
    {
        $product = MarketplaceProduct::findOrFail($id);
        
        $product->update([
            'status' => 'active',
            'rejection_reason' => null,
            'rejection_category' => null,
            'field_feedback' => null,
            'appeal_message' => null,
            'appealed_at' => null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // TODO: Send notification to seller

        return back()->with('success', 'Product approved.');
    }

    public function rejectProduct(Request $request, int $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'category' => 'required|string|in:policy_violation,poor_quality,misleading_info,prohibited_item,incomplete_info,pricing_issue,image_issue,other',
            'field_feedback' => 'nullable|array',
            'field_feedback.*.field' => 'required_with:field_feedback|string',
            'field_feedback.*.message' => 'required_with:field_feedback|string',
        ]);

        $product = MarketplaceProduct::findOrFail($id);
        
        $product->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
            'rejection_category' => $request->category,
            'field_feedback' => $request->field_feedback,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // TODO: Send notification to seller

        return back()->with('success', 'Product rejected.');
    }

    public function requestChanges(Request $request, int $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'category' => 'required|string|in:policy_violation,poor_quality,misleading_info,prohibited_item,incomplete_info,pricing_issue,image_issue,other',
            'field_feedback' => 'nullable|array',
            'field_feedback.*.field' => 'required_with:field_feedback|string',
            'field_feedback.*.message' => 'required_with:field_feedback|string',
        ]);

        $product = MarketplaceProduct::findOrFail($id);
        
        $product->update([
            'status' => 'changes_requested',
            'rejection_reason' => $request->reason,
            'rejection_category' => $request->category,
            'field_feedback' => $request->field_feedback,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // TODO: Send notification to seller

        return back()->with('success', 'Changes requested from seller.');
    }

    /**
     * Get rejection categories for frontend
     */
    public static function getRejectionCategories(): array
    {
        return [
            'policy_violation' => [
                'label' => 'Policy Violation',
                'description' => 'Product violates marketplace policies or terms of service',
            ],
            'poor_quality' => [
                'label' => 'Poor Quality Images/Content',
                'description' => 'Images are blurry, low resolution, or content is poorly written',
            ],
            'misleading_info' => [
                'label' => 'Misleading Information',
                'description' => 'Product description or title is misleading or inaccurate',
            ],
            'prohibited_item' => [
                'label' => 'Prohibited Item',
                'description' => 'This type of product is not allowed on the marketplace',
            ],
            'incomplete_info' => [
                'label' => 'Incomplete Information',
                'description' => 'Missing required details like specifications or sizing',
            ],
            'pricing_issue' => [
                'label' => 'Pricing Issue',
                'description' => 'Price seems incorrect, too high, or suspicious',
            ],
            'image_issue' => [
                'label' => 'Image Issue',
                'description' => 'Images contain watermarks, text overlays, or are not of the actual product',
            ],
            'other' => [
                'label' => 'Other',
                'description' => 'Other issue not listed above',
            ],
        ];
    }

    /**
     * Order Monitoring
     */
    public function orders(Request $request)
    {
        $query = MarketplaceOrder::with(['buyer', 'seller']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by seller
        if ($request->has('seller_id')) {
            $query->where('seller_id', $request->seller_id);
        }

        $orders = $query->latest()->paginate(20);

        return Inertia::render('Admin/Marketplace/Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['status', 'seller_id']),
        ]);
    }

    public function orderShow(int $id)
    {
        $order = MarketplaceOrder::with(['buyer', 'seller', 'items.product'])
            ->findOrFail($id);

        return Inertia::render('Admin/Marketplace/Orders/Show', [
            'order' => $order,
        ]);
    }

    /**
     * Dispute Resolution
     */
    public function disputes(Request $request)
    {
        $query = MarketplaceDispute::with(['order', 'buyer', 'seller']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $disputes = $query->latest()->paginate(20);

        return Inertia::render('Admin/Marketplace/Disputes/Index', [
            'disputes' => $disputes,
            'filters' => $request->only(['status']),
        ]);
    }

    public function disputeShow(int $id)
    {
        $dispute = MarketplaceDispute::with(['order.items.product', 'buyer', 'seller'])
            ->findOrFail($id);

        return Inertia::render('Admin/Marketplace/Disputes/Show', [
            'dispute' => $dispute,
        ]);
    }

    public function resolveDispute(Request $request, int $id)
    {
        $request->validate([
            'resolution_type' => 'required|in:refund,replacement,partial_refund,no_action',
            'resolution' => 'required|string|max:1000',
        ]);

        $dispute = MarketplaceDispute::findOrFail($id);
        
        $dispute->update([
            'status' => 'resolved',
            'resolution_type' => $request->resolution_type,
            'resolution' => $request->resolution,
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        // TODO: Process refund if applicable
        // TODO: Send notifications to buyer and seller

        return back()->with('success', 'Dispute resolved.');
    }

    /**
     * Reviews Moderation
     */
    public function reviews(Request $request)
    {
        $query = MarketplaceReview::with(['product', 'buyer', 'seller']);

        // Search by rating
        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }

        $reviews = $query->latest()->paginate(20);

        return Inertia::render('Admin/Marketplace/Reviews/Index', [
            'reviews' => $reviews,
            'filters' => $request->only(['rating']),
        ]);
    }

    public function deleteReview(int $id)
    {
        $review = MarketplaceReview::findOrFail($id);
        $review->delete();

        return back()->with('success', 'Review deleted.');
    }

    /**
     * Categories Management
     */
    public function categories(Request $request)
    {
        $query = MarketplaceCategory::query();

        $categories = $query->orderBy('sort_order')->get();

        return Inertia::render('Admin/Marketplace/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:marketplace_categories,slug',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        MarketplaceCategory::create($request->all());

        return back()->with('success', 'Category created.');
    }

    public function updateCategory(Request $request, int $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:marketplace_categories,slug,' . $id,
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $category = MarketplaceCategory::findOrFail($id);
        $category->update($request->all());

        return back()->with('success', 'Category updated.');
    }

    public function deleteCategory(int $id)
    {
        $category = MarketplaceCategory::findOrFail($id);
        
        // Check if category has products
        if ($category->products()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete category with products.']);
        }

        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    /**
     * Analytics
     */
    public function analytics()
    {
        // TODO: Implement comprehensive analytics

        return Inertia::render('Admin/Marketplace/Analytics');
    }

    /**
     * Settings
     */
    public function settings()
    {
        // TODO: Implement marketplace settings

        return Inertia::render('Admin/Marketplace/Settings');
    }

    public function updateSettings(Request $request)
    {
        // TODO: Implement settings update logic
        
        return back()->with('success', 'Settings updated.');
    }
}
