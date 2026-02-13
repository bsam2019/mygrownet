<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Core\Services\AnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {}

    public function operations(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;
        $period = $request->get('period', 'month');

        $metrics = $this->analyticsService->getOperationsMetrics($companyId, $period);

        return Inertia::render('CMS/Analytics/Operations', [
            'metrics' => $metrics,
            'period' => $period,
        ]);
    }

    public function finance(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;
        $period = $request->get('period', 'month');

        $metrics = $this->analyticsService->getFinanceMetrics($companyId, $period);

        return Inertia::render('CMS/Analytics/Finance', [
            'metrics' => $metrics,
            'period' => $period,
        ]);
    }
}
