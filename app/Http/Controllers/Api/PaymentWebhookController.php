<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Core\Contracts\IntegrationEventDispatcher;
use App\Domain\PlatformPayments\Enums\PaymentStatus;
use App\Domain\PlatformPayments\Gateways\PawapayGateway;
use App\Domain\PlatformPayments\Repositories\TransactionRepositoryInterface;
use App\Domain\PlatformPayments\Events\PaymentCompleted;
use App\Domain\PlatformPayments\Events\PaymentFailed;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    private ?PawapayGateway $pawapayGateway = null;

    public function __construct(
        private readonly TransactionRepositoryInterface $transactions,
        private readonly IntegrationEventDispatcher $events,
    ) {}

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
        $payload = $request->getContent();
        $gateway = $this->getPawapayGateway();

        // RFC-9421 HTTP Message Signatures (V2). PawaPay sends these headers only
        // when "signed callbacks" are enabled on the account.
        $signature = $request->header('Signature', '');
        $signatureInput = $request->header('Signature-Input', '');
        $signatureDate = $request->header('Signature-Date', '');
        $contentDigest = $request->header('Content-Digest', '');

        $verified = $gateway->verifyWebhookSignature(
            payload: $payload,
            signatureHeader: $signature,
            signatureInputHeader: $signatureInput,
            signatureDate: $signatureDate,
            contentDigest: $contentDigest,
        );

        if (!$verified) {
            Log::warning('PawaPay webhook signature verification failed', [
                'ip' => $request->ip(),
                'has_signature' => !empty($signature),
                'has_signature_input' => !empty($signatureInput),
            ]);

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $data = $request->json()->all();
        $transactionId = $data['depositId'] ?? $data['payoutId'] ?? $data['refundId'] ?? null;
        $status = $data['status'] ?? null;

        if (!$transactionId || !$status) {
            Log::warning('PawaPay webhook missing required fields', [
                'has_deposit_id' => isset($data['depositId']),
                'has_payout_id' => isset($data['payoutId']),
                'has_status' => isset($data['status']),
            ]);

            return response()->json(['received' => true, 'error' => 'Missing fields'], 422);
        }

        Log::info('PawaPay webhook received', [
            'transaction_id' => $transactionId,
            'status' => $status,
        ]);

        // Record webhook heartbeat for ops monitoring (growstream:check-ops).
        \Illuminate\Support\Facades\Cache::put(
            'ops.pawapay.last_webhook_at',
            now()->toIso8601String(),
            now()->addDays(1)
        );

        $this->processPawaPayWebhook($transactionId, $status, $data);

        return response()->json(['received' => true]);
    }

    private function getPawapayGateway(): PawapayGateway
    {
        return $this->pawapayGateway ??= new PawapayGateway(
            credentials: [
                'api_token' => config('services.pawapay.api_token'),
                'webhook_secret' => config('services.pawapay.webhook_secret'),
            ],
            testMode: config('services.pawapay.base_url') === 'https://api.sandbox.pawapay.io',
        );
    }

    private function processPawaPayWebhook(string $transactionId, string $status, array $data): void
    {
        $transaction = $this->transactions->findByReference($transactionId);

        if (!$transaction) {
            Log::warning('PawaPay webhook: transaction not found', [
                'transaction_id' => $transactionId,
                'status' => $status,
            ]);
            return;
        }

        $paymentStatus = $this->mapToPaymentStatus($status);
        $providerTransactionId = $data['depositId'] ?? $data['payoutId'] ?? $data['refundId'] ?? null;

        if ($paymentStatus === PaymentStatus::COMPLETED) {
            $transaction->markCompleted($providerTransactionId, $transaction->providerReference());
            $this->transactions->save($transaction);

            $this->events->dispatch(new PaymentCompleted(
                transactionId: $transaction->id() ?? 0,
                organizationId: $transaction->organizationId(),
                amount: $transaction->amount(),
                currency: $transaction->currency(),
                providerTransactionId: $providerTransactionId,
            ));

            return;
        }

        if (in_array($paymentStatus, [PaymentStatus::FAILED, PaymentStatus::CANCELLED, PaymentStatus::EXPIRED], true)) {
            $transaction->markFailed($status);
            $this->transactions->save($transaction);

            $this->events->dispatch(new PaymentFailed(
                transactionId: $transaction->id() ?? 0,
                organizationId: $transaction->organizationId(),
                amount: $transaction->amount(),
                currency: $transaction->currency(),
                failureReason: $status,
                attemptCount: $transaction->attemptCount(),
            ));
        }
    }

    private function mapToPaymentStatus(string $status): PaymentStatus
    {
        return $this->getPawapayGateway()->mapStatus($status);
    }
}
