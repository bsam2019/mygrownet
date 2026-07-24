<?php

namespace App\Http\Controllers\ZamStay;

use App\Domain\ZamStay\Services\ReviewService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        private readonly ReviewService $reviewService,
    ) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:zamstay_bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $this->reviewService->create($request->user()->id, $validated);

        return back()->with('success', 'Review submitted successfully.');
    }
}
