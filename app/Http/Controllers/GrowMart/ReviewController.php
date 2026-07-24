<?php

namespace App\Http\Controllers\GrowMart;

use App\Http\Controllers\Controller;
use App\Domain\GrowMart\Repositories\ReviewRepositoryInterface;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        private readonly ReviewRepositoryInterface $reviewRepository,
    ) {}

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:growmart_products,id',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:2000',
        ]);

        $existing = $this->reviewRepository->findUserReview(auth()->id(), $request->product_id);

        if ($existing) {
            $this->reviewRepository->update($existing['id'], [
                'rating' => $request->rating,
                'review_text' => $request->review_text,
            ]);
        } else {
            $this->reviewRepository->save([
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
        $reviews = $this->reviewRepository->findByProduct($productId);
        $averageRating = $this->reviewRepository->getAverageRating($productId);

        $formatted = array_map(fn($r) => [
            'id' => $r['id'],
            'user_name' => $r['user']['name'] ?? 'Unknown',
            'rating' => $r['rating'],
            'review_text' => $r['review_text'] ?? '',
            'created_at' => \Carbon\Carbon::parse($r['created_at'])->diffForHumans(),
        ], $reviews);

        return response()->json([
            'reviews' => $formatted,
            'average_rating' => $averageRating,
            'total_reviews' => count($formatted),
        ]);
    }
}
