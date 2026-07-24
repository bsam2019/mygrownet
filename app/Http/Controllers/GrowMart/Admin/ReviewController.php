<?php

namespace App\Http\Controllers\GrowMart\Admin;

use App\Http\Controllers\Controller;
use App\Domain\GrowMart\Repositories\ReviewRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends Controller
{
    public function __construct(
        private readonly ReviewRepositoryInterface $reviewRepository,
    ) {}

    public function index(Request $request)
    {
        $reviews = $this->reviewRepository->findAll([
            'status' => $request->status,
            'search' => $request->q,
        ]);

        $formatted = array_map(fn($r) => [
            'id' => $r['id'],
            'rating' => $r['rating'],
            'review_text' => $r['review_text'] ?? '',
            'is_approved' => $r['is_approved'] ?? false,
            'is_verified_purchase' => $r['is_verified_purchase'] ?? false,
            'helpful_count' => $r['helpful_count'] ?? 0,
            'seller_response' => $r['seller_response'] ?? null,
            'created_at' => \Carbon\Carbon::parse($r['created_at'])->format('M d, Y g:i A'),
            'user' => ['id' => $r['user']['id'] ?? null, 'name' => $r['user']['name'] ?? 'Unknown'],
            'product' => ['id' => $r['product']['id'] ?? null, 'name' => $r['product']['name'] ?? 'Unknown'],
        ], $reviews['data'] ?? []);

        return Inertia::render('GrowMart/Admin/Reviews/Index', [
            'reviews' => $formatted,
            'filters' => $request->only(['status', 'q']),
        ]);
    }

    public function approve(int $id)
    {
        $this->reviewRepository->update($id, ['is_approved' => true]);
        return back()->with('success', 'Review approved.');
    }

    public function reject(int $id)
    {
        $this->reviewRepository->update($id, ['is_approved' => false]);
        return back()->with('success', 'Review rejected.');
    }

    public function respond(Request $request, int $id)
    {
        $validated = $request->validate([
            'response' => 'required|string|max:1000',
        ]);

        $this->reviewRepository->update($id, [
            'seller_response' => $validated['response'],
            'seller_responded_at' => now()->toDateTimeString(),
        ]);

        return back()->with('success', 'Response saved.');
    }

    public function destroy(int $id)
    {
        $this->reviewRepository->delete($id);
        return back()->with('success', 'Review deleted.');
    }
}
