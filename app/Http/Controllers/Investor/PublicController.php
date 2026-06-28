<?php

namespace App\Http\Controllers\Investor;

use App\Domain\Investor\Services\InvestorInquiryService;
use App\Domain\Investor\Services\PlatformMetricsService;
use App\Domain\Investor\Repositories\InvestmentRoundRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Public Investor Controller
 * 
 * Handles public-facing investor pages and inquiry submissions
 */
class PublicController extends Controller
{
    public function __construct(
        private readonly InvestorInquiryService $inquiryService,
        private readonly PlatformMetricsService $metricsService,
        private readonly InvestmentRoundRepositoryInterface $investmentRoundRepository
    ) {}

    /**
     * Display the public investor landing page
     */
    public function index(): Response
    {
        $featuredRound = $this->investmentRoundRepository->getFeaturedRound();
        
        return Inertia::render('Investor/PublicLanding', [
            'metrics' => $this->metricsService->getPublicMetrics(),
            'investmentRound' => $featuredRound ? $this->formatInvestmentRound($featuredRound) : null,
        ]);
    }
    
    private function formatInvestmentRound($round): array
    {
        return [
            'id' => $round->getId(),
            'name' => $round->getName(),
            'description' => $round->getDescription(),
            'goalAmount' => $round->getGoalAmount(),
            'raisedAmount' => $round->getRaisedAmount(),
            'progressPercentage' => $round->getProgressPercentage(),
            'minimumInvestment' => $round->getMinimumInvestment(),
            'valuation' => $round->getValuation(),
            'equityPercentage' => $round->getEquityPercentage(),
            'expectedRoi' => $round->getExpectedRoi(),
            'useOfFunds' => $round->getUseOfFunds(),
        ];
    }

    /**
     * Handle investor inquiry submission
     */
    public function submitInquiry(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'investmentRange' => 'required|string|in:25-50,50-100,100-250,250+',
            'message' => 'nullable|string|max:1000',
        ]);

        try {
            $inquiry = $this->inquiryService->createInquiry(
                name: $validated['name'],
                email: $validated['email'],
                phone: $validated['phone'],
                investmentRangeValue: $validated['investmentRange'],
                message: $validated['message'] ?? null
            );

            // TODO: Send notification to admin
            // TODO: Send confirmation email to investor

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your interest! We will contact you within 24 hours.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
}
