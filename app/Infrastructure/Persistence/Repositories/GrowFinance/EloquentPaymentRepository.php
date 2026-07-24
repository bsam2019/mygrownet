<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\Payment;
use App\Domain\GrowFinance\Repositories\PaymentRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinancePaymentModel;

class EloquentPaymentRepository implements PaymentRepositoryInterface
{
    public function findById(int $id): ?Payment
    {
        $model = GrowFinancePaymentModel::find($id);
        return $model ? Payment::reconstitute($model->toArray()) : null;
    }

    public function save(Payment $entity): Payment
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinancePaymentModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinancePaymentModel::create($data);
        return Payment::reconstitute($model->toArray());
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinancePaymentModel::forBusiness($businessId)->get()->map(fn($m) => Payment::reconstitute($m->toArray()))->toArray();
    }

    public function findByPayable(string $payableType, int $payableId): array
    {
        return GrowFinancePaymentModel::where('payable_type', $payableType)->where('payable_id', $payableId)->get()->map(fn($m) => Payment::reconstitute($m->toArray()))->toArray();
    }

    public function findInDateRange(int $businessId, string $startDate, string $endDate): array
    {
        return GrowFinancePaymentModel::forBusiness($businessId)->inDateRange($startDate, $endDate)->get()->map(fn($m) => Payment::reconstitute($m->toArray()))->toArray();
    }

}
