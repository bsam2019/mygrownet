<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\Sale;
use App\Domain\StockFlow\Entities\SaleItem;
use App\Domain\StockFlow\Repositories\SaleRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\SaleId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Domain\StockFlow\ValueObjects\PaymentMethod;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaSaleModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaSaleItemModel;
use DateTimeImmutable;

class EloquentSaleRepository implements SaleRepositoryInterface
{
    public function findById(SaleId $id): ?Sale
    {
        $model = SaSaleModel::with('items')->find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId, int $perPage = 50): array
    {
        return SaSaleModel::where('sa_company_id', $companyId->toInt())
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findByCompanyIdAndDateBetween(CompanyId $companyId, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        return SaSaleModel::where('sa_company_id', $companyId->toInt())
            ->whereBetween('sale_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->with('items')
            ->orderBy('sale_date')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findByDate(CompanyId $companyId, DateTimeImmutable $date): array
    {
        return SaSaleModel::where('sa_company_id', $companyId->toInt())
            ->whereDate('sale_date', $date->format('Y-m-d'))
            ->with('items')
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function getTodayTotal(CompanyId $companyId): float
    {
        return (float) SaSaleModel::where('sa_company_id', $companyId->toInt())
            ->whereDate('sale_date', now())
            ->sum('total');
    }

    public function save(Sale $sale): Sale
    {
        $saleData = [
            'sa_company_id' => $sale->getCompanyId()->toInt(),
            'receipt_number' => $sale->getReceiptNumber(),
            'sale_date' => $sale->getSaleDate()->format('Y-m-d'),
            'sale_time' => $sale->getSaleDate()->format('H:i'),
            'payment_method' => $sale->getPaymentMethod()->value(),
            'subtotal' => $sale->getSubtotal()->toFloat(),
            'discount' => 0,
            'tax' => 0,
            'total' => $sale->getTotal()->toFloat(),
            'amount_tendered' => $sale->getAmountTendered()->toFloat(),
            'change_due' => $sale->getChangeDue()->toFloat(),
            'sold_by' => $sale->getSoldBy(),
            'notes' => $sale->getNotes(),
        ];

        if ($sale->id() === 0) {
            $model = SaSaleModel::create($saleData);
        } else {
            $model = SaSaleModel::find($sale->id());
            $model->update($saleData);
        }

        // Persist sale items
        SaSaleItemModel::where('sa_sale_id', $model->id)->delete();
        foreach ($sale->getItems() as $item) {
            SaSaleItemModel::create([
                'sa_sale_id' => $model->id,
                'sa_item_id' => $item->getItemId()->toInt(),
                'item_name' => $item->getItemName(),
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
        return 'INV-' . str_pad($maxId + 1, 6, '0', STR_PAD_LEFT);
    }

    public function getMaxId(): int
    {
        return (int) SaSaleModel::max('id');
    }

    private function toDomainEntity(SaSaleModel $model): Sale
    {
        $sale = Sale::reconstitute(
            id: SaleId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            receiptNumber: $model->receipt_number,
            saleDate: new DateTimeImmutable($model->sale_date->format('Y-m-d')),
            saleTime: $model->sale_time,
            paymentMethod: PaymentMethod::fromString($model->payment_method),
            subtotal: Money::fromFloat((float) $model->subtotal),
            discount: Money::fromFloat((float) ($model->discount ?? 0)),
            tax: Money::fromFloat((float) ($model->tax ?? 0)),
            total: Money::fromFloat((float) $model->total),
            amountTendered: Money::fromFloat((float) $model->amount_tendered),
            changeDue: Money::fromFloat((float) $model->change_due),
            soldBy: $model->sold_by,
            notes: $model->notes,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );

        if ($model->relationLoaded('items')) {
            foreach ($model->items as $itemModel) {
                $saleItem = SaleItem::reconstitute(
                    id: new \App\Domain\StockFlow\ValueObjects\SaleItemId($itemModel->id),
                    saleId: SaleId::fromInt($itemModel->sa_sale_id),
                    itemId: ItemId::fromInt($itemModel->sa_item_id),
                    itemName: $itemModel->item_name,
                    quantity: (float) $itemModel->quantity,
                    unitPrice: Money::fromFloat((float) $itemModel->unit_price),
                    total: Money::fromFloat((float) $itemModel->total),
                    createdAt: new DateTimeImmutable($itemModel->created_at->format('Y-m-d H:i:s')),
                );
                $sale->addItem($saleItem);
            }
        }

        return $sale;
    }
}
