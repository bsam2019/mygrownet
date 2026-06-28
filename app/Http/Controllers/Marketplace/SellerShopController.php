<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceSeller;
use App\Models\MarketplaceSellerFollower;
use App\Models\MarketplaceReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SellerShopController extends Controller
{
    /**
     * Follow a seller
     */
    public function follow(Request $request, int $sellerId)
    {
        $seller = MarketplaceSeller::findOrFail($sellerId);
        $userId = $request->user()->id;

        // Check if already following
        $existing = MarketplaceSellerFollower::where('seller_id', $sellerId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Already following this seller',
                'is_following' => true,
                'followers_count' => $seller->followers()->count(),
            ]);
        }

        MarketplaceSellerFollower::create([
            'seller_id' => $sellerId,
            'user_id' => $userId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Now following ' . $seller->business_name,
            'is_following' => true,
            'followers_count' => $seller->followers()->count(),
        ]);
    }

    /**
     * Unfollow a seller
     */
    public function unfollow(Request $request, int $sellerId)
    {
        $seller = MarketplaceSeller::findOrFail($sellerId);
        $userId = $request->user()->id;

        MarketplaceSellerFollower::where('seller_id', $sellerId)
            ->where('user_id', $userId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Unfollowed ' . $seller->business_name,
            'is_following' => false,
            'followers_count' => $seller->followers()->count(),
        ]);
    }

    /**
     * Get seller reviews with pagination
     */
    public function reviews(int $sellerId, Request $request)
    {
        $seller = MarketplaceSeller::findOrFail($sellerId);

        $reviews = $seller->reviews()
            ->with(['buyer:id,name', 'product:id,name,slug'])
            ->where('is_approved', true)
            ->orderByDesc('created_at')
            ->paginate(10);

        // Calculate rating breakdown
        $ratingBreakdown = $seller->reviews()
            ->where('is_approved', true)
            ->select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $totalReviews = array_sum($ratingBreakdown);
        $breakdown = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = $ratingBreakdown[$i] ?? 0;
            $breakdown[$i] = [
                'count' => $count,
                'percentage' => $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0,
            ];
        }

        return response()->json([
            'reviews' => $reviews,
            'rating_breakdown' => $breakdown,
            'total_reviews' => $totalReviews,
            'average_rating' => $seller->rating ?? 0,
        ]);
    }

    /**
     * Get seller stats
     */
    public function stats(int $sellerId)
    {
        $seller = MarketplaceSeller::findOrFail($sellerId);

        return response()->json([
            'products_count' => $seller->products()->where('status', 'active')->count(),
            'total_sold' => $seller->total_orders,
            'response_rate' => $seller->response_rate ?? 98,
            'followers_count' => $seller->followers()->count(),
            'rating' => $seller->rating ?? 0,
            'review_count' => $seller->reviews()->where('is_approved', true)->count(),
        ]);
    }
}
