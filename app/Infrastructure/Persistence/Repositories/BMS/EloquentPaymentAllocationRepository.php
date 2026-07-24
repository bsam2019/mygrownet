<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\PaymentAllocation;
use App\Domain\BMS\Repositories\PaymentAllocationRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\PaymentAllocationModel;

class EloquentPaymentAllocationRepository implements PaymentAllocationRepositoryInterface
{
    public function findById(int $id): ?PaymentAllocation
    {
        $model = PaymentAllocationModel::find($id);
        return $model ? PaymentAllocation::reconstitute($model->toArray()) : null;
    }

    public function save(PaymentAllocation $entity): PaymentAllocation
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            PaymentAllocationModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = PaymentAllocationModel::create($data);
        return PaymentAllocation::reconstitute($model->toArray());
    }

    public function findByPayment(int $paymentId): array
    {
        return PaymentAllocationModel::where('payment_id', $paymentId)->get()
            ->map(fn($m) => PaymentAllocation::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByInvoice(int $invoiceId): array
    {
        return PaymentAllocationModel::where('invoice_id', $invoiceId)->get()
            ->map(fn($m) => PaymentAllocation::reconstitute($m->toArray()))
            ->toArray();
    }

    public function delete(int $id): void
    {
        PaymentAllocationModel::where('id', $id)->delete();
    }
}
