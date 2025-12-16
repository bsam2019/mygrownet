<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Payment\DTOs\CollectionRequest;
use App\Domain\Payment\DTOs\DisbursementRequest;
use App\Domain\Payment\Services\PaymentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService)
    {
    }

    public function gateways(): JsonResponse
    {
        return response()->json([
            'gateways' => $this->paymentService->getAvailableGateways(),
            'default' => $this->paymentService->getDefaultGateway(),
        ]);
    }

    public function collect(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone_number' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string|size:3',
            'provider' => 'required|string',
            'reference' => 'nullable|string',
            'description' => 'nullable|string',
            'gateway' => 'nullable|string',
        ]);

        $collectionRequest = new CollectionRequest(
            phoneNumber: $validated['phone_number'],
            amount: (float) $validated['amount'],
            currency: strtoupper($validated['currency']),
            provider: $validated['provider'],
            reference: $validated['reference'] ?? null,
            description: $validated['description'] ?? null,
        );

        $response = $this->paymentService->collect(
            $collectionRequest,
            $validated['gateway'] ?? null
        );

        return response()->json([
            'success' => $response->success,
            'transaction_id' => $response->transactionId,
            'provider_reference' => $response->providerReference,
            'status' => $response->status->value,
            'message' => $response->message,
        ], $response->success ? 200 : 400);
    }

    public function disburse(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone_number' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string|size:3',
            'provider' => 'required|string',
            'reference' => 'nullable|string',
            'description' => 'nullable|string',
            'gateway' => 'nullable|string',
        ]);

        $disbursementRequest = new DisbursementRequest(
            phoneNumber: $validated['phone_number'],
            amount: (float) $validated['amount'],
            currency: strtoupper($validated['currency']),
            provider: $validated['provider'],
            reference: $validated['reference'] ?? null,
            description: $validated['description'] ?? null,
        );

        $response = $this->paymentService->disburse(
            $disbursementRequest,
            $validated['gateway'] ?? null
        );

        return response()->json([
            'success' => $response->success,
            'transaction_id' => $response->transactionId,
            'provider_reference' => $response->providerReference,
            'status' => $response->status->value,
            'message' => $response->message,
        ], $response->success ? 200 : 400);
    }

    public function status(Request $request, string $transactionId): JsonResponse
    {
        $type = $request->query('type', 'collection');
        $gateway = $request->query('gateway');

        $status = $type === 'disbursement'
            ? $this->paymentService->checkDisbursementStatus($transactionId, $gateway)
            : $this->paymentService->checkCollectionStatus($transactionId, $gateway);

        return response()->json([
            'transaction_id' => $transactionId,
            'status' => $status->value,
        ]);
    }
}
