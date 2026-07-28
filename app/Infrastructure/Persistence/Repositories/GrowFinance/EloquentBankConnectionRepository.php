<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\BankConnection;
use App\Domain\GrowFinance\Repositories\BankConnectionRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceBankConnectionModel;

class EloquentBankConnectionRepository implements BankConnectionRepositoryInterface
{
    public function findById(int $id): ?BankConnection
    {
        $model = GrowFinanceBankConnectionModel::find($id);
        return $model ? BankConnection::reconstitute($model->toArray()) : null;
    }

    public function save(BankConnection $entity): BankConnection
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at'], $data['credentials']);

        $credentials = $entity->credentials ? json_encode($entity->credentials) : null;

        if ($id) {
            GrowFinanceBankConnectionModel::where('id', $id)->update($data + ($credentials ? ['credentials' => $credentials] : []));
            return $this->findById($id);
        }

        $model = GrowFinanceBankConnectionModel::create($data + ($credentials ? ['credentials' => $credentials] : []));
        return BankConnection::reconstitute($model->toArray());
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinanceBankConnectionModel::where('business_id', $businessId)
            ->orderBy('bank_name')
            ->get()
            ->map(fn($m) => BankConnection::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findActiveByBusiness(int $businessId): array
    {
        return GrowFinanceBankConnectionModel::where('business_id', $businessId)
            ->where('status', 'active')
            ->orderBy('bank_name')
            ->get()
            ->map(fn($m) => BankConnection::reconstitute($m->toArray()))
            ->toArray();
    }

    public function delete(int $id): void
    {
        GrowFinanceBankConnectionModel::where('id', $id)->delete();
    }
}
