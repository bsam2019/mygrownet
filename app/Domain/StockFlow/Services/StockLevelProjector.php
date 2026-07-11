<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Infrastructure\Persistence\Eloquent\StockFlow\SaStockLevelModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaStockMovementModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaItemModel;
use Illuminate\Support\Facades\DB;

/**
 * Rebuilds stock levels from the append-only stock_movements ledger.
 * This is the single source of truth — stock_levels is always a projection.
 */
class StockLevelProjector
{
    /**
     * Rebuild all stock levels for a company from ledger movements.
     */
    public function rebuildForCompany(int $companyId): void
    {
        DB::transaction(function () use ($companyId) {
            // Delete existing projections for this company
            SaStockLevelModel::where('sa_company_id', $companyId)->delete();

            // Rebuild from ledger — sum all movements per item
            $levels = SaStockMovementModel::where('sa_company_id', $companyId)
                ->select([
                    'sa_company_id',
                    'sa_item_id',
                    DB::raw('SUM(CASE WHEN type IN (\'purchase_in\',\'adjustment_in\',\'return_in\',\'physical_count\') THEN quantity ELSE 0 END) as total_in'),
                    DB::raw('SUM(CASE WHEN type IN (\'sale_out\',\'adjustment_out\',\'damage_out\',\'expired_out\') THEN ABS(quantity) ELSE 0 END) as total_out'),
                    DB::raw('MAX(created_at) as last_movement_at'),
                ])
                ->groupBy('sa_company_id', 'sa_item_id')
                ->get();

            foreach ($levels as $row) {
                $qtyOnHand = (float) $row->total_in - (float) $row->total_out;
                $item = SaItemModel::find($row->sa_item_id);
                $valueOnHand = $qtyOnHand * (float) ($item?->unit_price ?? 0);

                SaStockLevelModel::create([
                    'sa_company_id' => $companyId,
                    'sa_item_id' => $row->sa_item_id,
                    'qty_on_hand' => max(0, $qtyOnHand),
                    'value_on_hand' => $valueOnHand,
                    'last_movement_at' => $row->last_movement_at,
                ]);
            }

            // Also create zero-level entries for items with no movements
            $itemsWithMovements = $levels->pluck('sa_item_id');
            SaItemModel::where('sa_company_id', $companyId)
                ->whereNotIn('id', $itemsWithMovements)
                ->each(function ($item) use ($companyId) {
                    SaStockLevelModel::create([
                        'sa_company_id' => $companyId,
                        'sa_item_id' => $item->id,
                        'qty_on_hand' => 0,
                        'value_on_hand' => 0,
                        'last_movement_at' => null,
                    ]);
                });
        });
    }

    /**
     * Rebuild stock level for a single item from the ledger.
     */
    public function rebuildForItem(int $companyId, int $itemId): void
    {
        $in = (float) SaStockMovementModel::where('sa_company_id', $companyId)
            ->where('sa_item_id', $itemId)
            ->whereIn('type', ['purchase_in', 'adjustment_in', 'return_in', 'physical_count'])
            ->sum('quantity');

        $out = (float) SaStockMovementModel::where('sa_company_id', $companyId)
            ->where('sa_item_id', $itemId)
            ->whereIn('type', ['sale_out', 'adjustment_out', 'damage_out', 'expired_out'])
            ->sum(DB::raw('ABS(quantity)'));

        $qtyOnHand = max(0, $in - $out);
        $lastMovement = SaStockMovementModel::where('sa_company_id', $companyId)
            ->where('sa_item_id', $itemId)
            ->max('created_at');

        $item = SaItemModel::find($itemId);
        $valueOnHand = $qtyOnHand * (float) ($item?->unit_price ?? 0);

        SaStockLevelModel::updateOrCreate(
            ['sa_company_id' => $companyId, 'sa_item_id' => $itemId],
            [
                'qty_on_hand' => $qtyOnHand,
                'value_on_hand' => $valueOnHand,
                'last_movement_at' => $lastMovement,
            ]
        );

        // Update the item's system_quantity as well (cached projection)
        if ($item) {
            $item->update(['system_quantity' => $qtyOnHand]);
        }
    }

    /**
     * Get stock levels for all items in a company.
     */
    public function getLevelsForCompany(int $companyId): array
    {
        return SaStockLevelModel::where('sa_company_id', $companyId)
            ->with('item.bin.department')
            ->get()
            ->keyBy('sa_item_id')
            ->toArray();
    }

    /**
     * Calculate total value of stock on hand for a company.
     */
    public function getTotalValue(int $companyId): float
    {
        return (float) SaStockLevelModel::where('sa_company_id', $companyId)
            ->sum('value_on_hand');
    }
}
