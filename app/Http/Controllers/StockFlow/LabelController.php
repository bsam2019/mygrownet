<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaItemModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaCompanyModel;

class LabelController extends Controller
{
    public function printLabel(int $itemId)
    {
        $companyId = session('stockflow_company_id');
        $item = SaItemModel::with('company')->where('id', $itemId)->where('sa_company_id', $companyId)->firstOrFail();

        $html = view('stockflow.label', [
            'item' => $item,
            'company' => $item->company,
        ])->render();

        if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            return $pdf->download("label-{$item->sku}-{$item->id}.pdf");
        }

        return response($html)->header('Content-Type', 'text/html');
    }
}
