<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\Invoice;
use App\Domain\GrowFinance\Repositories\InvoiceRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceInvoiceModel;

class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    public function findById(int $id): ?Invoice
    {
        $model = GrowFinanceInvoiceModel::find($id);
        return $model ? Invoice::reconstitute($model->toArray()) : null;
    }

    public function save(Invoice $entity): Invoice
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceInvoiceModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceInvoiceModel::create($data);
        return Invoice::reconstitute($model->toArray());
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinanceInvoiceModel::forBusiness($businessId)->get()->map(fn($m) => Invoice::reconstitute($m->toArray()))->toArray();
    }

    public function findByCustomer(int $customerId): array
    {
        return GrowFinanceInvoiceModel::where('customer_id', $customerId)->get()->map(fn($m) => Invoice::reconstitute($m->toArray()))->toArray();
    }

    public function findByStatus(int $businessId, string $status): array
    {
        return GrowFinanceInvoiceModel::forBusiness($businessId)->where('status', $status)->get()->map(fn($m) => Invoice::reconstitute($m->toArray()))->toArray();
    }

    public function findOverdue(int $businessId): array
    {
        return GrowFinanceInvoiceModel::forBusiness($businessId)->where('due_date', '<', now()->toDateString())->whereNotIn('status', ['paid', 'cancelled'])->get()->map(fn($m) => Invoice::reconstitute($m->toArray()))->toArray();
    }

    public function findByNumber(int $businessId, string $number): ?Invoice
    {
        $_ = GrowFinanceInvoiceModel::forBusiness($businessId)->where('invoice_number', $number)->first(); return $_ ? Invoice::reconstitute($_->toArray()) : null;
    }

}
