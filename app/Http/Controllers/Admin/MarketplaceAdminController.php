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
            'pending_reviews' => MarketplaceReview::where('is_approved', false)->count(),
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

    public function approveProduct(int $id)
    {
        $product = MarketplaceProduct::findOrFail($id);
        
        $product->update(['status' => 'active']);

        // TODO: Send notification to seller

        return back()->with('success', 'Product approved.');
    }

    public function rejectProduct(Request $request, int $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $product = MarketplaceProduct::findOrFail($id);
        
        $product->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        // TODO: Send notification to seller

        return back()->with('success', 'Product rejected.');
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
        $query = MarketplaceReview::with(['product', 'buyer']);

        // Filter by approval status
        if ($request->has('approved')) {
            $query->where('is_approved', $request->approved === 'true');
        }

        $reviews = $query->latest()->paginate(20);

        return Inertia::render('Admin/Marketplace/Reviews/Index', [
            'reviews' => $reviews,
            'filters' => $request->only(['approved']),
        ]);
    }

    public function approveReview(int $id)
    {
        $review = MarketplaceReview::findOrFail($id);
        $review->update(['is_approved' => true]);

        return back()->with('success', 'Review approved.');
    }

    public function rejectReview(int $id)
    {
        $review = MarketplaceReview::findOrFail($id);
        $review->update(['is_approved' => false]);

        return back()->with('success', 'Review rejected.');
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
}
