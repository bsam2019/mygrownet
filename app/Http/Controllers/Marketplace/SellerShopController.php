<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SellerShopController extends Controller
{
    public function follow(Request $request, int $sellerId)
    {
        $seller = DB::table('marketplace_sellers')->find($sellerId);

        if (!$seller) {
            abort(404);
        }

        $userId = $request->user()->id;

        $existing = DB::table('marketplace_seller_followers')
            ->where('seller_id', $sellerId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Already following this seller',
                'is_following' => true,
                'followers_count' => DB::table('marketplace_seller_followers')
                    ->where('seller_id', $sellerId)
                    ->count(),
            ]);
        }

        DB::table('marketplace_seller_followers')->insert([
            'seller_id' => $sellerId,
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Now following ' . $seller->business_name,
            'is_following' => true,
            'followers_count' => DB::table('marketplace_seller_followers')
                ->where('seller_id', $sellerId)
                ->count(),
        ]);
    }

    public function unfollow(Request $request, int $sellerId)
    {
        $seller = DB::table('marketplace_sellers')->find($sellerId);

        if (!$seller) {
            abort(404);
        }

        $userId = $request->user()->id;

        DB::table('marketplace_seller_followers')
            ->where('seller_id', $sellerId)
            ->where('user_id', $userId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Unfollowed ' . $seller->business_name,
            'is_following' => false,
            'followers_count' => DB::table('marketplace_seller_followers')
                ->where('seller_id', $sellerId)
                ->count(),
        ]);
    }

    public function reviews(int $sellerId, Request $request)
    {
        $seller = DB::table('marketplace_sellers')->find($sellerId);

        if (!$seller) {
            abort(404);
        }

        $reviews = DB::table('marketplace_reviews')
            ->leftJoin('users', 'marketplace_reviews.buyer_id', '=', 'users.id')
            ->leftJoin('marketplace_products', 'marketplace_reviews.product_id', '=', 'marketplace_products.id')
            ->where('marketplace_reviews.seller_id', $sellerId)
            ->where('marketplace_reviews.is_approved', true)
            ->select(
                'marketplace_reviews.*',
                'users.id as buyer_user_id',
                'users.name as buyer_name',
                'marketplace_products.id as product_id_value',
                'marketplace_products.name as product_name',
                'marketplace_products.slug as product_slug'
            )
            ->orderByDesc('marketplace_reviews.created_at')
            ->paginate(10);

        $transformedReviews = $reviews->getCollection()->map(function ($review) {
            return (array) $review;
        });

        $reviews->setCollection(collect($transformedReviews));

        $ratingBreakdown = DB::table('marketplace_reviews')
            ->where('seller_id', $sellerId)
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

    public function stats(int $sellerId)
    {
        $seller = DB::table('marketplace_sellers')->find($sellerId);

        if (!$seller) {
            abort(404);
        }

        return response()->json([
            'products_count' => DB::table('marketplace_products')
                ->where('seller_id', $sellerId)
                ->where('status', 'active')
                ->count(),
            'total_sold' => $seller->total_orders,
            'response_rate' => $seller->response_rate ?? 98,
            'followers_count' => DB::table('marketplace_seller_followers')
                ->where('seller_id', $sellerId)
                ->count(),
            'rating' => $seller->rating ?? 0,
            'review_count' => DB::table('marketplace_reviews')
                ->where('seller_id', $sellerId)
                ->where('is_approved', true)
                ->count(),
        ]);
    }
}
