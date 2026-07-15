<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\Receipt;
use App\Domain\StockFlow\Entities\ReceiptItem;
use App\Domain\StockFlow\Repositories\ReceiptRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\ReceiptId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Domain\StockFlow\ValueObjects\PaymentMethod;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaReceiptModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaReceiptItemModel;
use DateTimeImmutable;

class EloquentReceiptRepository implements ReceiptRepositoryInterface
{
    public function findById(ReceiptId $id): ?Receipt
    {
        $model = SaReceiptModel::with('items')->find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId, int $perPage = 50): array
    {
        return SaReceiptModel::where('sa_company_id', $companyId->toInt())
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findByCompanyIdAndDateBetween(CompanyId $companyId, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        return SaReceiptModel::where('sa_company_id', $companyId->toInt())
            ->whereBetween('receipt_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->with('items')
            ->orderBy('receipt_date')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findBySaleId(CompanyId $companyId, int $saleId): array
    {
        return SaReceiptModel::where('sa_company_id', $companyId->toInt())
            ->where('sa_sale_id', $saleId)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function save(Receipt $receipt): Receipt
    {
        $data = [
            'sa_company_id' => $receipt->getCompanyId()->toInt(),
            'receipt_number' => $receipt->getReceiptNumber(),
            'sa_sale_id' => $receipt->getSaleId(),
            'sa_invoice_id' => $receipt->getInvoiceId(),
            'customer_name' => $receipt->getCustomerName(),
            'customer_phone' => $receipt->getCustomerPhone(),
            'customer_email' => $receipt->getCustomerEmail(),
            'receipt_date' => $receipt->getReceiptDate()->format('Y-m-d'),
            'payment_method' => $receipt->getPaymentMethod()->value(),
            'subtotal' => $receipt->getTotal()->toFloat(),
            'total' => $receipt->getTotal()->toFloat(),
            'amount_received' => $receipt->getAmountReceived()->toFloat(),
            'change_due' => $receipt->getChangeDue()->toFloat(),
            'reference_number' => $receipt->getReferenceNumber(),
            'notes' => $receipt->getNotes(),
            'created_by' => $receipt->getCreatedBy(),
        ];

        if ($receipt->id() === 0) {
            $model = SaReceiptModel::create($data);
        } else {
            $model = SaReceiptModel::find($receipt->id());
            $model->update($data);
        }

        SaReceiptItemModel::where('sa_receipt_id', $model->id)->delete();
        foreach ($receipt->getItems() as $item) {
            SaReceiptItemModel::create([
                'sa_receipt_id' => $model->id,
                'item_description' => $item->getItemDescription(),
                'quantity' => $item->getQuantity(),
                'unit_price' => $item->getUnitPrice()->toFloat(),
                'total' => $item->getTotal()->toFloat(),
            ]);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function nextReceiptNumber(): string
    {
        $maxId = $this->getMaxId();
        return 'RCT-' . str_pad($maxId + 1, 6, '0', STR_PAD_LEFT);
    }

    public function getMaxId(): int
    {
        return (int) SaReceiptModel::max('id');
    }

    private function toDomainEntity(SaReceiptModel $model): Receipt
    {
        $receipt = Receipt::reconstitute(
            id: ReceiptId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            receiptNumber: $model->receipt_number,
            saleId: $model->sa_sale_id,
            invoiceId: $model->sa_invoice_id,
            customerName: $model->customer_name,
            customerPhone: $model->customer_phone,
            customerEmail: $model->customer_email,
            receiptDate: new DateTimeImmutable($model->receipt_date->format('Y-m-d')),
            paymentMethod: PaymentMethod::fromString($model->payment_method),
            subtotal: Money::fromFloat((float) $model->subtotal),
            total: Money::fromFloat((float) $model->total),
            amountReceived: Money::fromFloat((float) $model->amount_received),
            changeDue: Money::fromFloat((float) $model->change_due),
            referenceNumber: $model->reference_number,
            notes: $model->notes,
            createdBy: $model->created_by,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );

        if ($model->relationLoaded('items')) {
            foreach ($model->items as $itemModel) {
                $item = ReceiptItem::reconstitute(
                    id: $itemModel->id,
                    receiptId: ReceiptId::fromInt($itemModel->sa_receipt_id),
                    itemDescription: $itemModel->item_description,
                    quantity: (float) $itemModel->quantity,
                    unitPrice: Money::fromFloat((float) $itemModel->unit_price),
                    total: Money::fromFloat((float) $itemModel->total),
                    createdAt: new DateTimeImmutable($itemModel->created_at->format('Y-m-d H:i:s')),
                );
                $receipt->addItem($item);
            }
        }

        return $receipt;
    }
}
