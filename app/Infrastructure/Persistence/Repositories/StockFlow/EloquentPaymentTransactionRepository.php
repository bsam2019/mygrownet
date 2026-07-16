<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\PaymentTransaction;
use App\Domain\StockFlow\Repositories\PaymentTransactionRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\PaymentTransactionId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaPaymentTransactionModel;
use DateTimeImmutable;

class EloquentPaymentTransactionRepository implements PaymentTransactionRepositoryInterface
{
    public function findById(PaymentTransactionId $id): ?PaymentTransaction
    {
        $model = SaPaymentTransactionModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId): array
    {
        return SaPaymentTransactionModel::where('sa_company_id', $companyId->toInt())->orderByDesc('created_at')->get()->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findByPayable(string $payableType, int $payableId): array
    {
        return SaPaymentTransactionModel::where('payable_type', $payableType)->where('payable_id', $payableId)->orderByDesc('created_at')->get()->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function save(PaymentTransaction $transaction): PaymentTransaction
    {
        $data = $transaction->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at']);
        if ($transaction->id() === 0) {
            $model = SaPaymentTransactionModel::create($data);
        } else {
            $model = SaPaymentTransactionModel::find($transaction->id());
            $model->update($data);
        }
        return $this->toDomainEntity($model->fresh());
    }

    private function toDomainEntity(SaPaymentTransactionModel $model): PaymentTransaction
    {
        return PaymentTransaction::reconstitute(
            id: PaymentTransactionId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            payableType: $model->payable_type,
            payableId: $model->payable_id,
            gateway: $model->gateway,
            transactionId: $model->transaction_id,
            amount: (float) $model->amount,
            currency: $model->currency,
            status: $model->status,
            gatewayResponse: $model->gateway_response ? (array) $model->gateway_response : null,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
