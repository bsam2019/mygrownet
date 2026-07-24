<?php

namespace App\Http\Controllers\GrowMart;

use App\Http\Controllers\Controller;
use App\Domain\GrowMart\Services\OrderService;
use App\Domain\GrowMart\Services\CartService;
use App\Domain\GrowMart\Repositories\OrderRepositoryInterface;
use App\Domain\Payment\Gateways\NOWPaymentsGateway;
use App\Domain\Payment\DTOs\CryptoPaymentRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly CartService $cartService,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly NOWPaymentsGateway $nowPayments,
    ) {}

    public function showCheckout()
    {
        $checkoutData = session('growmart.checkout');

        if (!$checkoutData) {
            return redirect()->route('growmart.checkout')
                ->with('error', 'Checkout session expired. Please try again.');
        }

        $cartSummary = $this->cartService->getSummary(auth()->id());

        if ($cartSummary['item_count'] === 0) {
            return redirect()->route('growmart.cart')
                ->with('error', 'Your cart is empty.');
        }

        $deliveryFee = config('growmart.delivery_fees.' . ($checkoutData['delivery_method'] ?? ''), 0);
        $total = $cartSummary['subtotal'] + $deliveryFee;

        return Inertia::render('GrowMart/Payment/Show', [
            'checkoutData' => $checkoutData,
            'cart' => $cartSummary,
            'cartCount' => $cartSummary['item_count'],
            'total' => $total,
            'total_formatted' => 'K' . number_format($total / 100, 2),
        ]);
    }

    public function process(Request $request)
    {
        $checkoutData = session('growmart.checkout');

        if (!$checkoutData) {
            return redirect()->route('growmart.checkout')
                ->with('error', 'Checkout session expired. Please try again.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:mobile_money,bank_transfer',
            'payment_reference' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $order = $this->orderService->createOrder(auth()->id(), $checkoutData);

            $updates = [
                'status' => 'pending',
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['payment_reference'],
                'payment_submitted_at' => now()->toDateTimeString(),
                'payment_status' => 'pending_verification',
            ];

            if (!empty($validated['phone_number'])) {
                $updates['payment_phone'] = $validated['phone_number'];
            }
            if (!empty($validated['notes'])) {
                $updates['payment_notes'] = $validated['notes'];
            }

            $this->orderRepository->update($order['id'], $updates);

            session()->forget('growmart.checkout');

            return redirect()->route('growmart.orders.show', $order['id'])
                ->with('success', 'Order placed! Payment submitted for verification. We will confirm your payment shortly.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function createCryptoInvoice(): JsonResponse
    {
        $checkoutData = session('growmart.checkout');

        if (!$checkoutData) {
            return response()->json([
                'success' => false,
                'message' => 'Checkout session expired. Please try again.',
            ], 400);
        }

        try {
            $order = $this->orderService->createOrder(auth()->id(), $checkoutData);

            $this->orderRepository->update($order['id'], ['payment_method' => 'crypto']);

            $amountInKw = $order['total'] / 100;
            $response = $this->nowPayments->createCryptoInvoice(new CryptoPaymentRequest(
                amount: $amountInKw,
                currency: 'ZMW',
                reference: "GM-{$order['id']}",
                customerEmail: auth()->user()?->email,
                description: "GrowMart Order #{$order['order_number']}"
            ));

            if ($response->success) {
                $paymentDetails = array_merge($order['payment_details'] ?? [], [
                    'nowpayments_invoice_id' => $response->transactionId,
                    'nowpayments_invoice_url' => $response->rawResponse['invoice_url'] ?? null,
                    'nowpayments_pay_address' => $response->rawResponse['pay_address'] ?? null,
                    'nowpayments_pay_amount' => $response->rawResponse['pay_amount'] ?? null,
                    'nowpayments_pay_currency' => $response->rawResponse['pay_currency'] ?? null,
                    'nowpayments_price_amount' => $response->rawResponse['price_amount'] ?? null,
                    'nowpayments_price_currency' => $response->rawResponse['price_currency'] ?? null,
                    'nowpayments_created_at' => now()->toIso8601String(),
                ]);

                $this->orderRepository->update($order['id'], ['payment_details' => $paymentDetails]);

                Log::info('GrowMart crypto invoice created', [
                    'order_id' => $order['id'],
                    'invoice_id' => $response->transactionId,
                    'invoice_url' => $response->rawResponse['invoice_url'] ?? null,
                ]);

                session()->forget('growmart.checkout');

                return response()->json([
                    'success' => true,
                    'order_id' => $order['id'],
                    'invoice_url' => $response->rawResponse['invoice_url'] ?? null,
                    'pay_address' => $response->rawResponse['pay_address'] ?? null,
                    'pay_amount' => $response->rawResponse['pay_amount'] ?? null,
                    'pay_currency' => $response->rawResponse['pay_currency'] ?? null,
                    'transaction_id' => $response->transactionId,
                ]);
            }

            Log::error('GrowMart crypto invoice creation failed', [
                'order_id' => $order['id'],
                'message' => $response->message,
            ]);

            $this->orderRepository->update($order['id'], ['status' => 'cancelled']);

            return response()->json([
                'success' => false,
                'message' => $response->message ?? 'Failed to create crypto payment invoice.',
            ], 400);

        } catch (\Exception $e) {
            Log::error('GrowMart crypto invoice exception', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment gateway error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
