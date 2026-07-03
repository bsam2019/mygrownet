<?php

namespace App\Http\Controllers\ZamStay;

use App\Domain\Payment\DTOs\CollectionRequest;
use App\Domain\Payment\Services\PaymentService;
use App\Http\Controllers\Controller;
use App\Models\ZamStay\ZamStayBooking;
use App\Models\ZamStay\ZamStayProperty;
use App\Notifications\ZamStay\BookingCancelledNotification;
use App\Notifications\ZamStay\BookingConfirmedNotification;
use App\Notifications\ZamStay\BookingCreatedNotification;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function checkout(ZamStayProperty $property, Request $request)
    {
        $property->load('owner');

        return Inertia::render('ZamStay/Checkout', [
            'property' => $property,
            'checkIn' => $request->check_in,
            'checkOut' => $request->check_out,
            'guests' => $request->guests ?? 1,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:zamstay_properties,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        $property = ZamStayProperty::findOrFail($validated['property_id']);

        if ($property->max_guests < $validated['guests']) {
            return back()->withErrors(['guests' => 'This property only accommodates up to ' . $property->max_guests . ' guests.']);
        }

        $exists = ZamStayBooking::where('property_id', $property->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($validated) {
                $q->whereBetween('check_in', [$validated['check_in'], $validated['check_out']])
                  ->orWhereBetween('check_out', [$validated['check_in'], $validated['check_out']])
                  ->orWhere(function ($q2) use ($validated) {
                      $q2->where('check_in', '<=', $validated['check_in'])
                         ->where('check_out', '>=', $validated['check_out']);
                  });
            })->exists();

        if ($exists) {
            return back()->withErrors(['dates' => 'Property is not available for the selected dates.']);
        }

        $checkIn = \Carbon\Carbon::parse($validated['check_in']);
        $checkOut = \Carbon\Carbon::parse($validated['check_out']);
        $nights = $checkIn->diffInDays($checkOut);
        $totalPrice = $nights * $property->price_per_night;

        $booking = ZamStayBooking::create([
            'property_id' => $property->id,
            'user_id' => $request->user()->id,
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'total_price' => $totalPrice,
            'status' => 'pending',
            'guests' => $validated['guests'],
            'special_requests' => $validated['special_requests'] ?? null,
        ]);

        $booking->load('property.owner', 'user');
        $booking->user->notify(new BookingCreatedNotification($booking, 'guest'));
        $booking->property->owner->notify(new BookingCreatedNotification($booking, 'host'));

        return redirect()->route('zamstay.bookings.show', $booking->id)
            ->with('success', 'Booking created! Please complete payment to confirm.');
    }

    public function pay(ZamStayBooking $booking, Request $request, PaymentService $paymentService)
    {
        if ($booking->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($booking->status === 'confirmed') {
            return back()->with('info', 'This booking is already confirmed.');
        }

        if ($booking->status === 'cancelled') {
            return back()->withErrors(['booking' => 'Cannot pay for a cancelled booking.']);
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:mobile_money,bank_transfer,cash',
            'payment_phone' => 'required_if:payment_method,mobile_money|string|max:20',
        ]);

        $booking->update([
            'payment_method' => $validated['payment_method'],
            'payment_phone' => $validated['payment_phone'] ?? null,
        ]);

        if ($validated['payment_method'] === 'mobile_money') {
            $network = $this->detectNetwork($validated['payment_phone']);

            $collectRequest = new CollectionRequest(
                phoneNumber: $validated['payment_phone'],
                amount: (float) $booking->total_price,
                currency: 'ZMW',
                provider: $network,
                reference: 'ZAM-' . $booking->id . '-' . now()->timestamp,
                description: "ZamStay payment for booking #{$booking->id} - {$booking->property->title}",
                customerName: $request->user()->name,
                customerEmail: $request->user()->email,
                metadata: ['booking_id' => $booking->id, 'module' => 'zamstay'],
            );

            try {
                $response = $paymentService->collect($collectRequest);
                $booking->update([
                    'transaction_id' => $response->transactionId,
                    'payment_reference' => $response->providerReference,
                ]);

                    if ($response->success) {
                    $booking->update(['status' => 'confirmed', 'paid_at' => now()]);
                    $booking->load('property.owner', 'user');
                    $booking->user->notify(new BookingConfirmedNotification($booking, 'guest'));
                    $booking->property->owner->notify(new BookingConfirmedNotification($booking, 'host'));
                    return redirect()->route('zamstay.bookings.show', $booking->id)
                        ->with('success', 'Payment successful! Your booking is confirmed.');
                }

                return back()->with('info', 'Payment initiated. We\'ll confirm your booking once payment is verified.');
            } catch (\Exception $e) {
                return back()->withErrors(['payment' => 'Payment failed: ' . $e->getMessage()]);
            }
        }

        return redirect()->route('zamstay.bookings.show', $booking->id)
            ->with('info', 'Booking submitted. The host will confirm once payment is received.');
    }

    private function detectNetwork(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '260')) {
            $phone = '0' . substr($phone, 3);
        }
        if (preg_match('/^09[567]\d{7}$/', $phone)) return 'airtel';
        if (preg_match('/^07[567]\d{7}$/', $phone)) return 'zamtel';
        return 'mtn';
    }

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:zamstay_properties,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
        ]);

        $property = ZamStayProperty::findOrFail($request->property_id);

        $exists = ZamStayBooking::where('property_id', $property->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($request) {
                $q->whereBetween('check_in', [$request->check_in, $request->check_out])
                  ->orWhereBetween('check_out', [$request->check_in, $request->check_out])
                  ->orWhere(function ($q2) use ($request) {
                      $q2->where('check_in', '<=', $request->check_in)
                         ->where('check_out', '>=', $request->check_out);
                  });
            })->exists();

        $checkIn = \Carbon\Carbon::parse($request->check_in);
        $checkOut = \Carbon\Carbon::parse($request->check_out);
        $nights = $checkIn->diffInDays($checkOut);
        $totalPrice = $nights * $property->price_per_night;

        return response()->json([
            'available' => !$exists,
            'total_price' => $totalPrice,
            'nights' => $nights,
            'price_per_night' => $property->price_per_night,
        ]);
    }

    public function myBookings(Request $request)
    {
        $bookings = ZamStayBooking::where('user_id', $request->user()->id)
            ->with('property')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('ZamStay/MyBookings', [
            'bookings' => $bookings,
        ]);
    }

    public function show(ZamStayBooking $booking)
    {
        $booking->load(['property.owner', 'review']);

        return Inertia::render('ZamStay/BookingDetail', [
            'booking' => $booking,
        ]);
    }

    public function cancel(ZamStayBooking $booking, Request $request)
    {
        if ($booking->user_id !== $request->user()->id) {
            abort(403);
        }

        $booking->update(['status' => 'cancelled']);
        $booking->load('property.owner', 'user');
        $booking->user->notify(new BookingCancelledNotification($booking, 'guest'));
        $booking->property->owner->notify(new BookingCancelledNotification($booking, 'host'));

        return back()->with('success', 'Booking cancelled successfully.');
    }

    public function confirm(ZamStayBooking $booking, Request $request)
    {
        if ($booking->property->owner_id !== $request->user()->id) {
            abort(403);
        }

        $booking->load('property.owner', 'user');
        $booking->update(['status' => 'confirmed']);
        $booking->user->notify(new BookingConfirmedNotification($booking, 'guest'));
        $booking->property->owner->notify(new BookingConfirmedNotification($booking, 'host'));

        return back()->with('success', 'Booking confirmed.');
    }
}
