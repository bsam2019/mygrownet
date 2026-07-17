<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaItemModel;
use Illuminate\Http\Request;

class ItemBulkController extends Controller
{
    public function bulkDelete(Request $request)
    {
        $companyId = session('stockflow_company_id');

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:sa_items,id',
        ]);

        SaItemModel::whereIn('id', $validated['ids'])
            ->where('sa_company_id', $companyId)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Items deleted successfully.']);
    }

    public function bulkAdjustStock(Request $request)
    {
        $companyId = session('stockflow_company_id');

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:sa_items,id',
            'quantity_change' => 'required|numeric',
            'reason' => 'required|string|max:255',
        ]);

        $items = SaItemModel::whereIn('id', $validated['ids'])
            ->where('sa_company_id', $companyId)
            ->get();

        foreach ($items as $item) {
            $newQty = max(0, $item->system_quantity + $validated['quantity_change']);
            $item->update(['system_quantity' => $newQty]);
        }

        return response()->json(['success' => true, 'message' => 'Stock adjusted for ' . count($items) . ' items.']);
    }

    public function bulkUpdatePrice(Request $request)
    {
        $companyId = session('stockflow_company_id');

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:sa_items,id',
            'unit_price' => 'required|numeric|min:0',
        ]);

        SaItemModel::whereIn('id', $validated['ids'])
            ->where('sa_company_id', $companyId)
            ->update(['unit_price' => $validated['unit_price']]);

        return response()->json(['success' => true, 'message' => 'Price updated successfully.']);
    }
}
