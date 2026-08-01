<?php

namespace App\Domain\PlatformPayments\Services;

use App\Domain\PlatformPayments\Contracts\PaymentGatewayInterface;
use App\Domain\PlatformPayments\DTOs\PaymentRequest;
use App\Domain\PlatformPayments\DTOs\RefundRequest;
use App\Domain\PlatformPayments\Entities\PaymentMethod;
use App\Domain\PlatformPayments\Entities\PaymentTransaction;
use App\Domain\PlatformPayments\Entities\TransactionStatus;
use App\Domain\PlatformPayments\Enums\GatewayProvider;
use App\Domain\PlatformPayments\Events\PaymentInitiated;
use App\Domain\PlatformPayments\Events\PaymentRefunded;
use App\Domain\PlatformPayments\Exceptions\PaymentException;
use App\Domain\PlatformPayments\Repositories\TransactionRepositoryInterface;
use App\Domain\Core\Contracts\IntegrationEventDispatcher;

/**
 * Shared, module-agnostic payment facade used by every module's checkout flow.
 *
 * Wires the PlatformPayments gateway adapters (PawaPay and friends) through
 * PaymentGatewayFactory, persists a PaymentTransaction per payment, and emits
 * the standard PlatformPayments domain events.
 */
class SharedPaymentService
{
    public function __construct(
        private readonly TransactionRepositoryInterface $transactions,
        private readonly IntegrationEventDispatcher $events,
    ) {}

    /**
     * Initiate a payment collection.
     *
     * @return array{transaction: PaymentTransaction, response: \App\Domain\PlatformPayments\DTOs\PaymentResponse}
     */
    public function initiate(
        int $organizationId,
        float $amount,
        string $currency,
        string $phoneNumber,
        string $gateway = 'pawapay',
        ?string $description = null,
        ?string $reference = null,
        array $metadata = [],
    ): array {
        $provider = GatewayProvider::tryFrom($gateway) ?? GatewayProvider::PAWAPAY;
        $instance = $this->gateway($provider);

        $transaction = PaymentTransaction::create(
            organizationId: $organizationId,
            amount: $amount,
            currency: strtoupper($currency),
            paymentMethod: $this->paymentMethodFor($phoneNumber),
            provider: $provider->value,
            metadata: array_merge($metadata, [
                'phone_number' => $phoneNumber,
                'description' => $description,
            ]),
        );

        $saved = $this->transactions->save($transaction);

        $request = new PaymentRequest(
            amount: (string) $saved->amount(),
            currency: $saved->currency(),
            phoneNumber: $phoneNumber,
            reference: $reference ?? (string) $saved->id(),
            description: $description ?? 'Payment via ' . $provider->getLabel(),
            metadata: $metadata,
        );

        $response = $instance->initiatePayment($request);

        if ($response->success) {
            $saved->markPending(
                providerTransactionId: $response->externalReference ?? $request->reference,
                reference: $response->transactionReference,
            );
        } else {
            $saved->markFailed($response->message ?? 'Payment initiation failed');
        }

        $saved = $this->transactions->save($saved);

        $this->events->dispatch(new PaymentInitiated(
            transactionId: $saved->id() ?? 0,
            organizationId: $saved->organizationId(),
            amount: $saved->amount(),
            currency: $saved->currency(),
            paymentMethod: $saved->paymentMethod()->value,
        ));

        return [
            'transaction' => $saved,
            'response' => $response,
        ];
    }

    /**
     * Look up a transaction's current status (webhook-driven).
     */
    public function status(string $reference): ?PaymentTransaction
    {
        return $this->transactions->findByReference($reference);
    }

    /**
     * Refund a completed transaction.
     */
    public function refund(
        string $reference,
        float $amount,
        string $reason = 'Customer requested refund',
    ): PaymentTransaction {
        $transaction = $this->transactions->findByReference($reference);

        if (!$transaction) {
            throw PaymentException::transactionNotFound(0);
        }

        if ($transaction->status() !== TransactionStatus::Completed) {
            throw PaymentException::processingFailed('Only completed transactions can be refunded');
        }

        $provider = GatewayProvider::tryFrom($transaction->provider()) ?? GatewayProvider::PAWAPAY;
        $instance = $this->gateway($provider);

        $response = $instance->refundPayment(new RefundRequest(
            transactionReference: $transaction->providerTransactionId() ?? $reference,
            amount: (string) $amount,
            reason: $reason,
        ));

        if (!$response->success) {
            throw PaymentException::processingFailed($response->message ?? 'Refund failed');
        }

        $transaction->markRefunded($response->refundReference ?: $reference);
        $saved = $this->transactions->save($transaction);

        $this->events->dispatch(new PaymentRefunded(
            transactionId: $saved->id() ?? 0,
            organizationId: $saved->organizationId(),
            amount: $amount,
            currency: $saved->currency(),
            refundReference: $response->refundReference,
        ));

        return $saved;
    }

    /**
     * List all configured gateway options for the checkout UI.
     */
    public function availableGateways(): array
    {
        return PaymentGatewayFactory::getAvailableGateways();
    }

    public function getRequiredFields(string $gateway): array
    {
        $provider = GatewayProvider::tryFrom($gateway) ?? GatewayProvider::PAWAPAY;
        return PaymentGatewayFactory::getGatewayFields($provider);
    }

    private function gateway(GatewayProvider $provider): PaymentGatewayInterface
    {
        $credentials = match ($provider) {
            GatewayProvider::PAWAPAY => [
                'api_token' => config('services.pawapay.api_token'),
                'webhook_secret' => config('services.pawapay.webhook_secret'),
            ],
            GatewayProvider::MONEY_UNIFY => [
                'muid' => config('services.moneyunify.muid'),
                'webhook_secret' => config('services.moneyunify.webhook_secret'),
            ],
            default => [],
        };

        return PaymentGatewayFactory::create(
            gateway: $provider,
            credentials: $credentials,
            testMode: config('services.pawapay.base_url') === 'https://api.sandbox.pawapay.io',
        );
    }

    private function paymentMethodFor(string $phoneNumber): PaymentMethod
    {
        $clean = preg_replace('/[^0-9]/', '', $phoneNumber);

        if (str_starts_with($clean, '260')) {
            $clean = substr($clean, 3);
        }

        if (preg_match('/^(096|076|077)/', $clean)) {
            return PaymentMethod::MTNMoMo;
        }

        if (preg_match('/^(097|075)/', $clean)) {
            return PaymentMethod::AirtelMoney;
        }

        return PaymentMethod::Wallet;
    }
}
