<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Repositories\ItemRepositoryInterface;
use App\Domain\StockFlow\Repositories\SaleRepositoryInterface;
use App\Domain\StockFlow\Repositories\StockMovementRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Domain\StockFlow\Services\StockLevelProjector;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaItemModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaLotModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaSaleItemModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaStockMovementModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdvancedReportController extends Controller
{
    public function __construct(
        private StockLevelProjector $stockLevelProjector,
    ) {}

    public function abcAnalysis(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');

        $items = SaItemModel::where('sa_company_id', $companyId)->get();
        $levels = $this->stockLevelProjector->getLevelsForCompany($companyId);

        $analysis = [];
        foreach ($items as $item) {
            $qty = (float) ($levels[$item->id]['qty_on_hand'] ?? $item->system_quantity);
            $unitPrice = (float) $item->unit_price;
            $annualValue = $qty * $unitPrice;
            $analysis[] = [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'annual_value' => round($annualValue, 2),
                'percentage' => 0,
                'cumulative_percentage' => 0,
                'class' => '',
            ];
        }

        usort($analysis, fn($a, $b) => $b['annual_value'] <=> $a['annual_value']);

        $totalValue = array_sum(array_column($analysis, 'annual_value'));
        $cumulative = 0;

        foreach ($analysis as &$row) {
            $row['percentage'] = $totalValue > 0 ? round(($row['annual_value'] / $totalValue) * 100, 2) : 0;
            $cumulative += $row['percentage'];
            $row['cumulative_percentage'] = round($cumulative, 2);
            if ($cumulative <= 80) $row['class'] = 'A';
            elseif ($cumulative <= 95) $row['class'] = 'B';
            else $row['class'] = 'C';
        }

        return Inertia::render('StockFlow/Reports/ABC', [
            'items' => $analysis,
            'total_value' => round($totalValue, 2),
        ]);
    }

    public function stockAging(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');

        $items = SaItemModel::where('sa_company_id', $companyId)
            ->where('is_expirable', true)
            ->get();

        $now = now();
        $buckets = [
            '0-30' => ['label' => '0-30 days', 'items' => [], 'count' => 0, 'value' => 0],
            '31-60' => ['label' => '31-60 days', 'items' => [], 'count' => 0, 'value' => 0],
            '61-90' => ['label' => '61-90 days', 'items' => [], 'count' => 0, 'value' => 0],
            '90+' => ['label' => '90+ days', 'items' => [], 'count' => 0, 'value' => 0],
            'expired' => ['label' => 'Expired', 'items' => [], 'count' => 0, 'value' => 0],
        ];

        foreach ($items as $item) {
            if (!$item->expiry_date) continue;
            $expiry = \Carbon\Carbon::parse($item->expiry_date);
            $diff = $now->diffInDays($expiry, false);

            $qty = (float) $item->system_quantity;
            $value = $qty * (float) $item->unit_price;

            $entry = [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'expiry_date' => $item->expiry_date->format('Y-m-d'),
                'quantity' => $qty,
                'value' => round($value, 2),
                'days_remaining' => (int) $diff,
            ];

            if ($diff < 0) $key = 'expired';
            elseif ($diff <= 30) $key = '0-30';
            elseif ($diff <= 60) $key = '31-60';
            elseif ($diff <= 90) $key = '61-90';
            else $key = '90+';

            $buckets[$key]['items'][] = $entry;
            $buckets[$key]['count']++;
            $buckets[$key]['value'] = round($buckets[$key]['value'] + $value, 2);
        }

        // Also check lots
        $lots = SaLotModel::whereHas('item', fn($q) => $q->where('sa_company_id', $companyId))
            ->where('status', 'active')
            ->whereNotNull('expiry_date')
            ->get();

        foreach ($lots as $lot) {
            $expiry = \Carbon\Carbon::parse($lot->expiry_date);
            $diff = $now->diffInDays($expiry, false);
            $qty = (float) $lot->current_quantity;
            if ($qty <= 0) continue;
            $value = $qty * (float) ($lot->item?->unit_price ?? 0);

            $entry = [
                'id' => $lot->id,
                'name' => ($lot->item?->name ?? 'Unknown') . " (Lot: {$lot->lot_number})",
                'sku' => $lot->item?->sku,
                'expiry_date' => $lot->expiry_date->format('Y-m-d'),
                'quantity' => $qty,
                'value' => round($value, 2),
                'days_remaining' => (int) $diff,
            ];

            if ($diff < 0) $key = 'expired';
            elseif ($diff <= 30) $key = '0-30';
            elseif ($diff <= 60) $key = '31-60';
            elseif ($diff <= 90) $key = '61-90';
            else $key = '90+';

            $buckets[$key]['items'][] = $entry;
            $buckets[$key]['count']++;
            $buckets[$key]['value'] = round($buckets[$key]['value'] + $value, 2);
        }

        return Inertia::render('StockFlow/Reports/Aging', [
            'buckets' => $buckets,
        ]);
    }

    public function inventoryTurnover(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');

        $items = SaItemModel::where('sa_company_id', $companyId)->get();
        $levels = $this->stockLevelProjector->getLevelsForCompany($companyId);

        $sixMonthsAgo = now()->subMonths(6);

        $turnoverData = [];
        foreach ($items as $item) {
            // Total sales quantity in last 6 months
            $totalSold = (float) SaSaleItemModel::where('sa_item_id', $item->id)
                ->whereHas('sale', fn($q) => $q->where('sa_company_id', $companyId)->where('sale_date', '>=', $sixMonthsAgo))
                ->sum('quantity');

            $currentQty = (float) ($levels[$item->id]['qty_on_hand'] ?? $item->system_quantity);
            $avgInventory = max(1, ($currentQty + $totalSold) / 2);
            $turnover = $avgInventory > 0 ? round($totalSold / $avgInventory, 2) : 0;

            $turnoverData[] = [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'total_sold' => $totalSold,
                'current_stock' => $currentQty,
                'avg_inventory' => round($avgInventory, 2),
                'turnover' => $turnover,
                'category' => $turnover >= 3 ? 'Fast mover' : ($turnover >= 1 ? 'Medium' : 'Slow mover'),
            ];
        }

        usort($turnoverData, fn($a, $b) => $b['turnover'] <=> $a['turnover']);

        return Inertia::render('StockFlow/Reports/Turnover', [
            'items' => $turnoverData,
        ]);
    }
}
