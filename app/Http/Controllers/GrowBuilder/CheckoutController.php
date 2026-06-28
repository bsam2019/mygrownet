<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\Subdomain;
use App\Domain\GrowBuilder\Payment\Services\GrowBuilderPaymentService;
use App\Domain\GrowBuilder\Payment\DTOs\PaymentRequest;
use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderOrder;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderPayment;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderPaymentSettings;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderProduct;
use App\Models\GrowBuilder\SitePaymentConfig;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
        private GrowBuilderPaymentService $paymentService,
    ) {}

    public function createOrder(Request $request, string $subdomain)
    {
        try {
            $site = $this->siteRepository->findBySubdomain(Subdomain::fromString($subdomain));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Site not found'], 404);
        }

        if (!$site || !$site->isPublished()) {
            return response()->json(['error' => 'Site not found'], 404);
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|string|in:momo,airtel,whatsapp,cod,bank,online',
            'notes' => 'nullable|string|max:500',
        ]);

        $siteId = $site->getId()->value();

        // Validate products and calculate totals
        $orderItems = [];
        $subtotal = 0;

        foreach ($validated['items'] as $item) {
            $product = GrowBuilderProduct::where('site_id', $siteId)
                ->where('id', $item['product_id'])
                ->active()
                ->first();

            if (!$product) {
                return response()->json([
                    'error' => 'Product not found or unavailable',
                ], 400);
            }

            if ($product->track_stock && $product->stock_quantity < $item['quantity']) {
                return response()->json([
                    'error' => "Insufficient stock for {$product->name}",
                ], 400);
            }

            $lineTotal = $product->price * $item['quantity'];
            $subtotal += $lineTotal;

            $orderItems[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $item['quantity'],
                'total' => $lineTotal,
                'image' => $product->main_image,
            ];
        }

        // Create order
        $order = GrowBuilderOrder::create([
            'site_id' => $siteId,
            'order_number' => 'GB-' . strtoupper(substr(md5(uniqid()), 0, 8)),
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_email' => $validated['customer_email'] ?? null,
            'customer_address' => $validated['customer_address'] ?? null,
            'items' => $orderItems,
            'subtotal' => $subtotal,
            'shipping_cost' => 0,
            'discount_amount' => 0,
            'total' => $subtotal,
            'status' => 'pending',
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Decrement stock
        foreach ($validated['items'] as $item) {
            $product = GrowBuilderProduct::find($item['product_id']);
            if ($product && $product->track_stock) {
                $product->decrement('stock_quantity', $item['quantity']);
            }
        }

        // Handle payment based on method
        $response = [
            'success' => true,
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total' => $order->total,
                'formatted_total' => $order->formatted_total,
            ],
        ];

        // Check if site has new payment gateway configured
        $paymentConfig = SitePaymentConfig::where('site_id', $siteId)
            ->where('is_active', true)
            ->first();

        if ($paymentConfig && in_array($validated['payment_method'], ['momo', 'airtel', 'online'])) {
            // Use new payment gateway system
            $paymentResult = $this->initiateNewPayment($order, $siteId);
            $response['payment'] = $paymentResult;
        } elseif (in_array($validated['payment_method'], ['momo', 'airtel'])) {
            // Fallback to legacy payment system
            $paymentResult = $this->initiatePayment($order, $validated['payment_method']);
            $response['payment'] = $paymentResult;
        } elseif ($validated['payment_method'] === 'whatsapp') {
            $settings = GrowBuilderPaymentSettings::where('site_id', $siteId)->first();
            $response['whatsapp_url'] = $this->generateWhatsAppUrl($order, $settings);
        }

        return response()->json($response);
    }

    public function checkPaymentStatus(Request $request, string $subdomain, int $orderId)
    {
        try {
            $site = $this->siteRepository->findBySubdomain(Subdomain::fromString($subdomain));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Site not found'], 404);
        }

        $siteId = $site->getId()->value();
        $order = GrowBuilderOrder::where('site_id', $siteId)
            ->findOrFail($orderId);

        // Check if site uses new payment gateway
        $paymentConfig = SitePaymentConfig::where('site_id', $siteId)
            ->where('is_active', true)
            ->first();

        if ($paymentConfig && $order->payment_reference) {
            // Use new payment gateway system
            try {
                $result = $this->paymentService->verifyPayment($siteId, $order->payment_reference);

                if ($result->success && $result->status->value === 'completed') {
                    $order->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
                } elseif ($result->status->value === 'failed') {
                    $order->update([
                        'status' => 'payment_failed',
                    ]);
                }

                return response()->json([
                    'status' => $order->fresh()->status,
                    'is_paid' => $order->fresh()->isPaid(),
                    'payment_status' => $result->status->value,
                    'message' => $result->message,
                ]);
            } catch (\Exception $e) {
                \Log::error('Payment verification failed', [
                    'order_id' => $orderId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Fallback to legacy payment system
        $payment = $order->latestPayment;

        if (!$payment || !$payment->isPending()) {
            return response()->json([
                'status' => $order->status,
                'is_paid' => $order->isPaid(),
            ]);
        }

        // Check with legacy payment provider
        $settings = GrowBuilderPaymentSettings::where('site_id', $siteId)->first();
        
        if ($settings) {
            $gateway = match ($payment->provider) {
                'momo' => new \App\Infrastructure\GrowBuilder\Services\MoMoPaymentGateway($settings->getMomoConfig()),
                'airtel' => new \App\Infrastructure\GrowBuilder\Services\AirtelMoneyPaymentGateway($settings->getAirtelConfig()),
                default => null,
            };

            if ($gateway) {
                $result = $gateway->checkStatus($payment->transaction_id);

                if ($result->isCompleted()) {
                    $payment->update([
                        'status' => 'completed',
                        'external_reference' => $result->externalReference,
                        'provider_response' => $result->rawResponse,
                        'completed_at' => now(),
                    ]);

                    $order->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                        'payment_reference' => $result->transactionId,
                    ]);
                } elseif ($result->isFailed()) {
                    $payment->update([
                        'status' => 'failed',
                        'status_message' => $result->message,
                        'provider_response' => $result->rawResponse,
                    ]);
                }
            }
        }

        return response()->json([
            'status' => $order->fresh()->status,
            'is_paid' => $order->fresh()->isPaid(),
            'payment_status' => $payment->fresh()->status,
        ]);
    }

    private function initiateNewPayment(GrowBuilderOrder $order, int $siteId): array
    {
        try {
            $paymentRequest = new PaymentRequest(
                amount: number_format($order->total / 100, 2, '.', ''), // Convert from ngwee to kwacha
                currency: 'ZMW',
                phoneNumber: $order->customer_phone,
                reference: $order->order_number,
                description: "Order {$order->order_number}",
                customerName: $order->customer_name,
                customerEmail: $order->customer_email,
                metadata: [
                    'order_id' => $order->id,
                    'site_id' => $siteId,
                ],
                callbackUrl: route('growbuilder.api.payment-webhook', ['siteId' => $siteId]),
                returnUrl: route('growbuilder.api.payment-return', [
                    'subdomain' => $order->site->subdomain,
                    'orderId' => $order->id,
                ]),
            );

            $result = $this->paymentService->initiatePayment($siteId, $paymentRequest);

            if ($result->success) {
                $order->update([
                    'status' => 'payment_pending',
                    'payment_reference' => $result->transactionReference,
                ]);

                return [
                    'status' => $result->status->value,
                    'message' => $result->message ?? 'Payment initiated successfully',
                    'transaction_reference' => $result->transactionReference,
                    'checkout_url' => $result->checkoutUrl,
                ];
            }

            return [
                'error' => $result->message ?? 'Payment initiation failed',
            ];

        } catch (\Exception $e) {
            \Log::error('Payment initiation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => 'Payment initiation failed. Please try again.',
            ];
        }
    }

    private function initiatePayment(GrowBuilderOrder $order, string $method): array
    {
        $settings = GrowBuilderPaymentSettings::where('site_id', $order->site_id)->first();

        if (!$settings) {
            return ['error' => 'Payment not configured'];
        }

        $gateway = match ($method) {
            'momo' => $settings->momo_enabled ? new MoMoPaymentGateway($settings->getMomoConfig()) : null,
            'airtel' => $settings->airtel_enabled ? new AirtelMoneyPaymentGateway($settings->getAirtelConfig()) : null,
            default => null,
        };

        if (!$gateway) {
            return ['error' => 'Payment method not available'];
        }

        $result = $gateway->initiatePayment(
            amountInNgwee: $order->total,
            phoneNumber: $order->customer_phone,
            reference: $order->order_number,
            description: "Order {$order->order_number}",
        );

        // Create payment record
        GrowBuilderPayment::create([
            'order_id' => $order->id,
            'site_id' => $order->site_id,
            'provider' => $method,
            'transaction_id' => $result->transactionId,
            'external_reference' => $result->externalReference,
            'amount' => $order->total,
            'phone_number' => $order->customer_phone,
            'status' => $result->status,
            'status_message' => $result->message,
            'provider_response' => $result->rawResponse,
        ]);

        $order->update(['status' => 'payment_pending']);

        return [
            'status' => $result->status,
            'message' => $result->message,
            'transaction_id' => $result->transactionId,
        ];
    }

    private function generateWhatsAppUrl(GrowBuilderOrder $order, ?GrowBuilderPaymentSettings $settings): string
    {
        $phone = $settings?->whatsapp_number ?? '';
        $phone = preg_replace('/[^0-9]/', '', $phone);

        $items = collect($order->items)->map(function ($item) {
            return "• {$item['name']} x{$item['quantity']} - K" . number_format($item['total'] / 100, 2);
        })->join("\n");

        $message = "🛒 *New Order: {$order->order_number}*\n\n";
        $message .= "*Customer:* {$order->customer_name}\n";
        $message .= "*Phone:* {$order->customer_phone}\n\n";
        $message .= "*Items:*\n{$items}\n\n";
        $message .= "*Total:* {$order->formatted_total}";

        if ($order->customer_address) {
            $message .= "\n\n*Delivery Address:* {$order->customer_address}";
        }

        return "https://wa.me/{$phone}?text=" . urlencode($message);
    }
}
