<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use App\Domain\GrowBuilder\Payment\Services\GrowBuilderPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function __construct(
        private GrowBuilderPaymentService $paymentService
    ) {}

    /**
     * Handle payment webhook
     */
    public function handle(Request $request, int $siteId)
    {
        try {
            Log::info('Payment webhook received', [
                'site_id' => $siteId,
                'payload' => $request->all(),
            ]);

            $success = $this->paymentService->handleWebhook($siteId, $request->all());

            if ($success) {
                return response()->json(['status' => 'success'], 200);
            }

            return response()->json(['status' => 'failed'], 400);

        } catch (\Exception $e) {
            Log::error('Webhook processing error', [
                'site_id' => $siteId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }
}
