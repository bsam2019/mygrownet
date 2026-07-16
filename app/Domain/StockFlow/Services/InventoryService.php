<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\Item;
use App\Domain\StockFlow\Entities\StockMovement;
use App\Domain\StockFlow\Events\StockAdjusted;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\ItemRepositoryInterface;
use App\Domain\StockFlow\Repositories\StockMovementRepositoryInterface;
use App\Domain\StockFlow\Repositories\LotRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\CategoryId;
use App\Domain\StockFlow\ValueObjects\DepartmentId;
use App\Domain\StockFlow\ValueObjects\BinId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Domain\StockFlow\ValueObjects\MovementType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Throwable;

class InventoryService
{
    public function __construct(
        private ItemRepositoryInterface $itemRepository,
        private StockMovementRepositoryInterface $movementRepository,
        private StockLevelProjector $stockLevelProjector,
        private LotRepositoryInterface $lotRepository,
    ) {}

    public function createItem(int $companyId, array $data): Item
    {
        try {
            $item = Item::create(
                companyId: CompanyId::fromInt($companyId),
                departmentId: isset($data['sa_department_id']) ? DepartmentId::fromInt($data['sa_department_id']) : null,
                binId: isset($data['sa_bin_id']) ? BinId::fromInt($data['sa_bin_id']) : null,
                categoryId: isset($data['sa_category_id']) ? CategoryId::fromInt((int) $data['sa_category_id']) : null,
                name: $data['name'],
                sku: $data['sku'] ?? null,
                barcode: $data['barcode'] ?? null,
                brand: $data['brand'] ?? null,
                description: $data['description'] ?? null,
                unitPrice: Money::fromFloat((float) ($data['unit_price'] ?? 0)),
                wholesalePrice: isset($data['wholesale_price']) ? Money::fromFloat((float) $data['wholesale_price']) : null,
                vipPrice: isset($data['vip_price']) ? Money::fromFloat((float) $data['vip_price']) : null,
                unit: $data['unit'] ?? null,
                systemQuantity: (float) ($data['system_quantity'] ?? 0),
                reorderLevel: isset($data['reorder_level']) ? (float) $data['reorder_level'] : null,
                category: $data['category'] ?? null,
                isExpirable: (bool) ($data['is_expirable'] ?? false),
                expiryDate: isset($data['expiry_date']) && $data['expiry_date'] ? new \DateTimeImmutable($data['expiry_date']) : null,
                notes: $data['notes'] ?? null,
            );

            $savedItem = $this->itemRepository->save($item);

            // Record opening balance in ledger if initial stock > 0
            if ($savedItem->getSystemQuantity() > 0) {
                $this->movementRepository->save(
                    StockMovement::create(
                        companyId: CompanyId::fromInt($companyId),
                        itemId: ItemId::fromInt($savedItem->id()),
                        binId: $savedItem->getBinId(),
                        type: MovementType::fromString('purchase_in'),
                        quantity: $savedItem->getSystemQuantity(),
                        unitPrice: $savedItem->getUnitPrice(),
                        quantityBefore: 0,
                        quantityAfter: $savedItem->getSystemQuantity(),
                        reason: 'Opening balance',
                        createdBy: 0,
                    )
                );
                $this->stockLevelProjector->rebuildForItem($companyId, $savedItem->id());
            }

            return $savedItem;
        } catch (Throwable $e) {
            throw new OperationFailedException('create item', $e->getMessage());
        }
    }

    public function updateItem(int $itemId, int $companyId, array $data): Item
    {
        try {
            $item = $this->itemRepository->findById(ItemId::fromInt($itemId));
            if (!$item || $item->getCompanyId()->toInt() !== $companyId) {
                throw new OperationFailedException('update item', 'Item not found or access denied');
            }

            $item->updateDetails(
                name: $data['name'] ?? $item->getName(),
                departmentId: isset($data['sa_department_id']) ? DepartmentId::fromInt($data['sa_department_id']) : $item->getDepartmentId(),
                binId: isset($data['sa_bin_id']) ? BinId::fromInt($data['sa_bin_id']) : $item->getBinId(),
                categoryId: isset($data['sa_category_id']) ? CategoryId::fromInt((int) $data['sa_category_id']) : $item->getCategoryId(),
                sku: $data['sku'] ?? $item->getSku(),
                barcode: $data['barcode'] ?? $item->getBarcode(),
                brand: $data['brand'] ?? $item->getBrand(),
                description: $data['description'] ?? $item->getDescription(),
                unitPrice: isset($data['unit_price']) ? Money::fromFloat((float) $data['unit_price']) : $item->getUnitPrice(),
                wholesalePrice: isset($data['wholesale_price']) ? Money::fromFloat((float) $data['wholesale_price']) : $item->getWholesalePrice(),
                vipPrice: isset($data['vip_price']) ? Money::fromFloat((float) $data['vip_price']) : $item->getVipPrice(),
                unit: $data['unit'] ?? $item->getUnit(),
                reorderLevel: $data['reorder_level'] ?? $item->getReorderLevel(),
                category: $data['category'] ?? $item->getCategory(),
                notes: $data['notes'] ?? $item->getNotes(),
                isExpirable: (bool) ($data['is_expirable'] ?? $item->isExpirable()),
                expiryDate: isset($data['expiry_date']) && $data['expiry_date'] ? new \DateTimeImmutable($data['expiry_date']) : $item->getExpiryDate(),
            );

            return $this->itemRepository->save($item);
        } catch (Throwable $e) {
            throw new OperationFailedException('update item', $e->getMessage());
        }
    }

    public function adjustStock(int $itemId, int $companyId, float $newQuantity, string $type, string $reason, int $userId): Item
    {
        return DB::transaction(function () use ($itemId, $companyId, $newQuantity, $type, $reason, $userId) {
            $item = $this->itemRepository->findById(ItemId::fromInt($itemId));
            if (!$item || $item->getCompanyId()->toInt() !== $companyId) {
                throw new OperationFailedException('adjust stock', 'Item not found or access denied');
            }

            // Get current stock from projection
            $levels = $this->stockLevelProjector->getLevelsForCompany($companyId);
            $qtyBefore = (float) ($levels[$itemId]['qty_on_hand'] ?? $item->getSystemQuantity());
            $diff = $newQuantity - $qtyBefore;

            // Record stock movement (the ledger is the source of truth)
            $this->movementRepository->save(
                StockMovement::create(
                    companyId: CompanyId::fromInt($companyId),
                    itemId: ItemId::fromInt($itemId),
                    binId: $item->getBinId(),
                    type: MovementType::fromString($type),
                    quantity: $diff,
                    unitPrice: $item->getUnitPrice(),
                    quantityBefore: $qtyBefore,
                    quantityAfter: $newQuantity,
                    reason: $reason,
                    createdBy: $userId,
                )
            );

            // Rebuild stock level projection from ledger
            $this->stockLevelProjector->rebuildForItem($companyId, $itemId);

            // Dispatch domain event
            Event::dispatch(new StockAdjusted(
                companyId: $companyId,
                itemId: $itemId,
                reason: $reason,
                quantityBefore: $qtyBefore,
                quantityAfter: $newQuantity,
                adjustmentType: $type,
                adjustedBy: $userId,
            ));

            // Return fresh item with updated projection
            return $this->itemRepository->findById(ItemId::fromInt($itemId));
        });
    }

    public function deleteItem(int $itemId, int $companyId): void
    {
        try {
            $item = $this->itemRepository->findById(ItemId::fromInt($itemId));
            if (!$item || $item->getCompanyId()->toInt() !== $companyId) {
                throw new OperationFailedException('delete item', 'Item not found or access denied');
            }
            $this->itemRepository->delete(ItemId::fromInt($itemId));
        } catch (Throwable $e) {
            throw new OperationFailedException('delete item', $e->getMessage());
        }
    }

    public function getItemById(int $itemId, int $companyId): ?Item
    {
        $item = $this->itemRepository->findById(ItemId::fromInt($itemId));
        if ($item && $item->getCompanyId()->toInt() !== $companyId) {
            return null;
        }
        return $item;
    }

    public function getItemsForCompany(int $companyId): array
    {
        return $this->itemRepository->findByCompanyId(CompanyId::fromInt($companyId));
    }

    public function getItemsForCompanyWithFilters(int $companyId, array $filters = []): array
    {
        return $this->itemRepository->findByCompanyIdWithFilters(CompanyId::fromInt($companyId), $filters);
    }

    public function getInStockItems(int $companyId): array
    {
        return $this->itemRepository->findInStock(CompanyId::fromInt($companyId));
    }

    public function getItemCount(int $companyId): int
    {
        return $this->itemRepository->countByCompanyId(CompanyId::fromInt($companyId));
    }

    /**
     * FEFO: Returns the nearest-expiry batch for a given item.
     * Returns null if no active lots exist.
     */
    public function getFefoBatch(int $companyId, int $itemId): ?array
    {
        $companyCid = CompanyId::fromInt($companyId);
        $itemIdVo = ItemId::fromInt($itemId);
        $lots = $this->lotRepository->findByItemId($companyCid, $itemIdVo);

        // Filter active lots with current_quantity > 0 and sort by expiry_date ASC
        $active = array_filter($lots, fn($l) => $l->getCurrentQuantity() > 0 && $l->getStatus() === 'active');
        usort($active, fn($a, $b) => ($a->toArray()['expiry_date'] ?? '9999-12-31') <=> ($b->toArray()['expiry_date'] ?? '9999-12-31'));

        if (empty($active)) return null;
        return $active[0]->toArray();
    }

    /**
     * Get items expiring within a given number of days.
     */
    public function getExpiringItems(int $companyId, int $days = 30): array
    {
        $items = $this->itemRepository->findByCompanyId(CompanyId::fromInt($companyId));
        $now = new \DateTimeImmutable();
        $cutoff = $now->modify("+{$days} days");
        $result = [];

        foreach ($items as $item) {
            $expiry = $item->getExpiryDate();
            if ($expiry && $expiry >= $now && $expiry <= $cutoff && $item->getSystemQuantity() > 0) {
                $arr = $item->toArray();
                $arr['days_until_expiry'] = (int) $now->diff($expiry)->format('%a');
                $result[] = $arr;
            }
            // Also check lots
            $lots = $this->lotRepository->findByItemId(CompanyId::fromInt($companyId), $item->getId());
            foreach ($lots as $lot) {
                $lotExpiry = $lot->toArray()['expiry_date'];
                if ($lotExpiry && $lot->getCurrentQuantity() > 0) {
                    $lotDate = new \DateTimeImmutable($lotExpiry);
                    if ($lotDate >= $now && $lotDate <= $cutoff) {
                        $arr = $lot->toArray();
                        $arr['item_name'] = $item->getName();
                        $arr['days_until_expiry'] = (int) $now->diff($lotDate)->format('%a');
                        $result[] = $arr;
                    }
                }
            }
        }

        usort($result, fn($a, $b) => ($a['days_until_expiry'] ?? 999) <=> ($b['days_until_expiry'] ?? 999));
        return $result;
    }

    /**
     * Get inventory valuation breakdown by cost/selling price.
     */
    public function getInventoryValuation(int $companyId): array
    {
        $items = $this->itemRepository->findByCompanyId(CompanyId::fromInt($companyId));
        $totalCost = 0;
        $totalRetail = 0;
        $totalWholesale = 0;
        $totalVip = 0;
        $count = 0;

        foreach ($items as $item) {
            $qty = $item->getSystemQuantity();
            if ($qty <= 0) continue;
            $totalCost += $item->getStockValue()->toFloat();
            $totalRetail += ($item->getUnitPrice()->toFloat() * $qty);
            $totalWholesale += (($item->getWholesalePrice()?->toFloat() ?? $item->getUnitPrice()->toFloat()) * $qty);
            $totalVip += (($item->getVipPrice()?->toFloat() ?? $item->getUnitPrice()->toFloat()) * $qty);
            $count++;
        }

        return [
            'total_items_with_stock' => $count,
            'total_cost_value' => round($totalCost, 2),
            'total_retail_value' => round($totalRetail, 2),
            'total_wholesale_value' => round($totalWholesale, 2),
            'total_vip_value' => round($totalVip, 2),
            'potential_profit_retail' => round($totalRetail - $totalCost, 2),
            'potential_profit_wholesale' => round($totalWholesale - $totalCost, 2),
        ];
    }
}
