<?php

namespace App\Http\Controllers\GrowMart\Admin;

use App\Http\Controllers\Controller;
use App\Models\GrowMart\GrowMartReview;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = GrowMartReview::with(['user', 'product']);

        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->where('is_approved', false);
            } elseif ($request->status === 'approved') {
                $query->where('is_approved', true);
            }
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($qry) use ($q) {
                $qry->where('review_text', 'like', "%{$q}%")
                    ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$q}%"))
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$q}%"));
            });
        }

        $reviews = $query->latest()->paginate(20)->withQueryString();

        $formatted = $reviews->through(fn($r) => [
            'id' => $r->id,
            'rating' => $r->rating,
            'review_text' => $r->review_text,
            'is_approved' => $r->is_approved,
            'is_verified_purchase' => $r->is_verified_purchase,
            'helpful_count' => $r->helpful_count,
            'seller_response' => $r->seller_response,
            'created_at' => $r->created_at->format('M d, Y g:i A'),
            'user' => ['id' => $r->user?->id, 'name' => $r->user?->name ?? 'Unknown'],
            'product' => ['id' => $r->product?->id, 'name' => $r->product?->name ?? 'Unknown'],
        ]);

        return Inertia::render('GrowMart/Admin/Reviews/Index', [
            'reviews' => $formatted,
            'filters' => $request->only(['status', 'q']),
        ]);
    }

    public function approve(GrowMartReview $review)
    {
        $review->update(['is_approved' => true]);
        return back()->with('success', 'Review approved.');
    }

    public function reject(GrowMartReview $review)
    {
        $review->update(['is_approved' => false]);
        return back()->with('success', 'Review rejected.');
    }

    public function respond(Request $request, GrowMartReview $review)
    {
        $validated = $request->validate([
            'response' => 'required|string|max:1000',
        ]);

        $review->update([
            'seller_response' => $validated['response'],
            'seller_responded_at' => now(),
        ]);

        return back()->with('success', 'Response saved.');
    }

    public function destroy(GrowMartReview $review)
    {
        $review->delete();
        return back()->with('success', 'Review deleted.');
    }
}
