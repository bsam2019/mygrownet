<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\Item;
use App\Domain\StockFlow\Repositories\ItemRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\CategoryId;
use App\Domain\StockFlow\ValueObjects\DepartmentId;
use App\Domain\StockFlow\ValueObjects\BinId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaItemModel;
use DateTimeImmutable;

class EloquentItemRepository implements ItemRepositoryInterface
{
    public function findById(ItemId $id): ?Item
    {
        $model = SaItemModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId): array
    {
        return SaItemModel::where('sa_company_id', $companyId->toInt())
            ->orderBy('name')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findByCompanyIdWithFilters(CompanyId $companyId, array $filters = []): array
    {
        $query = SaItemModel::where('sa_company_id', $companyId->toInt());

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('sku', 'like', '%' . $filters['search'] . '%');
            });
        }
        if (!empty($filters['sa_bin_id'])) {
            $query->where('sa_bin_id', $filters['sa_bin_id']);
        }

        return $query->orderBy('name')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findInStock(CompanyId $companyId): array
    {
        return SaItemModel::where('sa_company_id', $companyId->toInt())
            ->where('system_quantity', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findLowStock(CompanyId $companyId): array
    {
        return SaItemModel::where('sa_company_id', $companyId->toInt())
            ->where('system_quantity', '>', 0)
            ->whereNotNull('reorder_level')
            ->whereRaw('system_quantity <= reorder_level')
            ->orderBy('name')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findOutOfStock(CompanyId $companyId): array
    {
        return SaItemModel::where('sa_company_id', $companyId->toInt())
            ->where('system_quantity', '<=', 0)
            ->orderBy('name')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function save(Item $item): Item
    {
        $data = [
            'sa_company_id' => $item->getCompanyId()->toInt(),
            'sa_department_id' => $item->getDepartmentIdValue(),
            'sa_bin_id' => $item->getBinIdValue(),
            'sa_category_id' => $item->getCategoryId()?->toInt(),
            'name' => $item->getName(),
            'sku' => $item->getSku(),
            'barcode' => $item->getBarcode(),
            'brand' => $item->getBrand(),
            'description' => $item->getDescription(),
            'unit_price' => $item->getUnitPrice()->toFloat(),
            'wholesale_price' => $item->getWholesalePrice()?->toFloat(),
            'vip_price' => $item->getVipPrice()?->toFloat(),
            'unit' => $item->getUnit(),
            'system_quantity' => $item->getSystemQuantity(),
            'reorder_level' => $item->getReorderLevel(),
            'category' => $item->getCategory(),
            'is_expirable' => $item->isExpirable(),
            'expiry_date' => $item->getExpiryDate()?->format('Y-m-d'),
            'notes' => $item->getNotes(),
        ];

        if ($item->id() === 0) {
            $model = SaItemModel::create($data);
        } else {
            $model = SaItemModel::find($item->id());
            $model->update($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function delete(ItemId $id): void
    {
        SaItemModel::destroy($id->toInt());
    }

    public function countByCompanyId(CompanyId $companyId): int
    {
        return SaItemModel::where('sa_company_id', $companyId->toInt())->count();
    }

    private function toDomainEntity(SaItemModel $model): Item
    {
        $departmentId = $model->sa_department_id ? DepartmentId::fromInt($model->sa_department_id) : null;
        $binId = $model->sa_bin_id ? BinId::fromInt($model->sa_bin_id) : null;
        $categoryId = $model->sa_category_id ? CategoryId::fromInt($model->sa_category_id) : null;
        $expiryDate = $model->expiry_date ? new DateTimeImmutable($model->expiry_date->format('Y-m-d')) : null;

        return Item::reconstitute(
            id: ItemId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            departmentId: $departmentId,
            binId: $binId,
            categoryId: $categoryId,
            name: $model->name,
            sku: $model->sku,
            barcode: $model->barcode,
            brand: $model->brand,
            description: $model->description,
            unitPrice: Money::fromFloat((float) $model->unit_price),
            wholesalePrice: $model->wholesale_price !== null ? Money::fromFloat((float) $model->wholesale_price) : null,
            vipPrice: $model->vip_price !== null ? Money::fromFloat((float) $model->vip_price) : null,
            unit: $model->unit,
            systemQuantity: (float) $model->system_quantity,
            reorderLevel: $model->reorder_level !== null ? (float) $model->reorder_level : null,
            category: $model->category,
            isExpirable: (bool) $model->is_expirable,
            expiryDate: $expiryDate,
            notes: $model->notes,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
