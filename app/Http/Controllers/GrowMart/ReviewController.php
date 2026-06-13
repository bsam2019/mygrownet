<?php

namespace App\Http\Controllers\GrowMart;

use App\Http\Controllers\Controller;
use App\Models\GrowMart\GrowMartProduct;
use App\Models\GrowMart\GrowMartReview;
use App\Domain\GrowMart\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
    ) {}

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:growmart_products,id',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:2000',
        ]);

        $existing = GrowMartReview::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($existing) {
            $existing->update([
                'rating' => $request->rating,
                'review_text' => $request->review_text,
            ]);
        } else {
            GrowMartReview::create([
                'user_id' => auth()->id(),
                'product_id' => $request->product_id,
                'rating' => $request->rating,
                'review_text' => $request->review_text,
            ]);
        }

        return back()->with('success', 'Review submitted successfully.');
    }

    public function product(int $productId)
    {
        $reviews = GrowMartReview::with('user')
            ->where('product_id', $productId)
            ->where('is_approved', true)
            ->latest()
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'user_name' => $r->user->name,
                'rating' => $r->rating,
                'review_text' => $r->review_text,
                'created_at' => $r->created_at->diffForHumans(),
            ]);

        $avg = GrowMartReview::where('product_id', $productId)
            ->where('is_approved', true)
            ->avg('rating');

        return response()->json([
            'reviews' => $reviews,
            'average_rating' => $avg ? round($avg, 1) : 0,
            'total_reviews' => $reviews->count(),
        ]);
    }
}
