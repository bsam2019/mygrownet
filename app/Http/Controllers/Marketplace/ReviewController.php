<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceReview;
use App\Models\MarketplaceReviewVote;
use App\Models\MarketplaceOrder;
use App\Models\MarketplaceProduct;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Store a new review
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:marketplace_orders,id',
            'product_id' => 'required|exists:marketplace_products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:1000',
        ]);

        $order = MarketplaceOrder::findOrFail($validated['order_id']);
        
        // Verify order belongs to user
        if ($order->buyer_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Verify order is completed
        if ($order->status !== 'completed') {
            return back()->withErrors(['message' => 'You can only review completed orders']);
        }

        // Check if review already exists
        $existingReview = MarketplaceReview::where('order_id', $order->id)
            ->where('product_id', $validated['product_id'])
            ->where('buyer_id', auth()->id())
            ->first();

        if ($existingReview) {
            return back()->withErrors(['message' => 'You have already reviewed this product']);
        }

        $product = MarketplaceProduct::findOrFail($validated['product_id']);

        // Create review
        MarketplaceReview::create([
            'product_id' => $validated['product_id'],
            'order_id' => $order->id,
            'buyer_id' => auth()->id(),
            'seller_id' => $product->seller_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'is_verified_purchase' => true,
            'is_approved' => true, // Auto-approve for now
        ]);

        // Update product rating
        $this->updateProductRating($product->id);

        // Update seller rating
        $this->updateSellerRating($product->seller_id);

        return back()->with('success', 'Review submitted successfully!');
    }

    /**
     * Vote on review helpfulness
     */
    public function vote(Request $request, int $reviewId)
    {
        $validated = $request->validate([
            'is_helpful' => 'required|boolean',
        ]);

        $review = MarketplaceReview::findOrFail($reviewId);

        // Check if user already voted
        $existingVote = MarketplaceReviewVote::where('review_id', $reviewId)
            ->where('user_id', auth()->id())
            ->first();

        if ($existingVote) {
            // Update existing vote
            $existingVote->update(['is_helpful' => $validated['is_helpful']]);
        } else {
            // Create new vote
            MarketplaceReviewVote::create([
                'review_id' => $reviewId,
                'user_id' => auth()->id(),
                'is_helpful' => $validated['is_helpful'],
            ]);
        }

        // Update review counts
        $review->update([
            'helpful_count' => MarketplaceReviewVote::where('review_id', $reviewId)
                ->where('is_helpful', true)
                ->count(),
            'not_helpful_count' => MarketplaceReviewVote::where('review_id', $reviewId)
                ->where('is_helpful', false)
                ->count(),
        ]);

        return back();
    }

    /**
     * Seller response to review
     */
    public function respond(Request $request, int $reviewId)
    {
        $validated = $request->validate([
            'response' => 'required|string|max:500',
        ]);

        $review = MarketplaceReview::findOrFail($reviewId);

        // Verify seller owns the product
        $seller = auth()->user()->marketplaceSeller;
        if (!$seller || $review->seller_id !== $seller->id) {
            abort(403, 'Unauthorized');
        }

        $review->update([
            'seller_response' => $validated['response'],
            'seller_responded_at' => now(),
        ]);

        return back()->with('success', 'Response posted successfully!');
    }

    /**
     * Update product average rating
     */
    private function updateProductRating(int $productId): void
    {
        $product = MarketplaceProduct::findOrFail($productId);
        
        $averageRating = MarketplaceReview::where('product_id', $productId)
            ->where('is_approved', true)
            ->avg('rating');

        $product->update([
            'rating' => $averageRating ?? 0,
        ]);
    }

    /**
     * Update seller average rating
     */
    private function updateSellerRating(int $sellerId): void
    {
        $averageRating = MarketplaceReview::where('seller_id', $sellerId)
            ->where('is_approved', true)
            ->avg('rating');

        \App\Models\MarketplaceSeller::where('id', $sellerId)->update([
            'rating' => $averageRating ?? 0,
        ]);
    }
}
