<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\ProfitabilityService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DateTimeImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfitabilityController extends Controller
{
    public function __construct(
        private ProfitabilityService $profitabilityService,
    ) {}

    public function index(Request $request): Response
    {
        $businessId = $request->user()->id;
        $from = new DateTimeImmutable($request->get('from', Carbon::now()->startOfMonth()->format('Y-m-d')));
        $to = new DateTimeImmutable($request->get('to', Carbon::now()->endOfMonth()->format('Y-m-d')));
        $dimension = $request->get('dimension');

        $byGroup = $this->profitabilityService->getProfitabilityByGroup($businessId, $from, $to);
        $byDimension = $dimension
            ? $this->profitabilityService->getProfitabilityByDimension($businessId, $dimension, $from, $to)
            : [];

        return Inertia::render('GrowFinance/Reports/Profitability', [
            'byGroup' => $byGroup,
            'byDimension' => $byDimension,
            'filters' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
                'dimension' => $dimension,
            ],
        ]);
    }
}
