<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\ThreeWayMatchingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MatchingController extends Controller
{
    public function __construct(
        private ThreeWayMatchingService $matchingService,
    ) {}

    public function index(Request $request): Response
    {
        $businessId = $request->user()->id;

        $from = $request->input('from')
            ? new \DateTimeImmutable($request->input('from'))
            : null;
        $to = $request->input('to')
            ? new \DateTimeImmutable($request->input('to'))
            : null;

        $results = $this->matchingService->runMatching($businessId, $from, $to);

        return Inertia::render('GrowFinance/Reports/ThreeWayMatching', [
            'results' => $results,
            'dateRange' => [
                'from' => $from?->format('Y-m-d') ?? (new \DateTimeImmutable('-90 days'))->format('Y-m-d'),
                'to' => $to?->format('Y-m-d') ?? (new \DateTimeImmutable('now'))->format('Y-m-d'),
            ],
        ]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_id' => 'required|integer',
            'expense_id' => 'required|integer',
        ]);

        $this->matchingService->confirmMatch(
            (int) $validated['invoice_id'],
            (int) $validated['expense_id'],
        );

        return redirect()->back()->with('success', 'Match confirmed');
    }
}
