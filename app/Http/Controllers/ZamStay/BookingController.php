<?php

namespace App\Http\Controllers\ZamStay;

use App\Domain\Payment\DTOs\CollectionRequest;
use App\Domain\Payment\Services\PaymentService;
use App\Domain\ZamStay\Services\BookingService;
use App\Domain\ZamStay\Services\PropertyService;
use App\Http\Controllers\Controller;
use App\Notifications\ZamStay\BookingCancelledNotification;
use App\Notifications\ZamStay\BookingConfirmedNotification;
use App\Notifications\ZamStay\BookingCreatedNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly PropertyService $propertyService,
    ) {}

    public function checkout(int $propertyId, Request $request)
    {
        $property = $this->propertyService->findOrFail($propertyId);

        return Inertia::render('ZamStay/Checkout', [
            'property' => $property->toArray(),
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

        $booking = $this->bookingService->create($request->user()->id, $validated);

        $bookingArr = $booking->toArray();
        $request->user()->notify(new BookingCreatedNotification($bookingArr, 'guest'));

        return redirect()->route('zamstay.bookings.show', $booking->id)
            ->with('success', 'Booking created! Please complete payment to confirm.');
    }

    public function pay(int $id, Request $request, PaymentService $paymentService)
    {
        $booking = $this->bookingService->findById($id);
        if (!$booking || $booking->userId !== $request->user()->id) {
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

        $this->bookingService->updatePaymentInfo($id, $validated['payment_method'], $validated['payment_phone'] ?? null);

        if ($validated['payment_method'] === 'mobile_money') {
            $network = $this->detectNetwork($validated['payment_phone']);
            $property = $this->propertyService->findById($booking->propertyId);

            $collectRequest = new CollectionRequest(
                phoneNumber: $validated['payment_phone'],
                amount: (float) $booking->totalPrice,
                currency: 'ZMW',
                provider: $network,
                reference: 'ZAM-' . $booking->id . '-' . now()->timestamp,
                description: "ZamStay payment for booking #{$booking->id} - " . ($property?->title ?? ''),
                customerName: $request->user()->name,
                customerEmail: $request->user()->email,
                metadata: ['booking_id' => $booking->id, 'module' => 'zamstay'],
            );

            try {
                $response = $paymentService->collect($collectRequest);

                if ($response->success) {
                    $bookingArr = $this->bookingService->markAsPaid($id, $response->transactionId, $response->providerReference)->toArray();
                    $request->user()->notify(new BookingConfirmedNotification($bookingArr, 'guest'));
                    return redirect()->route('zamstay.bookings.show', $id)
                        ->with('success', 'Payment successful! Your booking is confirmed.');
                }

                return back()->with('info', 'Payment initiated. We\'ll confirm your booking once payment is verified.');
            } catch (\Exception $e) {
                return back()->withErrors(['payment' => 'Payment failed: ' . $e->getMessage()]);
            }
        }

        return redirect()->route('zamstay.bookings.show', $id)
            ->with('info', 'Booking submitted. The host will confirm once payment is received.');
    }

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:zamstay_properties,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
        ]);

        $result = $this->bookingService->checkAvailability(
            (int) $request->property_id,
            $request->check_in,
            $request->check_out
        );

        return response()->json($result);
    }

    public function myBookings(Request $request)
    {
        $bookings = $this->bookingService->findByUser($request->user()->id);

        return Inertia::render('ZamStay/MyBookings', [
            'bookings' => $bookings,
        ]);
    }

    public function show(int $id)
    {
        $booking = $this->bookingService->findById($id);

        return Inertia::render('ZamStay/BookingDetail', [
            'booking' => $booking?->toArray(),
        ]);
    }

    public function cancel(int $id, Request $request)
    {
        $booking = $this->bookingService->cancel($id, $request->user()->id);

        $bookingArr = $booking->toArray();
        $request->user()->notify(new BookingCancelledNotification($bookingArr, 'guest'));

        return back()->with('success', 'Booking cancelled successfully.');
    }

    public function confirm(int $id, Request $request)
    {
        $booking = $this->bookingService->confirm($id, $request->user()->id);

        $bookingArr = $booking->toArray();
        $request->user()->notify(new BookingConfirmedNotification($bookingArr, 'guest'));

        return back()->with('success', 'Booking confirmed.');
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
}
