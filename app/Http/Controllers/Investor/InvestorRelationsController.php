<?php

namespace App\Http\Controllers\Investor;

use App\Domain\Investor\Services\InvestorRelationsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvestorRelationsController extends Controller
{
    public function __construct(
        private readonly InvestorRelationsService $relationsService
    ) {}

    /**
     * Display investor relations hub
     */
    public function index(Request $request)
    {
        $investorId = $request->user()->id;

        // Get investor relations data
        $quarterlyReports = $this->relationsService->getQuarterlyReports($investorId);
        $boardUpdates = $this->relationsService->getBoardUpdates($investorId);
        $upcomingMeetings = $this->relationsService->getUpcomingMeetings($investorId);

        return Inertia::render('Investor/InvestorRelations', [
            'quarterlyReports' => $quarterlyReports,
            'boardUpdates' => $boardUpdates,
            'upcomingMeetings' => $upcomingMeetings,
        ]);
    }

    /**
     * Download quarterly report
     */
    public function downloadReport(Request $request, int $reportId)
    {
        return $this->relationsService->downloadQuarterlyReport($reportId);
    }
}
