<?php

namespace App\Http\Controllers\Investor;

use App\Domain\Investor\Services\DividendManagementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DividendsController extends Controller
{
    public function __construct(
        private readonly DividendManagementService $dividendService
    ) {}

    /**
     * Display dividend dashboard
     */
    public function index(Request $request)
    {
        $investorId = $request->user()->id;

        // Get dividend data
        $dividends = $this->dividendService->getInvestorDividends($investorId);
        $upcomingDistributions = $this->dividendService->getUpcomingDistributions($investorId);
        $totalEarned = $this->dividendService->getTotalDividendsEarned($investorId);

        return Inertia::render('Investor/Dividends', [
            'dividends' => $dividends,
            'upcomingDistributions' => $upcomingDistributions,
            'totalEarned' => $totalEarned,
        ]);
    }

    /**
     * Get dividend history
     */
    public function history(Request $request)
    {
        $investorId = $request->user()->id;
        $year = $request->input('year', date('Y'));

        $history = $this->dividendService->getDividendHistory($investorId, $year);

        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }
}
