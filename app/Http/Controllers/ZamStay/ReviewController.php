<?php

namespace App\Http\Controllers\ZamStay;

use App\Http\Controllers\Controller;
use App\Models\ZamStayBooking;
use App\Models\ZamStayReview;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:zamstay_bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $booking = ZamStayBooking::findOrFail($validated['booking_id']);

        if ($booking->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($booking->status !== 'confirmed') {
            return back()->withErrors(['booking' => 'You can only review confirmed bookings.']);
        }

        if ($booking->review()->exists()) {
            return back()->withErrors(['booking' => 'You have already reviewed this booking.']);
        }

        ZamStayReview::create([
            'booking_id' => $booking->id,
            'user_id' => $request->user()->id,
            'property_id' => $booking->property_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return back()->with('success', 'Review submitted successfully.');
    }
}
