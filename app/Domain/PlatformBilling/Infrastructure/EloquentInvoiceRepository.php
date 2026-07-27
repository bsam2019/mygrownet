<?php

namespace App\Domain\PlatformBilling\Infrastructure;

use App\Domain\PlatformBilling\Entities\Invoice;
use App\Domain\PlatformBilling\Repositories\InvoiceRepositoryInterface;

class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    public function findById(int $id): ?Invoice
    {
        $model = InvoiceModel::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function findBySubscription(int $subscriptionId): array
    {
        return InvoiceModel::where('subscription_id', $subscriptionId)->get()
            ->map(fn($m) => $this->toEntity($m))->all();
    }

    public function findByOrganization(int $organizationId): array
    {
        return InvoiceModel::where('organization_id', $organizationId)->get()
            ->map(fn($m) => $this->toEntity($m))->all();
    }

    public function findOverdue(): array
    {
        return InvoiceModel::whereIn('status', ['issued'])
            ->where('due_date', '<', now())->get()
            ->map(fn($m) => $this->toEntity($m))->all();
    }

    public function findDueToday(): array
    {
        return InvoiceModel::whereIn('status', ['draft', 'issued'])
            ->whereDate('due_date', today())->get()
            ->map(fn($m) => $this->toEntity($m))->all();
    }

    public function save(Invoice $invoice): Invoice
    {
        $data = $invoice->toArray();
        if ($invoice->id()) {
            InvoiceModel::where('id', $invoice->id())->update($data);
        } else {
            $model = InvoiceModel::create($data);
            $invoice = $this->toEntity($model);
        }
        return $invoice;
    }

    public function delete(int $id): void
    {
        InvoiceModel::destroy($id);
    }

    private function toEntity(InvoiceModel $model): Invoice
    {
        return Invoice::reconstitute(
            id: $model->id,
            subscriptionId: $model->subscription_id,
            organizationId: $model->organization_id,
            invoiceNumber: $model->invoice_number,
            amount: (float) $model->amount,
            currency: $model->currency,
            status: $model->status,
            issuedAt: $model->issued_at ? new \DateTimeImmutable($model->issued_at) : null,
            dueDate: new \DateTimeImmutable($model->due_date),
            paidAt: $model->paid_at ? new \DateTimeImmutable($model->paid_at) : null,
            description: $model->description,
            lineItems: $model->line_items ?? [],
            createdAt: new \DateTimeImmutable($model->created_at),
            updatedAt: new \DateTimeImmutable($model->updated_at),
        );
    }
}
