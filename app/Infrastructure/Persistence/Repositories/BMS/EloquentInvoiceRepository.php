<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\Invoice;
use App\Domain\BMS\Repositories\InvoiceRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\InvoiceModel;
use App\Domain\BMS\Core\ValueObjects\InvoiceStatus;

class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    public function findById(int $id): ?Invoice
    {
        $model = InvoiceModel::find($id);
        return $model ? Invoice::reconstitute($model->toArray()) : null;
    }

    public function save(Invoice $entity): Invoice
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['balance_due'], $data['created_at'], $data['updated_at']);

        if ($id) {
            InvoiceModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = InvoiceModel::create($data);
        return Invoice::reconstitute($model->toArray());
    }

    public function findByCompany(int $companyId): array
    {
        return InvoiceModel::where('company_id', $companyId)->get()
            ->map(fn($m) => Invoice::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByCustomer(int $customerId): array
    {
        return InvoiceModel::where('customer_id', $customerId)->get()
            ->map(fn($m) => Invoice::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByStatus(int $companyId, string $status): array
    {
        return InvoiceModel::where('company_id', $companyId)->where('status', $status)->get()
            ->map(fn($m) => Invoice::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findOverdue(int $companyId): array
    {
        return InvoiceModel::where('company_id', $companyId)
            ->where('due_date', '<', now()->toDateString())
            ->whereNotIn('status', [InvoiceStatus::PAID->value, InvoiceStatus::CANCELLED->value, InvoiceStatus::VOID->value])
            ->get()
            ->map(fn($m) => Invoice::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByNumber(int $companyId, string $number): ?Invoice
    {
        $model = InvoiceModel::where('company_id', $companyId)->where('invoice_number', $number)->first();
        return $model ? Invoice::reconstitute($model->toArray()) : null;
    }

    public function getSummary(int $companyId): array
    {
        $invoices = InvoiceModel::where('company_id', $companyId)->get();

        return [
            'total_invoices' => $invoices->count(),
            'draft_count' => $invoices->where('status', InvoiceStatus::DRAFT->value)->count(),
            'sent_count' => $invoices->where('status', InvoiceStatus::SENT->value)->count(),
            'partial_count' => $invoices->where('status', InvoiceStatus::PARTIAL->value)->count(),
            'paid_count' => $invoices->where('status', InvoiceStatus::PAID->value)->count(),
            'total_value' => $invoices->sum('total_amount'),
            'total_paid' => $invoices->sum('amount_paid'),
            'total_outstanding' => $invoices->sum(fn($inv) => $inv->total_amount - $inv->amount_paid),
        ];
    }

    public function findPendingByCompany(int $companyId): array
    {
        return InvoiceModel::where('company_id', $companyId)
            ->whereIn('status', [InvoiceStatus::SENT->value, InvoiceStatus::PARTIAL->value])
            ->get()
            ->map(fn($m) => Invoice::reconstitute($m->toArray()))
            ->toArray();
    }
}
