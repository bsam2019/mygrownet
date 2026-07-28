<?php

declare(strict_types=1);

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\ReportingEngine;
use App\Domain\GrowFinance\Services\ScenarioModellingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScenarioController extends Controller
{
    public function __construct(
        private ScenarioModellingService $scenarioService,
        private ReportingEngine $reportingEngine,
    ) {}

    public function index(Request $request)
    {
        $businessId = $request->user()->id;
        $savedScenarios = $this->scenarioService->listScenarios($businessId);
        $from = $request->input('from', date('Y-m-d', strtotime('first day of this month')));
        $to = $request->input('to', date('Y-m-d'));

        $basePnl = $this->reportingEngine->getProfitAndLoss($businessId, new \DateTimeImmutable($from), new \DateTimeImmutable($to));

        $accounts = $this->getAccountsList($businessId);

        return Inertia::render('GrowFinance/Reports/ScenarioModelling', [
            'savedScenarios' => $savedScenarios,
            'basePnl' => $basePnl,
            'accounts' => $accounts,
            'filters' => compact('from', 'to'),
        ]);
    }

    public function simulate(Request $request)
    {
        $businessId = $request->user()->id;
        $type = $request->input('type', 'revenue');
        $from = new \DateTimeImmutable($request->input('from', date('Y-m-d', strtotime('first day of this month'))));
        $to = new \DateTimeImmutable($request->input('to', date('Y-m-d')));

        $result = match ($type) {
            'revenue' => $this->scenarioService->modelRevenueChange(
                $businessId, (float)$request->input('percentage', 10), $from, $to
            ),
            'expense' => $this->scenarioService->modelExpenseChange(
                $businessId, (float)$request->input('percentage', -10), $from, $to
            ),
            'account' => $this->scenarioService->modelAccountChange(
                $businessId, $request->input('account_code', ''), (float)$request->input('new_amount', 0), $from, $to
            ),
            'combined' => $this->scenarioService->modelCombined(
                $businessId, $request->input('scenarios', []), $from, $to
            ),
            default => throw new \InvalidArgumentException("Unknown scenario type: {$type}"),
        };

        return response()->json($result);
    }

    public function save(Request $request)
    {
        $businessId = $request->user()->id;
        $this->scenarioService->saveScenario(
            $businessId,
            $request->input('name', 'Unnamed Scenario'),
            $request->input('parameters', []),
            $request->input('results', []),
        );
        return redirect()->back()->with('success', 'Scenario saved');
    }

    public function show(Request $request, int $id)
    {
        $businessId = $request->user()->id;
        $scenario = $this->scenarioService->getScenario($id);
        if (!$scenario || $scenario['business_id'] !== $businessId) {
            return redirect()->back()->with('error', 'Scenario not found');
        }

        $savedScenarios = $this->scenarioService->listScenarios($businessId);
        $from = $request->input('from', date('Y-m-d', strtotime('first day of this month')));
        $to = $request->input('to', date('Y-m-d'));

        $basePnl = $this->reportingEngine->getProfitAndLoss($businessId, new \DateTimeImmutable($from), new \DateTimeImmutable($to));
        $accounts = $this->getAccountsList($businessId);

        return Inertia::render('GrowFinance/Reports/ScenarioModelling', [
            'activeScenario' => $scenario,
            'savedScenarios' => $savedScenarios,
            'basePnl' => $basePnl,
            'accounts' => $accounts,
            'filters' => compact('from', 'to'),
        ]);
    }

    private function getAccountsList(int $businessId): array
    {
        $accounts = $this->scenarioService->getAccountsForModelling($businessId);
        return array_map(fn($a) => [
            'code' => $a->code,
            'name' => $a->name,
            'type' => $a->type->value,
        ], $accounts);
    }
}
