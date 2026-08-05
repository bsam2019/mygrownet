<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\PlatformPayments\Services\SharedPaymentService;
use App\Http\Controllers\Controller;
use App\Models\ModuleDiscount;
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

    public function validateDiscount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'module_id' => 'required|string|max:50',
            'tier' => 'nullable|string|max:50',
            'amount' => 'required|numeric|min:0',
            'billing_cycle' => 'nullable|string',
        ]);

        $discount = ModuleDiscount::where('code', $validated['code'])
            ->valid()
            ->where(function ($q) use ($validated) {
                $q->whereNull('module_id')->orWhere('module_id', $validated['module_id']);
            })
            ->first();

        if (! $discount) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid or expired promo code.',
            ]);
        }

        // Tier restriction
        if ($discount->applies_to === 'specific_tiers' && ! empty($discount->tier_keys)) {
            if (! in_array($validated['tier'] ?? '', $discount->tier_keys, true)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'This code is not valid for the selected tier.',
                ]);
            }
        }

        // Annual-only code on monthly billing
        if ($discount->applies_to === 'annual_only' && ($validated['billing_cycle'] ?? 'monthly') !== 'annual') {
            return response()->json([
                'valid' => false,
                'message' => 'This code is only valid for annual billing.',
            ]);
        }

        // Minimum purchase
        if ($discount->min_purchase_amount && $validated['amount'] < $discount->min_purchase_amount) {
            return response()->json([
                'valid' => false,
                'message' => 'Minimum purchase of K' . $discount->min_purchase_amount . ' required.',
            ]);
        }

        // Usage exhausted
        if ($discount->max_uses && $discount->current_uses >= $discount->max_uses) {
            return response()->json([
                'valid' => false,
                'message' => 'This code has been fully redeemed.',
            ]);
        }

        $original = (float) $validated['amount'];

        if ($discount->discount_type === 'percentage') {
            $discounted = round($original * (1 - $discount->discount_value / 100), 2);
        } else {
            $discounted = round(max(0, $original - $discount->discount_value), 2);
        }

        return response()->json([
            'valid' => true,
            'discount' => [
                'id' => $discount->id,
                'code' => $discount->code,
                'type' => $discount->discount_type,
                'value' => $discount->discount_value,
                'name' => $discount->name,
            ],
            'original_amount' => $original,
            'discounted_amount' => round($discounted, 2),
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
