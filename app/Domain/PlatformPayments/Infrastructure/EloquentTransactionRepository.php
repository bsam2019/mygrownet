<?php

namespace App\Domain\PlatformPayments\Infrastructure;

use App\Domain\PlatformPayments\Entities\PaymentTransaction;
use App\Domain\PlatformPayments\Entities\PaymentMethod;
use App\Domain\PlatformPayments\Entities\TransactionStatus;
use App\Domain\PlatformPayments\Repositories\TransactionRepositoryInterface;

class EloquentTransactionRepository implements TransactionRepositoryInterface
{
    public function findById(int $id): ?PaymentTransaction
    {
        $model = PaymentTransactionModel::find($id);
        return $model ? $this->toDomain($model) : null;
    }

    public function findByOrganization(int $organizationId): array
    {
        return PaymentTransactionModel::where('organization_id', $organizationId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn(PaymentTransactionModel $m) => $this->toDomain($m))
            ->all();
    }

    public function findByProviderTransactionId(string $providerTransactionId): ?PaymentTransaction
    {
        $model = PaymentTransactionModel::where('provider_transaction_id', $providerTransactionId)->first();
        return $model ? $this->toDomain($model) : null;
    }

    public function findPending(): array
    {
        return PaymentTransactionModel::whereIn('status', ['initiated', 'pending'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn(PaymentTransactionModel $m) => $this->toDomain($m))
            ->all();
    }

    public function findFailed(): array
    {
        return PaymentTransactionModel::where('status', 'failed')
            ->orderBy('updated_at', 'asc')
            ->get()
            ->map(fn(PaymentTransactionModel $m) => $this->toDomain($m))
            ->all();
    }

    public function findSettled(): array
    {
        return PaymentTransactionModel::where('status', 'settled')
            ->orderBy('settled_at', 'desc')
            ->get()
            ->map(fn(PaymentTransactionModel $m) => $this->toDomain($m))
            ->all();
    }

    public function save(PaymentTransaction $transaction): PaymentTransaction
    {
        $data = $transaction->toArray();

        $model = $transaction->id()
            ? PaymentTransactionModel::findOrFail($transaction->id())
            : new PaymentTransactionModel();

        $model->organization_id = $transaction->organizationId();
        $model->amount = $transaction->amount();
        $model->currency = $data['currency'] ?? 'USD';
        $model->payment_method = $transaction->paymentMethod()->value;
        $model->status = $transaction->status()->value;
        $model->provider = $transaction->provider();
        $model->attempt_count = $transaction->attemptCount();
        $model->failure_reason = $transaction->failureReason();
        $model->provider_transaction_id = $transaction->providerTransactionId();
        $model->provider_reference = $data['provider_reference'] ?? null;
        $model->fee = $data['fee'] ?? null;
        $model->settled_amount = $data['settled_amount'] ?? null;
        $model->settled_at = $data['settled_at'] ?? null;
        $model->metadata = $transaction->id() ? $model->metadata : [];
        $model->save();

        return $this->toDomain($model);
    }

    private function toDomain(PaymentTransactionModel $model): PaymentTransaction
    {
        return PaymentTransaction::reconstitute(
            id: $model->id,
            organizationId: $model->organization_id,
            amount: (float) $model->amount,
            currency: $model->currency,
            paymentMethod: $model->payment_method,
            status: $model->status,
            providerTransactionId: $model->provider_transaction_id,
            providerReference: $model->provider_reference,
            provider: $model->provider,
            fee: $model->fee ? (float) $model->fee : null,
            settledAmount: $model->settled_amount ? (float) $model->settled_amount : null,
            settledAt: $model->settled_at ? new \DateTimeImmutable($model->settled_at) : null,
            metadata: $model->metadata ?? [],
            failureReason: $model->failure_reason,
            attemptCount: $model->attempt_count,
            createdAt: new \DateTimeImmutable($model->created_at),
            updatedAt: new \DateTimeImmutable($model->updated_at),
        );
    }
}
