<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:marketplace_orders,id',
            'product_id' => 'required|exists:marketplace_products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:1000',
        ]);

        $order = DB::table('marketplace_orders')->find($validated['order_id']);

        if (!$order) {
            abort(404);
        }

        if ($order->buyer_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        if ($order->status !== 'completed') {
            return back()->withErrors(['message' => 'You can only review completed orders']);
        }

        $existingReview = DB::table('marketplace_reviews')
            ->where('order_id', $order->id)
            ->where('product_id', $validated['product_id'])
            ->where('buyer_id', auth()->id())
            ->first();

        if ($existingReview) {
            return back()->withErrors(['message' => 'You have already reviewed this product']);
        }

        $product = DB::table('marketplace_products')->find($validated['product_id']);

        if (!$product) {
            abort(404);
        }

        DB::table('marketplace_reviews')->insert([
            'product_id' => $validated['product_id'],
            'order_id' => $order->id,
            'buyer_id' => auth()->id(),
            'seller_id' => $product->seller_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'is_verified_purchase' => true,
            'is_approved' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->updateProductRating($product->id);

        $this->updateSellerRating($product->seller_id);

        return back()->with('success', 'Review submitted successfully!');
    }

    public function vote(Request $request, int $reviewId)
    {
        $validated = $request->validate([
            'is_helpful' => 'required|boolean',
        ]);

        $review = DB::table('marketplace_reviews')->find($reviewId);

        if (!$review) {
            abort(404);
        }

        $existingVote = DB::table('marketplace_review_votes')
            ->where('review_id', $reviewId)
            ->where('user_id', auth()->id())
            ->first();

        if ($existingVote) {
            DB::table('marketplace_review_votes')
                ->where('id', $existingVote->id)
                ->update(['is_helpful' => $validated['is_helpful']]);
        } else {
            DB::table('marketplace_review_votes')->insert([
                'review_id' => $reviewId,
                'user_id' => auth()->id(),
                'is_helpful' => $validated['is_helpful'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $helpfulCount = DB::table('marketplace_review_votes')
            ->where('review_id', $reviewId)
            ->where('is_helpful', true)
            ->count();

        $notHelpfulCount = DB::table('marketplace_review_votes')
            ->where('review_id', $reviewId)
            ->where('is_helpful', false)
            ->count();

        DB::table('marketplace_reviews')
            ->where('id', $reviewId)
            ->update([
                'helpful_count' => $helpfulCount,
                'not_helpful_count' => $notHelpfulCount,
            ]);

        return back();
    }

    public function respond(Request $request, int $reviewId)
    {
        $validated = $request->validate([
            'response' => 'required|string|max:500',
        ]);

        $review = DB::table('marketplace_reviews')->find($reviewId);

        if (!$review) {
            abort(404);
        }

        $seller = DB::table('marketplace_sellers')
            ->where('user_id', auth()->id())
            ->first();

        if (!$seller || $review->seller_id !== $seller->id) {
            abort(403, 'Unauthorized');
        }

        DB::table('marketplace_reviews')
            ->where('id', $reviewId)
            ->update([
                'seller_response' => $validated['response'],
                'seller_responded_at' => now(),
            ]);

        return back()->with('success', 'Response posted successfully!');
    }

    private function updateProductRating(int $productId): void
    {
        $averageRating = DB::table('marketplace_reviews')
            ->where('product_id', $productId)
            ->where('is_approved', true)
            ->avg('rating');

        DB::table('marketplace_products')
            ->where('id', $productId)
            ->update(['rating' => $averageRating ?? 0]);
    }

    private function updateSellerRating(int $sellerId): void
    {
        $averageRating = DB::table('marketplace_reviews')
            ->where('seller_id', $sellerId)
            ->where('is_approved', true)
            ->avg('rating');

        DB::table('marketplace_sellers')
            ->where('id', $sellerId)
            ->update(['rating' => $averageRating ?? 0]);
    }
}
