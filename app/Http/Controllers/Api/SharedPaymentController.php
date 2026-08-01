<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\PlatformPayments\Services\SharedPaymentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Shared, module-agnostic payment API consumed by every module's checkout page.
 */
class SharedPaymentController extends Controller
{
    public function __construct(
        private readonly SharedPaymentService $payments,
    ) {}

    public function gateways(): JsonResponse
    {
        return response()->json([
            'gateways' => $this->payments->availableGateways(),
            'default' => 'pawapay',
        ]);
    }

    public function initiate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone_number' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string|size:3',
            'gateway' => 'nullable|string',
            'description' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:64',
            'metadata' => 'nullable|array',
            'organization_id' => 'nullable|integer',
        ]);

        $organizationId = (int) ($validated['organization_id'] ?? 0);

        $result = $this->payments->initiate(
            organizationId: $organizationId,
            amount: (float) $validated['amount'],
            currency: strtoupper($validated['currency']),
            phoneNumber: $validated['phone_number'],
            gateway: $validated['gateway'] ?? 'pawapay',
            description: $validated['description'] ?? null,
            reference: $validated['reference'] ?? null,
            metadata: $validated['metadata'] ?? [],
        );

        return response()->json([
            'success' => true,
            'transaction' => [
                'id' => $result['transaction']->id(),
                'reference' => $result['transaction']->providerReference() ?? ($validated['reference'] ?? null),
                'status' => $result['transaction']->status()->value,
                'amount' => $result['transaction']->amount(),
                'currency' => $result['transaction']->currency(),
            ],
            'gateway' => [
                'message' => $result['response']->message,
                'external_reference' => $result['response']->externalReference,
                'checkout_url' => $result['response']->checkoutUrl,
            ],
        ]);
    }

    public function status(Request $request, string $reference): JsonResponse
    {
        $transaction = $this->payments->status($reference);

        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        return response()->json([
            'transaction' => [
                'id' => $transaction->id(),
                'reference' => $transaction->providerReference() ?? $reference,
                'status' => $transaction->status()->value,
                'amount' => $transaction->amount(),
                'currency' => $transaction->currency(),
            ],
        ]);
    }

    public function refund(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reference' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            $transaction = $this->payments->refund(
                reference: $validated['reference'],
                amount: (float) $validated['amount'],
                reason: $validated['reason'] ?? 'Customer requested refund',
            );
        } catch (\App\Domain\PlatformPayments\Exceptions\PaymentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'transaction' => [
                'id' => $transaction->id(),
                'reference' => $transaction->providerReference(),
                'status' => $transaction->status()->value,
            ],
        ]);
    }

    public function fields(Request $request, string $gateway): JsonResponse
    {
        return response()->json([
            'gateway' => $gateway,
            'fields' => $this->payments->getRequiredFields($gateway),
        ]);
    }
}
