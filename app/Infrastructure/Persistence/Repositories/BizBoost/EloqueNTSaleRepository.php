<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\BizBoost;

use App\Domain\BizBoost\Entities\Sale;
use App\Domain\BizBoost\Repositories\SaleRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BizBoostSaleModel;
use Illuminate\Support\Facades\DB;

class EloquentSaleRepository implements SaleRepositoryInterface
{
    public function findById(int $id): ?Sale
    {
        $model = BizBoostSaleModel::find($id);
        return $model ? Sale::reconstitute($model->toArray()) : null;
    }

    public function findByBusiness(int $businessId, array $filters = []): array
    {
        $query = BizBoostSaleModel::where('business_id', $businessId);

        if (!empty($filters['date_from'])) {
            $query->where('sale_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('sale_date', '<=', $filters['date_to']);
        }
        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        return $query->orderByDesc('sale_date')->get()
            ->map(fn($m) => Sale::reconstitute($m->toArray()))
            ->toArray();
    }

    public function save(Sale $entity): Sale
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            BizBoostSaleModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = BizBoostSaleModel::create($data);
        return Sale::reconstitute($model->toArray());
    }

    public function delete(int $id): void
    {
        BizBoostSaleModel::destroy($id);
    }

    public function sumByBusiness(int $businessId, array $conditions = []): float
    {
        $query = BizBoostSaleModel::where('business_id', $businessId);
        if (!empty($conditions['date_from'])) {
            $query->where('sale_date', '>=', $conditions['date_from']);
        }
        if (!empty($conditions['date_to'])) {
            $query->where('sale_date', '<=', $conditions['date_to']);
        }
        return (float) $query->sum('total_amount');
    }

    public function getSalesReport(int $businessId, string $startDate, string $endDate): array
    {
        $byDay = BizBoostSaleModel::where('business_id', $businessId)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw('DATE(sale_date)'))
            ->orderBy('date')
            ->get()
            ->toArray();

        $topProducts = BizBoostSaleModel::where('business_id', $businessId)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->select(
                'product_name',
                DB::raw('SUM(total_amount) as total'),
                DB::raw('SUM(quantity) as quantity')
            )
            ->groupBy('product_name')
            ->orderByDesc('total')
            ->take(10)
            ->get()
            ->toArray();

        $byPayment = BizBoostSaleModel::where('business_id', $businessId)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->whereNotNull('payment_method')
            ->select(
                'payment_method',
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('payment_method')
            ->get()
            ->toArray();

        return [
            'by_day' => $byDay,
            'top_products' => $topProducts,
            'by_payment' => $byPayment,
        ];
    }
}