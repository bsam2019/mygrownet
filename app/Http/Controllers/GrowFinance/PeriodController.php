<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\AccountingPeriodService;
use App\Domain\GrowFinance\Repositories\AccountingPeriodRepositoryInterface;
use App\Domain\GrowFinance\Repositories\FiscalYearRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PeriodController extends Controller
{
    public function __construct(
        private AccountingPeriodService $periodService,
        private AccountingPeriodRepositoryInterface $periodRepo,
        private FiscalYearRepositoryInterface $fiscalYearRepo,
    ) {}

    public function index(Request $request): Response
    {
        $businessId = $request->user()->id;
        $fiscalYears = $this->fiscalYearRepo->findByBusiness($businessId);

        $periodsByYear = [];
        foreach ($fiscalYears as $year) {
            $periodsByYear[] = [
                'fiscal_year' => $year->toArray(),
                'periods' => array_map(fn($p) => $p->toArray(), $this->periodRepo->findByFiscalYear($year->id)),
            ];
        }

        $currentPeriod = $this->periodRepo->findCurrent($businessId);

        return Inertia::render('GrowFinance/Periods/Index', [
            'periodsByYear' => $periodsByYear,
            'currentPeriod' => $currentPeriod?->toArray(),
        ]);
    }

    public function create(Request $request): Response
    {
        $businessId = $request->user()->id;
        $fiscalYears = $this->fiscalYearRepo->findByBusiness($businessId);

        return Inertia::render('GrowFinance/Periods/Create', [
            'fiscalYears' => array_map(fn($y) => $y->toArray(), $fiscalYears),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $businessId = $request->user()->id;

        $validated = $request->validate([
            'label' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'generate_periods' => 'boolean',
        ]);

        $year = $this->periodService->createFiscalYear(
            businessId: $businessId,
            label: $validated['label'],
            startDate: $validated['start_date'],
            endDate: $validated['end_date'],
            generatePeriods: $validated['generate_periods'] ?? true,
        );

        return redirect()->route('growfinance.periods.index')
            ->with('success', 'Fiscal year created.');
    }

    public function close(Request $request, int $id): RedirectResponse
    {
        try {
            $this->periodService->closePeriod($id, $request->user()->id);
            return redirect()->route('growfinance.periods.index')
                ->with('success', 'Period closed successfully.');
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reopen(Request $request, int $id): RedirectResponse
    {
        try {
            $this->periodService->reopenPeriod($id);
            return redirect()->route('growfinance.periods.index')
                ->with('success', 'Period reopened.');
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
