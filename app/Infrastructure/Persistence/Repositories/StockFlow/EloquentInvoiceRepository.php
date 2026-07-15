<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\Invoice;
use App\Domain\StockFlow\Entities\InvoiceItem;
use App\Domain\StockFlow\Repositories\InvoiceRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\InvoiceId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaInvoiceModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaInvoiceItemModel;
use DateTimeImmutable;

class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    public function findById(InvoiceId $id): ?Invoice
    {
        $model = SaInvoiceModel::with('items')->find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId, int $perPage = 50): array
    {
        return SaInvoiceModel::where('sa_company_id', $companyId->toInt())
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findByCompanyIdAndDateBetween(CompanyId $companyId, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        return SaInvoiceModel::where('sa_company_id', $companyId->toInt())
            ->whereBetween('invoice_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->with('items')
            ->orderBy('invoice_date')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findByStatus(CompanyId $companyId, string $status): array
    {
        return SaInvoiceModel::where('sa_company_id', $companyId->toInt())
            ->where('status', $status)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findOverdue(CompanyId $companyId): array
    {
        return SaInvoiceModel::where('sa_company_id', $companyId->toInt())
            ->whereIn('status', ['draft', 'sent', 'partially_paid'])
            ->where('due_date', '<', now())
            ->with('items')
            ->orderBy('due_date')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function save(Invoice $invoice): Invoice
    {
        $data = [
            'sa_company_id' => $invoice->getCompanyId()->toInt(),
            'invoice_number' => $invoice->getInvoiceNumber(),
            'customer_name' => $invoice->getCustomerName(),
            'customer_phone' => $invoice->getCustomerPhone(),
            'customer_email' => $invoice->getCustomerEmail(),
            'invoice_date' => $invoice->getInvoiceDate()->format('Y-m-d'),
            'due_date' => $invoice->getDueDate()?->format('Y-m-d'),
            'status' => $invoice->getStatus(),
            'subtotal' => $invoice->getSubtotal()->toFloat(),
            'discount' => $invoice->getDiscount()->toFloat(),
            'tax' => $invoice->getTax()->toFloat(),
            'total' => $invoice->getTotal()->toFloat(),
            'amount_paid' => $invoice->getAmountPaid()->toFloat(),
            'balance_due' => $invoice->getBalanceDue()->toFloat(),
            'payment_terms' => $invoice->getPaymentTerms(),
            'notes' => $invoice->getNotes(),
            'created_by' => $invoice->getCreatedBy(),
            'sa_quotation_id' => $invoice->getQuotationId(),
            'sa_sale_id' => $invoice->getSaleId(),
        ];

        if ($invoice->id() === 0) {
            $model = SaInvoiceModel::create($data);
        } else {
            $model = SaInvoiceModel::find($invoice->id());
            $model->update($data);
        }

        SaInvoiceItemModel::where('sa_invoice_id', $model->id)->delete();
        foreach ($invoice->getItems() as $item) {
            SaInvoiceItemModel::create([
                'sa_invoice_id' => $model->id,
                'sa_item_id' => $item->getItemId()?->toInt(),
                'item_name' => $item->getItemName(),
                'quantity' => $item->getQuantity(),
                'unit_price' => $item->getUnitPrice()->toFloat(),
                'total' => $item->getTotal()->toFloat(),
            ]);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function nextInvoiceNumber(): string
    {
        $maxId = $this->getMaxId();
        return 'INV-' . str_pad($maxId + 1, 6, '0', STR_PAD_LEFT);
    }

    public function getMaxId(): int
    {
        return (int) SaInvoiceModel::max('id');
    }

    private function toDomainEntity(SaInvoiceModel $model): Invoice
    {
        $invoice = Invoice::reconstitute(
            id: InvoiceId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            invoiceNumber: $model->invoice_number,
            customerName: $model->customer_name,
            customerPhone: $model->customer_phone,
            customerEmail: $model->customer_email,
            invoiceDate: new DateTimeImmutable($model->invoice_date->format('Y-m-d')),
            dueDate: $model->due_date ? new DateTimeImmutable($model->due_date->format('Y-m-d')) : null,
            status: $model->status,
            subtotal: Money::fromFloat((float) $model->subtotal),
            discount: Money::fromFloat((float) ($model->discount ?? 0)),
            tax: Money::fromFloat((float) ($model->tax ?? 0)),
            total: Money::fromFloat((float) $model->total),
            amountPaid: Money::fromFloat((float) $model->amount_paid),
            balanceDue: Money::fromFloat((float) $model->balance_due),
            paymentTerms: $model->payment_terms,
            notes: $model->notes,
            createdBy: $model->created_by,
            quotationId: $model->sa_quotation_id,
            saleId: $model->sa_sale_id,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );

        if ($model->relationLoaded('items')) {
            foreach ($model->items as $itemModel) {
                $item = InvoiceItem::reconstitute(
                    id: new \App\Domain\StockFlow\ValueObjects\InvoiceItemId($itemModel->id),
                    invoiceId: InvoiceId::fromInt($itemModel->sa_invoice_id),
                    itemId: $itemModel->sa_item_id ? ItemId::fromInt($itemModel->sa_item_id) : null,
                    itemName: $itemModel->item_name,
                    quantity: (float) $itemModel->quantity,
                    unitPrice: Money::fromFloat((float) $itemModel->unit_price),
                    total: Money::fromFloat((float) $itemModel->total),
                    createdAt: new DateTimeImmutable($itemModel->created_at->format('Y-m-d H:i:s')),
                );
                $invoice->addItem($item);
            }
        }

        return $invoice;
    }
}
