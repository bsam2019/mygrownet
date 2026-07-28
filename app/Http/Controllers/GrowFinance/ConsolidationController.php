<?php

declare(strict_types=1);

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\ConsolidationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ConsolidationController extends Controller
{
    public function __construct(
        private ConsolidationService $consolidationService,
    ) {}

    public function index(Request $request)
    {
        $businessId = $request->user()->id;
        $period = $request->get('period', date('Y-m'));
        $consolidation = $this->consolidationService->getConsolidatedStatements($businessId, $period);
        $history = $this->consolidationService->listConsolidations($businessId);

        return Inertia::render('GrowFinance/Reports/Consolidation', [
            'consolidation' => $consolidation?->toArray(),
            'history' => array_map(fn($c) => $c->toArray(), $history),
            'currentPeriod' => $period,
        ]);
    }

    public function consolidate(Request $request)
    {
        $businessId = $request->user()->id;
        $period = $request->input('period', date('Y-m'));
        $reportingCurrency = $request->input('reporting_currency', 'ZMW');

        try {
            $result = $this->consolidationService->consolidate($businessId, $period, $reportingCurrency);
            return redirect()->back()->with('success', 'Consolidation completed for ' . $period);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Consolidation failed: ' . $e->getMessage());
        }
    }

    public function show(Request $request, int $id)
    {
        $businessId = $request->user()->id;
        $repo = app(\App\Domain\GrowFinance\Repositories\GroupConsolidationRepositoryInterface::class);
        $consolidation = $repo->findById($id);
        if (!$consolidation) {
            return redirect()->back()->with('error', 'Consolidation not found');
        }
        return Inertia::render('GrowFinance/Reports/Consolidation', [
            'consolidation' => $consolidation->toArray(),
            'currentPeriod' => $consolidation->period,
        ]);
    }
}
