<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Domain\Investor\Repositories\InvestorAccountRepositoryInterface;
use App\Domain\Investor\Repositories\InvestmentRoundRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class InvestorPortalController extends Controller
{
    public function __construct(
        private readonly InvestorAccountRepositoryInterface $accountRepository,
        private readonly InvestmentRoundRepositoryInterface $roundRepository
    ) {}

    /**
     * Show investor login page
     */
    public function showLogin()
    {
        return Inertia::render('Investor/Login');
    }

    /**
     * Handle investor login
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'access_code' => 'required|string',
        ]);

        // Find investor by email
        $investor = $this->accountRepository->findByEmail($validated['email']);

        if (!$investor) {
            return back()->withErrors([
                'email' => 'No investor account found with this email.',
            ]);
        }

        // For now, use a simple access code system
        // In production, you'd want proper password hashing
        $expectedCode = $this->generateAccessCode($investor->getEmail(), $investor->getId());

        if ($validated['access_code'] !== $expectedCode) {
            return back()->withErrors([
                'access_code' => 'Invalid access code.',
            ]);
        }

        // Store investor ID in session
        session(['investor_id' => $investor->getId()]);
        session(['investor_email' => $investor->getEmail()]);

        return redirect()->route('investor.dashboard');
    }

    /**
     * Show investor dashboard
     */
    public function dashboard()
    {
        $investorId = session('investor_id');
        
        if (!$investorId) {
            return redirect()->route('investor.login');
        }

        $investor = $this->accountRepository->findById($investorId);

        if (!$investor) {
            session()->forget(['investor_id', 'investor_email']);
            return redirect()->route('investor.login');
        }

        // Get investment round details
        $round = $this->roundRepository->findById($investor->getInvestmentRoundId());

        // Calculate investment metrics
        $investmentAmount = $investor->getInvestmentAmount();
        $equityPercentage = $investor->getEquityPercentage();
        $currentValuation = $round ? $round->getValuation() : 0;
        $currentValue = $currentValuation * ($equityPercentage / 100);
        $roi = $investmentAmount > 0 ? (($currentValue - $investmentAmount) / $investmentAmount) * 100 : 0;
        
        // Calculate holding period
        $investmentDate = $investor->getInvestmentDate();
        $holdingDays = max(0, now()->diffInDays($investmentDate, false));
        $holdingMonths = max(0, now()->diffInMonths($investmentDate, false));

        // Get platform metrics
        try {
            $metricsService = app(\App\Domain\Investor\Services\PlatformMetricsService::class);
            $platformMetrics = $metricsService->getPublicMetrics();
        } catch (\Exception $e) {
            \Log::error('Platform Metrics Error: ' . $e->getMessage());
            $platformMetrics = [
                'totalMembers' => 0,
                'monthlyRevenue' => 0,
                'activeRate' => 0,
                'retention' => 0,
                'revenueGrowth' => ['labels' => [], 'data' => []],
            ];
        }

        // Get all investors for round stats
        try {
            $allInvestors = $this->accountRepository->findByInvestmentRound($investor->getInvestmentRoundId());
            $totalInvestors = count($allInvestors);
            $totalRaised = array_sum(array_map(fn($inv) => $inv->getInvestmentAmount(), $allInvestors));
        } catch (\Exception $e) {
            \Log::error('Investor Stats Error: ' . $e->getMessage());
            $totalInvestors = 1;
            $totalRaised = $investmentAmount;
        }

        // Build the data array
        $data = [
            'investor' => [
                'id' => $investor->getId(),
                'name' => $investor->getName(),
                'email' => $investor->getEmail(),
                'investment_amount' => $investor->getInvestmentAmount(),
                'investment_date' => $investor->getInvestmentDate()->format('Y-m-d'),
                'investment_date_formatted' => $investor->getInvestmentDate()->format('F j, Y'),
                'status' => $investor->getStatus()->value(),
                'status_label' => $investor->getStatus()->label(),
                'equity_percentage' => $investor->getEquityPercentage(),
                'holding_days' => $holdingDays,
                'holding_months' => $holdingMonths,
            ],
            'investmentMetrics' => [
                'initial_investment' => $investmentAmount,
                'current_value' => round($currentValue, 2),
                'roi_percentage' => round($roi, 2),
                'equity_percentage' => $equityPercentage,
                'valuation_at_investment' => $round ? $round->getValuation() : 0,
                'current_valuation' => $currentValuation,
            ],
            'round' => $round ? [
                'id' => $round->getId(),
                'name' => $round->getName(),
                'valuation' => $round->getValuation(),
                'goal_amount' => $round->getGoalAmount(),
                'raised_amount' => $round->getRaisedAmount(),
                'progress_percentage' => $round->getProgressPercentage(),
                'total_investors' => $totalInvestors,
                'total_raised' => $totalRaised,
                'status' => $round->getStatus()->value(),
                'status_label' => $round->getStatus()->label(),
            ] : null,
            'platformMetrics' => [
                'total_members' => $platformMetrics['totalMembers'],
                'monthly_revenue' => $platformMetrics['monthlyRevenue'],
                'active_rate' => $platformMetrics['activeRate'],
                'retention_rate' => $platformMetrics['retention'],
                'revenue_growth' => $platformMetrics['revenueGrowth'],
            ],
        ];

        // Debug: Log the complete data
        \Log::info('Investor Dashboard Complete Data', ['keys' => array_keys($data)]);

        return Inertia::render('Investor/Dashboard', $data);
    }

    /**
     * Show investor documents
     */
    public function documents()
    {
        $investorId = session('investor_id');
        
        if (!$investorId) {
            return redirect()->route('investor.login');
        }

        $investor = $this->accountRepository->findById($investorId);

        return Inertia::render('Investor/Documents', [
            'investor' => [
                'name' => $investor->getName(),
                'email' => $investor->getEmail(),
            ],
        ]);
    }

    /**
     * Logout investor
     */
    public function logout()
    {
        session()->forget(['investor_id', 'investor_email']);
        return redirect()->route('investor.login')->with('success', 'Logged out successfully');
    }

    /**
     * Generate access code for investor
     * In production, use proper password hashing
     */
    private function generateAccessCode(string $email, int $id): string
    {
        // Simple access code: first 4 chars of email + investor ID
        // In production, use proper password system
        return strtoupper(substr($email, 0, 4)) . $id;
    }

    /**
     * Send access code to investor email
     * This would be called by admin when creating investor account
     */
    public function sendAccessCode(int $investorId)
    {
        $investor = $this->accountRepository->findById($investorId);

        if (!$investor) {
            return back()->with('error', 'Investor not found');
        }

        $accessCode = $this->generateAccessCode($investor->getEmail(), $investor->getId());

        // TODO: Send email with access code
        // Mail::to($investor->getEmail())->send(new InvestorAccessCode($accessCode));

        return back()->with('success', "Access code sent to {$investor->getEmail()}");
    }
}
