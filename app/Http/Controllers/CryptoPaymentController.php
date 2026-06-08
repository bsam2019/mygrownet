<?php

namespace App\Http\Controllers;

use App\Domain\Payment\Gateways\NOWPaymentsGateway;
use App\Domain\Payment\DTOs\CollectionRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CryptoPaymentController extends Controller
{
    public function __construct(
        private NOWPaymentsGateway $gateway
    ) {}

    /**
     * Create a cryptocurrency payment invoice
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'order_id' => 'required|string',
                'amount' => 'required|numeric|min:0.01',
                'currency' => 'required|string|size:3',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Crypto payment validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid payment data: ' . implode(', ', array_map(fn($e) => implode(', ', $e), $e->errors())),
            ], 422);
        }

        try {
            $response = $this->gateway->collect(new CollectionRequest(
                amount: $validated['amount'],
                currency: strtoupper($validated['currency']),
                reference: "ORDER-{$validated['order_id']}",
                customerEmail: $request->user()?->email,
                description: "Order #{$validated['order_id']} - MyGrowNet"
            ));

            if ($response->success) {
                Log::info('Crypto payment invoice created', [
                    'order_id' => $validated['order_id'],
                    'transaction_id' => $response->transactionId,
                    'amount' => $validated['amount'],
                    'currency' => $validated['currency'],
                ]);

                return response()->json([
                    'success' => true,
                    'transaction_id' => $response->transactionId,
                    'invoice_url' => $response->rawResponse['invoice_url'] ?? null,
                    'pay_address' => $response->rawResponse['pay_address'] ?? null,
                    'pay_amount' => $response->rawResponse['pay_amount'] ?? null,
                    'pay_currency' => $response->rawResponse['pay_currency'] ?? null,
                    'message' => 'Payment invoice created successfully',
                ]);
            }

            Log::warning('Crypto payment creation failed', [
                'order_id' => $validated['order_id'],
                'response' => $response,
            ]);

            return response()->json([
                'success' => false,
                'message' => $response->message ?? 'Failed to create payment invoice',
            ], 400);

        } catch (\Exception $e) {
            Log::error('Crypto payment creation exception', [
                'order_id' => $validated['order_id'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create cryptocurrency payment: ' . $e->getMessage(),
                'debug' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * Check payment status
     */
    public function status(Request $request, string $transactionId): JsonResponse
    {
        try {
            $status = $this->gateway->checkCollectionStatus($transactionId);

            return response()->json([
                'success' => true,
                'transaction_id' => $transactionId,
                'status' => $status->value,
            ]);

        } catch (\Exception $e) {
            Log::error('Crypto payment status check failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check payment status',
            ], 500);
        }
    }
}
