<?php

namespace App\Infrastructure\Persistence\Repositories\Investor;

use App\Domain\Investor\Entities\InvestorPaymentMethod;
use App\Domain\Investor\Repositories\InvestorPaymentMethodRepositoryInterface;
use App\Models\Investor\InvestorPaymentMethod as InvestorPaymentMethodModel;
use DateTimeImmutable;

class EloquentInvestorPaymentMethodRepository implements InvestorPaymentMethodRepositoryInterface
{
    public function save(InvestorPaymentMethod $method): InvestorPaymentMethod
    {
        $data = [
            'investor_account_id' => $method->getInvestorAccountId(),
            'method_type' => $method->getMethodType(),
            'bank_name' => $method->getBankName(),
            'account_number' => $method->getAccountNumber(),
            'account_name' => $method->getAccountName(),
            'branch_code' => $method->getBranchCode(),
            'mobile_provider' => $method->getMobileProvider(),
            'mobile_number' => $method->getMobileNumber(),
            'is_primary' => $method->isPrimary(),
            'is_verified' => $method->isVerified(),
        ];

        if ($method->getId() > 0) {
            $model = InvestorPaymentMethodModel::findOrFail($method->getId());
            $model->update($data);
        } else {
            $model = InvestorPaymentMethodModel::create($data);
        }

        return $this->toDomainEntity($model);
    }

    public function findById(int $id): ?InvestorPaymentMethod
    {
        $model = InvestorPaymentMethodModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findPrimaryByInvestor(int $investorAccountId): ?InvestorPaymentMethod
    {
        $model = InvestorPaymentMethodModel::where('investor_account_id', $investorAccountId)
            ->where('is_primary', true)
            ->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByInvestor(int $investorAccountId): array
    {
        return InvestorPaymentMethodModel::where('investor_account_id', $investorAccountId)
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->toArray();
    }

    public function setAllNonPrimary(int $investorAccountId): void
    {
        InvestorPaymentMethodModel::where('investor_account_id', $investorAccountId)
            ->update(['is_primary' => false]);
    }

    public function updateOrCreate(int $investorAccountId, string $methodType, array $data): InvestorPaymentMethod
    {
        $model = InvestorPaymentMethodModel::updateOrCreate(
            ['investor_account_id' => $investorAccountId, 'method_type' => $methodType],
            $data
        );
        return $this->toDomainEntity($model);
    }

    private function toDomainEntity(InvestorPaymentMethodModel $model): InvestorPaymentMethod
    {
        return InvestorPaymentMethod::fromPersistence(
            id: $model->id,
            investorAccountId: $model->investor_account_id,
            methodType: $model->method_type,
            bankName: $model->bank_name,
            accountNumber: $model->account_number,
            accountName: $model->account_name,
            branchCode: $model->branch_code,
            mobileProvider: $model->mobile_provider,
            mobileNumber: $model->mobile_number,
            isPrimary: $model->is_primary,
            isVerified: $model->is_verified,
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: new DateTimeImmutable($model->updated_at)
        );
    }
}
