<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\FinancialRatioService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DateTimeImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FinancialRatioController extends Controller
{
    public function __construct(
        private FinancialRatioService $ratioService,
    ) {}

    public function index(Request $request): Response
    {
        $businessId = $request->user()->id;
        $from = new DateTimeImmutable($request->get('from', Carbon::now()->startOfMonth()->format('Y-m-d')));
        $to = new DateTimeImmutable($request->get('to', Carbon::now()->endOfMonth()->format('Y-m-d')));
        $asOf = $request->get('as_of')
            ? new DateTimeImmutable($request->get('as_of'))
            : null;

        $ratios = $this->ratioService->getAllRatios($businessId, $from, $to, $asOf);

        return Inertia::render('GrowFinance/Reports/FinancialRatios', [
            'ratios' => $ratios,
            'filters' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
                'as_of' => $asOf ? $asOf->format('Y-m-d') : $to->format('Y-m-d'),
            ],
        ]);
    }

    public function trend(Request $request): \Illuminate\Http\JsonResponse
    {
        $businessId = $request->user()->id;
        $months = (int) $request->get('months', 12);

        $trend = $this->ratioService->getTrend($businessId, $months);

        return response()->json(['trend' => $trend]);
    }
}
