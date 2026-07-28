<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\ReportScheduleService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportScheduleController extends Controller
{
    public function __construct(
        private ReportScheduleService $scheduleService,
    ) {}

    public function index(Request $request): Response
    {
        $schedules = $this->scheduleService->getSchedules($request->user()->id);

        return Inertia::render('GrowFinance/Reports/Schedules', [
            'schedules' => $schedules,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'report_type' => 'required|in:profit_loss,balance_sheet,trial_balance,cash_flow',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly,yearly',
            'recipients' => 'required|array|min:1',
            'recipients.*.email' => 'required|email',
            'recipients.*.name' => 'required|string|max:255',
            'format' => 'sometimes|in:pdf,csv,xlsx',
        ]);

        $this->scheduleService->create(
            $request->user()->id,
            $validated['name'],
            $validated['report_type'],
            $validated['frequency'],
            $validated['recipients'],
            $validated['format'] ?? 'pdf',
        );

        return redirect()->route('growfinance.report-schedules.index')
            ->with('success', 'Report schedule created.');
    }

    public function update(Request $request, int $schedule): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'report_type' => 'sometimes|in:profit_loss,balance_sheet,trial_balance,cash_flow',
            'frequency' => 'sometimes|in:daily,weekly,monthly,quarterly,yearly',
            'recipients' => 'sometimes|array|min:1',
            'recipients.*.email' => 'required_with:recipients|email',
            'recipients.*.name' => 'required_with:recipients|string|max:255',
            'format' => 'sometimes|in:pdf,csv,xlsx',
            'is_active' => 'sometimes|boolean',
        ]);

        $this->scheduleService->update($schedule, $validated);

        return redirect()->route('growfinance.report-schedules.index')
            ->with('success', 'Report schedule updated.');
    }

    public function destroy(Request $request, int $schedule): RedirectResponse
    {
        $this->scheduleService->delete($schedule);

        return redirect()->route('growfinance.report-schedules.index')
            ->with('success', 'Report schedule deleted.');
    }
}
