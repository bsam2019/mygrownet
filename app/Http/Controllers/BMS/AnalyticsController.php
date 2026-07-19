<?php

namespace App\Http\Controllers\BMS;

use App\Http\Controllers\Controller;
use App\Domain\BMS\Core\Services\AnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {}

    public function overview(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $metrics = $this->analyticsService->getOverviewMetrics($companyId);

        return Inertia::render('BMS/Analytics/Overview', [
            'metrics' => $metrics,
        ]);
    }

    public function operations(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;
        $period = $request->get('period', 'month');

        $metrics = $this->analyticsService->getOperationsMetrics($companyId, $period);

        return Inertia::render('BMS/Analytics/Operations', [
            'metrics' => $metrics,
            'period' => $period,
        ]);
    }

    public function finance(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;
        $period = $request->get('period', 'month');

        $metrics = $this->analyticsService->getFinanceMetrics($companyId, $period);

        return Inertia::render('BMS/Analytics/Finance', [
            'metrics' => $metrics,
            'period' => $period,
        ]);
    }

    public function procurement(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;
        $period = $request->get('period', 'month');

        $metrics = $this->analyticsService->getProcurementMetrics($companyId, $period);
        $contracts = $this->analyticsService->getContractMetrics($companyId);
        $assets = $this->analyticsService->getAssetMetrics($companyId);

        return Inertia::render('BMS/Analytics/Procurement', [
            'metrics' => $metrics,
            'contracts' => $contracts,
            'assets' => $assets,
            'period' => $period,
        ]);
    }

    public function exportCsv(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;
        $period = $request->get('period', 'month');

        $csv = $this->analyticsService->exportFinanceCsv($companyId, $period);
        $filename = 'finance-export-' . now()->format('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
