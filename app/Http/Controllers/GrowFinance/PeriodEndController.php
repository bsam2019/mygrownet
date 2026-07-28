<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\PeriodEndService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PeriodEndController extends Controller
{
    public function __construct(
        private PeriodEndService $periodEndService,
    ) {}

    public function index(Request $request): Response
    {
        $businessId = $request->user()->id;
        $checklist = $this->periodEndService->getChecklist($businessId);

        return Inertia::render('GrowFinance/PeriodEnd/Index', [
            'checklist' => $checklist,
            'currentPeriod' => [
                'start' => Carbon::now()->startOfMonth()->format('Y-m-d'),
                'end' => Carbon::now()->endOfMonth()->format('Y-m-d'),
                'label' => Carbon::now()->format('F Y'),
            ],
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $businessId = $request->user()->id;
        $start = $request->get('period_start', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $end = $request->get('period_end', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $this->periodEndService->generateChecklist($businessId, $start, $end, $businessId);

        return redirect()->route('growfinance.period-end.index')
            ->with('success', 'Period-end checklist generated.');
    }

    public function completeTask(Request $request, int $checklistId): RedirectResponse
    {
        $validated = $request->validate(['task_name' => 'required|string']);
        $this->periodEndService->completeTask($checklistId, $validated['task_name'], $request->user()->id);

        return back()->with('success', 'Task marked as completed.');
    }

    public function runDepreciation(Request $request, int $checklistId): RedirectResponse
    {
        $result = $this->periodEndService->runDepreciation($request->user()->id);

        $this->periodEndService->completeTask(
            $checklistId,
            'Run depreciation',
            $request->user()->id,
        );

        return back()->with('success', "Depreciation posted for {$result['assets_depreciated']} asset(s).");
    }

    public function snapshotReports(Request $request, int $checklistId): RedirectResponse
    {
        $start = Carbon::now()->startOfMonth()->format('Y-m-d');
        $end = Carbon::now()->endOfMonth()->format('Y-m-d');

        $this->periodEndService->snapshotReports($request->user()->id, $start, $end);

        $this->periodEndService->completeTask(
            $checklistId,
            'Snapshot financial reports',
            $request->user()->id,
        );

        return back()->with('success', 'Financial reports snapshotted.');
    }

    public function closePeriod(Request $request, int $checklistId): RedirectResponse
    {
        $start = Carbon::now()->startOfMonth()->format('Y-m-d');
        $end = Carbon::now()->endOfMonth()->format('Y-m-d');

        $this->periodEndService->closePeriod($request->user()->id, $start, $end, $request->user()->id);

        return redirect()->route('growfinance.period-end.index')
            ->with('success', 'Accounting period closed successfully.');
    }
}
