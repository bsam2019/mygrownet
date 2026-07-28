<?php

namespace App\Domain\PlatformPayments\Services;

use App\Domain\PlatformPayments\Contracts\PaymentGateway;
use App\Domain\PlatformPayments\Entities\PaymentAttempt;
use App\Domain\PlatformPayments\Entities\PaymentTransaction;
use App\Domain\PlatformPayments\Entities\PaymentMethod;
use App\Domain\PlatformPayments\Entities\TransactionStatus;
use App\Domain\PlatformPayments\Events\PaymentAttemptFailed;
use App\Domain\PlatformPayments\Events\PaymentCompleted;
use App\Domain\PlatformPayments\Events\PaymentFailed;
use App\Domain\PlatformPayments\Events\PaymentInitiated;
use App\Domain\PlatformPayments\Events\PaymentRefunded;
use App\Domain\PlatformPayments\Exceptions\PaymentException;
use App\Domain\PlatformPayments\Repositories\AttemptRepositoryInterface;
use App\Domain\PlatformPayments\Repositories\TransactionRepositoryInterface;
use App\Domain\Core\Contracts\IntegrationEventDispatcher;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    private const MAX_RETRY_ATTEMPTS = 3;

    public function __construct(
        private readonly TransactionRepositoryInterface $transactions,
        private readonly AttemptRepositoryInterface $attempts,
        private readonly PaymentGateway $provider,
        private readonly RetryOrchestrator $retryOrchestrator,
        private readonly IntegrationEventDispatcher $events,
    ) {}

    public function initiate(
        int $organizationId,
        float $amount,
        string $currency,
        PaymentMethod $paymentMethod,
        string $provider,
        array $metadata = [],
    ): PaymentTransaction {
        $transaction = PaymentTransaction::create(
            organizationId: $organizationId,
            amount: $amount,
            currency: $currency,
            paymentMethod: $paymentMethod,
            provider: $provider,
            metadata: $metadata,
        );

        $saved = null;
        DB::transaction(function () use ($transaction, &$saved) {
            $saved = $this->transactions->save($transaction);
            $this->events->dispatch(new PaymentInitiated(
                transactionId: $saved->id(),
                organizationId: $saved->organizationId(),
                amount: $saved->amount(),
                currency: $saved->currency(),
                paymentMethod: $saved->paymentMethod()->value,
            ));
        });

        return $saved ?? $transaction;
    }

    public function process(PaymentTransaction $transaction): PaymentTransaction
    {
        $txId = $transaction->id() ?? 0;
        $attempt = PaymentAttempt::create(
            transactionId: $txId,
            attemptNumber: $transaction->attemptCount() + 1,
            scheduledAt: new \DateTimeImmutable(),
        );

        try {
            $response = $this->provider->process(
                amount: $transaction->amount(),
                currency: $transaction->currency(),
                reference: (string) $txId,
                metadata: ['organization_id' => $transaction->organizationId()],
            );

            $transaction->markCompleted(
                providerTransactionId: $response['transaction_id'],
                reference: $response['reference'] ?? null,
            );

            $attempt->markSuccess($response);

            $savedTx = null;
            DB::transaction(function () use ($transaction, $attempt, &$savedTx) {
                $savedTx = $this->transactions->save($transaction);
                $this->attempts->save($attempt);
                $this->events->dispatch(new PaymentCompleted(
                    transactionId: $savedTx->id() ?? $txId,
                    organizationId: $savedTx->organizationId(),
                    amount: $savedTx->amount(),
                    currency: $savedTx->currency(),
                    providerTransactionId: $savedTx->providerTransactionId(),
                ));
            });

            return $savedTx ?? $transaction;

        } catch (\Throwable $e) {
            $transaction->markFailed($e->getMessage());
            $attempt->markFailed($e->getMessage());

            DB::transaction(function () use ($transaction, $attempt) {
                $this->transactions->save($transaction);
                $this->attempts->save($attempt);
            });

            $this->events->dispatch(new PaymentAttemptFailed(
                transactionId: $transaction->id() ?? $txId,
                organizationId: $transaction->organizationId(),
                amount: $transaction->amount(),
                currency: $transaction->currency(),
                failureReason: $e->getMessage(),
                attemptNumber: $transaction->attemptCount(),
            ));

            if ($transaction->attemptCount() >= self::MAX_RETRY_ATTEMPTS) {
                $metadata = $transaction->metadata();
                $this->events->dispatch(new PaymentFailed(
                    transactionId: $transaction->id() ?? $txId,
                    organizationId: $transaction->organizationId(),
                    amount: $transaction->amount(),
                    currency: $transaction->currency(),
                    failureReason: $e->getMessage(),
                    attemptCount: $transaction->attemptCount(),
                    subscriptionId: $metadata['platform_subscription_id'] ?? null,
                ));
            } else {
                $this->retryOrchestrator->scheduleRetry($transaction);
            }

            return $transaction;
        }
    }

    public function refund(PaymentTransaction $transaction, float $amount): PaymentTransaction
    {
        if ($transaction->status() !== TransactionStatus::Completed) {
            throw PaymentException::processingFailed('Only completed transactions can be refunded');
        }

        $refundReference = $this->provider->refund(
            transactionId: $transaction->providerTransactionId(),
            amount: $amount,
        );

        $this->events->dispatch(new PaymentRefunded(
            transactionId: $transaction->id(),
            organizationId: $transaction->organizationId(),
            amount: $amount,
            currency: $transaction->currency(),
            refundReference: $refundReference,
        ));

        return $transaction;
    }

    public function processPendingTransactions(): int
    {
        $pending = $this->transactions->findPending();
        $processed = 0;

        foreach ($pending as $transaction) {
            $this->process($transaction);
            $processed++;
        }

        return $processed;
    }

    public function processFailedRetries(): int
    {
        $failed = $this->transactions->findFailed();
        $processed = 0;

        foreach ($failed as $transaction) {
            if ($transaction->attemptCount() < self::MAX_RETRY_ATTEMPTS) {
                $this->process($transaction);
                $processed++;
            }
        }

        return $processed;
    }
}
