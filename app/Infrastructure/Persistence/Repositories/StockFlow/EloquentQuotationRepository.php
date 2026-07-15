<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\Quotation;
use App\Domain\StockFlow\Entities\QuotationItem;
use App\Domain\StockFlow\Repositories\QuotationRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\QuotationId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaQuotationModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaQuotationItemModel;
use DateTimeImmutable;

class EloquentQuotationRepository implements QuotationRepositoryInterface
{
    public function findById(QuotationId $id): ?Quotation
    {
        $model = SaQuotationModel::with('items')->find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId, int $perPage = 50): array
    {
        return SaQuotationModel::where('sa_company_id', $companyId->toInt())
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findByCompanyIdAndDateBetween(CompanyId $companyId, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        return SaQuotationModel::where('sa_company_id', $companyId->toInt())
            ->whereBetween('quotation_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->with('items')
            ->orderBy('quotation_date')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findByStatus(CompanyId $companyId, string $status): array
    {
        return SaQuotationModel::where('sa_company_id', $companyId->toInt())
            ->where('status', $status)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function save(Quotation $quotation): Quotation
    {
        $data = [
            'sa_company_id' => $quotation->getCompanyId()->toInt(),
            'quotation_number' => $quotation->getQuotationNumber(),
            'customer_name' => $quotation->getCustomerName(),
            'customer_phone' => $quotation->getCustomerPhone(),
            'customer_email' => $quotation->getCustomerEmail(),
            'quotation_date' => $quotation->getQuotationDate()->format('Y-m-d'),
            'expiry_date' => $quotation->getExpiryDate()?->format('Y-m-d'),
            'status' => $quotation->getStatus(),
            'subtotal' => $quotation->getSubtotal()->toFloat(),
            'discount' => $quotation->getDiscount()->toFloat(),
            'tax' => $quotation->getTax()->toFloat(),
            'total' => $quotation->getTotal()->toFloat(),
            'notes' => $quotation->getNotes(),
            'terms_conditions' => $quotation->getTermsConditions(),
            'created_by' => $quotation->getCreatedBy(),
            'converted_to_sale_id' => $quotation->getConvertedToSaleId(),
        ];

        if ($quotation->id() === 0) {
            $model = SaQuotationModel::create($data);
        } else {
            $model = SaQuotationModel::find($quotation->id());
            $model->update($data);
        }

        SaQuotationItemModel::where('sa_quotation_id', $model->id)->delete();
        foreach ($quotation->getItems() as $item) {
            SaQuotationItemModel::create([
                'sa_quotation_id' => $model->id,
                'sa_item_id' => $item->getItemId()?->toInt(),
                'item_name' => $item->getItemName(),
                'quantity' => $item->getQuantity(),
                'unit_price' => $item->getUnitPrice()->toFloat(),
                'total' => $item->getTotal()->toFloat(),
            ]);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function nextQuotationNumber(): string
    {
        $maxId = $this->getMaxId();
        return 'QTN-' . str_pad($maxId + 1, 6, '0', STR_PAD_LEFT);
    }

    public function getMaxId(): int
    {
        return (int) SaQuotationModel::max('id');
    }

    private function toDomainEntity(SaQuotationModel $model): Quotation
    {
        $quotation = Quotation::reconstitute(
            id: QuotationId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            quotationNumber: $model->quotation_number,
            customerName: $model->customer_name,
            customerPhone: $model->customer_phone,
            customerEmail: $model->customer_email,
            quotationDate: new DateTimeImmutable($model->quotation_date->format('Y-m-d')),
            expiryDate: $model->expiry_date ? new DateTimeImmutable($model->expiry_date->format('Y-m-d')) : null,
            status: $model->status,
            subtotal: Money::fromFloat((float) $model->subtotal),
            discount: Money::fromFloat((float) ($model->discount ?? 0)),
            tax: Money::fromFloat((float) ($model->tax ?? 0)),
            total: Money::fromFloat((float) $model->total),
            notes: $model->notes,
            termsConditions: $model->terms_conditions,
            createdBy: $model->created_by,
            convertedToSaleId: $model->converted_to_sale_id,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );

        if ($model->relationLoaded('items')) {
            foreach ($model->items as $itemModel) {
                $item = QuotationItem::reconstitute(
                    id: new \App\Domain\StockFlow\ValueObjects\QuotationItemId($itemModel->id),
                    quotationId: QuotationId::fromInt($itemModel->sa_quotation_id),
                    itemId: $itemModel->sa_item_id ? ItemId::fromInt($itemModel->sa_item_id) : null,
                    itemName: $itemModel->item_name,
                    quantity: (float) $itemModel->quantity,
                    unitPrice: Money::fromFloat((float) $itemModel->unit_price),
                    total: Money::fromFloat((float) $itemModel->total),
                    createdAt: new DateTimeImmutable($itemModel->created_at->format('Y-m-d H:i:s')),
                );
                $quotation->addItem($item);
            }
        }

        return $quotation;
    }
}
