<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\Payment;
use App\Domain\BMS\Repositories\PaymentRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\PaymentModel;

class EloquentPaymentRepository implements PaymentRepositoryInterface
{
    public function findById(int $id): ?Payment
    {
        $model = PaymentModel::find($id);
        return $model ? Payment::reconstitute($model->toArray()) : null;
    }

    public function save(Payment $entity): Payment
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            PaymentModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = PaymentModel::create($data);
        return Payment::reconstitute($model->toArray());
    }

    public function findByCompany(int $companyId): array
    {
        return PaymentModel::where('company_id', $companyId)->get()
            ->map(fn($m) => Payment::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByCustomer(int $customerId): array
    {
        return PaymentModel::where('customer_id', $customerId)->get()
            ->map(fn($m) => Payment::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findUnallocatedByCustomer(int $customerId): array
    {
        return PaymentModel::where('customer_id', $customerId)
            ->where('is_voided', false)
            ->where('unallocated_amount', '>', 0)
            ->orderBy('payment_date', 'asc')
            ->get()
            ->map(fn($m) => Payment::reconstitute($m->toArray()))
            ->toArray();
    }

    public function getDailySummary(int $companyId, string $date): array
    {
        $payments = PaymentModel::where('company_id', $companyId)
            ->where('is_voided', false)
            ->whereDate('payment_date', $date)
            ->get();

        $summary = [
            'date' => $date,
            'total_received' => $payments->sum('amount'),
            'total_allocated' => $payments->sum(fn($p) => $p->amount - $p->unallocated_amount),
            'total_unallocated' => $payments->sum('unallocated_amount'),
            'payment_count' => $payments->count(),
            'by_method' => [],
        ];

        $byMethod = $payments->groupBy('payment_method');
        foreach ($byMethod as $method => $methodPayments) {
            $summary['by_method'][$method] = [
                'count' => $methodPayments->count(),
                'total' => $methodPayments->sum('amount'),
            ];
        }

        return $summary;
    }

    public function getCustomerPaymentSummary(int $customerId): array
    {
        $payments = PaymentModel::where('customer_id', $customerId)
            ->where('is_voided', false)
            ->get();

        return [
            'total_paid' => $payments->sum('amount'),
            'total_allocated' => $payments->sum(fn($p) => $p->amount - $p->unallocated_amount),
            'total_unallocated' => $payments->sum('unallocated_amount'),
            'payment_count' => $payments->count(),
        ];
    }
}
