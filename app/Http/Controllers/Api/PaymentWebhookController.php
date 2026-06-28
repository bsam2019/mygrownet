<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function moneyUnify(Request $request): JsonResponse
    {
        Log::info('MoneyUnify webhook received', $request->all());

        $data = $request->all();
        $transactionId = $data['transaction_id'] ?? $data['reference'] ?? null;
        $status = $data['status'] ?? null;

        if ($transactionId && $status) {
            // Process the webhook - update transaction status in your database
            // event(new PaymentStatusUpdated($transactionId, $status, 'moneyunify'));
        }

        return response()->json(['received' => true]);
    }

    public function pawapay(Request $request): JsonResponse
    {
        Log::info('PawaPay webhook received', $request->all());

        $data = $request->all();
        $transactionId = $data['depositId'] ?? $data['payoutId'] ?? null;
        $status = $data['status'] ?? null;

        if ($transactionId && $status) {
            // Process the webhook - update transaction status in your database
            // event(new PaymentStatusUpdated($transactionId, $status, 'pawapay'));
        }

        return response()->json(['received' => true]);
    }
}
