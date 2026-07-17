<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaItemModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaCustomerModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaSupplierModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaPurchaseOrderModel;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');
        $companyId = $request->session()->get('stockflow_company_id');

        if (!$query || strlen($query) < 2) {
            return response()->json([]);
        }

        $isSubdomain = $request->route() && str_starts_with($request->route()->getName() ?? '', 'stockflow.sub.');
        $pfx = $isSubdomain ? 'stockflow.sub.' : 'stockflow.';

        $results = [];

        $items = SaItemModel::where('sa_company_id', $companyId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'type' => 'Items',
                'route' => route($pfx . 'items.show', $m->id, false),
            ]);

        if ($items->count()) {
            $results[] = ['label' => 'Items', 'results' => $items];
        }

        $customers = SaCustomerModel::where('sa_company_id', $companyId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'type' => 'Customers',
                'route' => route($pfx . 'customers.show', $m->id, false),
            ]);

        if ($customers->count()) {
            $results[] = ['label' => 'Customers', 'results' => $customers];
        }

        $suppliers = SaSupplierModel::where('sa_company_id', $companyId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'type' => 'Suppliers',
                'route' => route($pfx . 'suppliers.index', [], false),
            ]);

        if ($suppliers->count()) {
            $results[] = ['label' => 'Suppliers', 'results' => $suppliers];
        }

        $orders = SaPurchaseOrderModel::where('sa_company_id', $companyId)
            ->where(function ($q) use ($query) {
                $q->where('order_number', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->order_number,
                'type' => 'Purchase Orders',
                'route' => route($pfx . 'purchases.show', $m->id, false),
            ]);

        if ($orders->count()) {
            $results[] = ['label' => 'Purchase Orders', 'results' => $orders];
        }

        return response()->json($results);
    }
}
