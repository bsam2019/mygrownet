<?php

namespace App\Domain\PlatformPayments\Infrastructure;

use App\Domain\PlatformPayments\Entities\PaymentAttempt;
use App\Domain\PlatformPayments\Repositories\AttemptRepositoryInterface;

class EloquentAttemptRepository implements AttemptRepositoryInterface
{
    public function findByTransaction(int $transactionId): array
    {
        return PaymentAttemptModel::where('transaction_id', $transactionId)
            ->orderBy('attempt_number', 'asc')
            ->get()
            ->map(fn(PaymentAttemptModel $m) => $this->toDomain($m))
            ->all();
    }

    public function findLastAttempt(int $transactionId): ?PaymentAttempt
    {
        $model = PaymentAttemptModel::where('transaction_id', $transactionId)
            ->orderBy('attempt_number', 'desc')
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function save(PaymentAttempt $attempt): PaymentAttempt
    {
        $data = $attempt->toArray();

        $model = $attempt->id()
            ? PaymentAttemptModel::findOrFail($attempt->id())
            : new PaymentAttemptModel();

        $model->transaction_id = $attempt->transactionId();
        $model->attempt_number = $attempt->attemptNumber();
        $model->status = $data['status'] ?? 'pending';
        $model->error_message = $data['error_message'] ?? null;
        $model->save();

        return $this->toDomain($model);
    }

    private function toDomain(PaymentAttemptModel $model): PaymentAttempt
    {
        return PaymentAttempt::reconstitute(
            id: $model->id,
            transactionId: $model->transaction_id,
            attemptNumber: $model->attempt_number,
            status: $model->status,
            providerResponse: $model->provider_response ? json_encode($model->provider_response) : null,
            errorMessage: $model->error_message,
            scheduledAt: $model->scheduled_at ? new \DateTimeImmutable($model->scheduled_at) : null,
            attemptedAt: new \DateTimeImmutable($model->attempted_at),
            createdAt: new \DateTimeImmutable($model->created_at),
        );
    }
}
