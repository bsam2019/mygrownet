<?php

declare(strict_types=1);

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\ReportExportService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function __construct(
        private ReportExportService $exportService,
    ) {}

    public function csv(Request $request)
    {
        $businessId = $request->user()->id;
        $reportType = $request->input('report', 'trial_balance');
        $params = $request->only(['from', 'to', 'as_of']);

        return $this->exportService->exportCsv($businessId, $reportType, $params);
    }

    public function pdf(Request $request)
    {
        $businessId = $request->user()->id;
        $reportType = $request->input('report', 'trial_balance');
        $params = $request->only(['from', 'to', 'as_of']);

        $pdf = $this->exportService->exportPdf($businessId, $reportType, $params);
        return $pdf->download($reportType . '_' . date('Ymd') . '.pdf');
    }
}
