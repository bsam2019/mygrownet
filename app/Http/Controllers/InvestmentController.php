<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\InvestmentOpportunity;
use App\Models\User;
use App\Services\InvestmentMetricsService;
use App\Services\ProfitCalculationService;
use App\Services\ReferralService;
use App\Http\Requests\InvestmentCreateRequest;
use App\Http\Requests\TierUpgradeRequest;
use App\Http\Requests\WithdrawalRequest as WithdrawalFormRequest;
use App\Domain\Investment\Services\InvestmentTierService;
use App\Domain\Financial\Services\WithdrawalPolicyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Carbon\Carbon;

class InvestmentController extends Controller
{
    protected $investmentMetricsService;
    protected $profitCalculationService;
    protected $referralService;
    protected $investmentTierService;
    protected $withdrawalPolicyService;

    public function __construct(
        InvestmentMetricsService $investmentMetricsService,
        ProfitCalculationService $profitCalculationService,
        ReferralService $referralService,
        InvestmentTierService $investmentTierService,
        WithdrawalPolicyService $withdrawalPolicyService
    ) {
        $this->investmentMetricsService = $investmentMetricsService;
        $this->profitCalculationService = $profitCalculationService;
        $this->referralService = $referralService;
        $this->investmentTierService = $investmentTierService;
        $this->withdrawalPolicyService = $withdrawalPolicyService;
    }

    public function index(Request $request)
    {
        $query = Investment::with(['user', 'tier'])
            ->when($request->user()->hasRole('admin'), function ($q) {
                return $q; // Admin sees all investments
            }, function ($q) use ($request) {
                return $q->where('user_id', $request->user()->id); // Users see only their investments
            });

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tier')) {
            $query->whereHas('tier', function ($q) use ($request) {
                $q->where('name', $request->tier);
            });
        }

        if ($request->filled('date_range')) {
            $query->filterByDateRange($request->date_range);
        }

        $investments = $query->latest()->paginate(10)->withQueryString();

        // Get summary statistics
        $stats = $this->getInvestmentStats($request->user());

        return Inertia::render('Investments/Index', [
            'investments' => $investments,
            'stats' => $stats,
            'filters' => $request->only(['status', 'tier', 'date_range']),
            'tiers' => InvestmentTier::active()->ordered()->get(['name', 'minimum_investment']),
        ]);
    }

    public function create(Request $request)
    {
        $tiers = InvestmentTier::active()->ordered()->get();
        $user = $request->user();
        
        // Get user's current tier and upgrade possibilities
        $currentTier = $user->getCurrentInvestmentTier();
        $tierUpgradeInfo = $currentTier ? $currentTier->getUpgradeRequirements($user) : null;

        return Inertia::render('Investments/Create', [
            'tiers' => $tiers->map(function ($tier) use ($user) {
                return [
                    'id' => $tier->id,
                    'name' => $tier->name,
                    'minimum_investment' => $tier->minimum_investment,
                    'fixed_profit_rate' => $tier->fixed_profit_rate,
                    'benefits' => $tier->getTierSpecificBenefits(),
                    'can_invest' => $user->total_investment_amount >= $tier->minimum_investment || 
                                   $tier->name === 'Basic', // Anyone can start with Basic
                ];
            }),
            'currentTier' => $currentTier,
            'tierUpgradeInfo' => $tierUpgradeInfo,
            'userStats' => [
                'total_investment' => $user->total_investment_amount ?? 0,
                'total_earnings' => $user->total_profit_earnings ?? 0,
                'referral_code' => $user->referral_code,
            ],
        ]);
    }

    public function store(InvestmentCreateRequest $request)
    {
        $validated = $request->validated();
        $tier = InvestmentTier::findOrFail($validated['tier_id']);
        $user = $request->user();

        // Validate tier eligibility
        if ($validated['amount'] < $tier->minimum_investment) {
            return back()->withErrors([
                'amount' => "Minimum investment for {$tier->name} tier is K{$tier->minimum_investment}"
            ])->withInput();
        }

        DB::beginTransaction();
        try {
            // Create investment
            $investment = Investment::create([
                'user_id' => $user->id,
                'tier_id' => $tier->id,
                'amount' => $validated['amount'],
                'status' => 'pending',
                'investment_date' => now(),
                'lock_in_period_end' => now()->addYear(), // 12-month lock-in
                'payment_proof' => $request->file('payment_proof')->store('payment_proofs', 'public'),
                'payment_method' => $validated['payment_method'],
            ]);

            // Update user's total investment amount
            $user->increment('total_investment_amount', $validated['amount']);

            // Check for tier upgrade using domain service
            $this->investmentTierService->checkAndProcessTierUpgrade($user);

            // Process referral if referrer code provided
            if (!empty($validated['referrer_code']) && !$user->referrer_id) {
                $referrer = User::where('referral_code', $validated['referrer_code'])->first();
                if ($referrer && $referrer->id !== $user->id) {
                    $user->update(['referrer_id' => $referrer->id]);
                    $referrer->increment('referral_count');
                    $referrer->update(['last_referral_at' => now()]);
                }
            }

            DB::commit();

            return redirect()->route('investments.show', $investment)
                ->with('success', 'Investment created successfully and is pending approval.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create investment. Please try again.'])->withInput();
        }
    }

    public function show(Investment $investment)
    {
        // Ensure user can only view their own investments (unless admin)
        if (!auth()->user()->hasRole('admin') && $investment->user_id !== auth()->id()) {
            abort(403);
        }

        // VBIF-specific investment details
        $metrics = $investment->getInvestmentPerformanceMetrics();
        $withdrawalInfo = $investment->canWithdraw();
        $projections = $investment->projectFutureValue(12);
        $penalties = $investment->calculateWithdrawalPenalties();
        $lockInStatus = $investment->getLockInPeriodRemaining();
        $withdrawalScenarios = $investment->simulateWithdrawalScenarios();
        
        return Inertia::render('Investments/Show', [
            'investment' => $investment->load(['user', 'tier', 'referralCommissions']),
            'metrics' => $metrics,
            'withdrawalInfo' => $withdrawalInfo,
            'projections' => $projections,
            'penalties' => $penalties,
            'lockInStatus' => $lockInStatus,
            'withdrawalScenarios' => $withdrawalScenarios,
            'tierBenefits' => $investment->tier?->getTierSpecificBenefits(),
        ]);
    }

    public function history(Request $request)
    {
        $user = $request->user();
        
        $query = Investment::where('user_id', $user->id)
            ->with(['tier', 'referralCommissions', 'profitShares']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tier')) {
            $query->whereHas('tier', function ($q) use ($request) {
                $q->where('name', $request->tier);
            });
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->date_from));
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->date_to));
        }

        $investments = $query->latest()->paginate(15)->withQueryString();

        // Calculate performance metrics
        $performanceMetrics = $this->calculatePortfolioPerformance($user);

        return Inertia::render('Investments/History', [
            'investments' => $investments,
            'performanceMetrics' => $performanceMetrics,
            'filters' => $request->only(['status', 'tier', 'date_from', 'date_to']),
            'tiers' => InvestmentTier::active()->get(['name']),
        ]);
    }

    public function performance(Request $request)
    {
        $user = $request->user();
        $period = $request->get('period', 'month');
        
        // Get performance metrics
        $metrics = $this->investmentMetricsService->generateMetrics($period);
        $userMetrics = $this->calculateUserPerformanceMetrics($user, $period);
        $portfolioBreakdown = $this->getPortfolioBreakdown($user);
        
        return Inertia::render('Investments/Performance', [
            'metrics' => $metrics,
            'userMetrics' => $userMetrics,
            'portfolioBreakdown' => $portfolioBreakdown,
            'period' => $period,
        ]);
    }

    public function requestTierUpgrade(TierUpgradeRequest $request)
    {

        $user = $request->user();
        $targetTier = InvestmentTier::findOrFail($request->target_tier_id);
        $currentTier = $user->getCurrentInvestmentTier();

        // Validate upgrade eligibility
        $totalAfterUpgrade = $user->total_investment_amount + $request->additional_amount;
        
        if ($totalAfterUpgrade < $targetTier->minimum_investment) {
            return back()->withErrors([
                'additional_amount' => "Total investment amount must be at least K{$targetTier->minimum_investment} for {$targetTier->name} tier"
            ]);
        }

        if ($currentTier && $targetTier->minimum_investment <= $currentTier->minimum_investment) {
            return back()->withErrors([
                'target_tier_id' => 'You can only upgrade to a higher tier'
            ]);
        }

        DB::beginTransaction();
        try {
            // Create upgrade investment
            $investment = Investment::create([
                'user_id' => $user->id,
                'tier_id' => $targetTier->id,
                'amount' => $request->additional_amount,
                'status' => 'pending',
                'investment_date' => now(),
                'lock_in_period_end' => now()->addYear(),
                'is_tier_upgrade' => true,
                'previous_tier_id' => $currentTier?->id,
            ]);

            DB::commit();

            return redirect()->route('investments.show', $investment)
                ->with('success', 'Tier upgrade request submitted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to process tier upgrade request.']);
        }
    }

    public function requestWithdrawal(WithdrawalFormRequest $request, Investment $investment)
    {

        $validated = $request->validated();
        
        // Verify OTP (implement OTP verification logic)
        if (!$this->verifyOTP($request->user(), $validated['otp_code'])) {
            return back()->withErrors(['otp_code' => 'Invalid OTP code']);
        }

        $withdrawalType = $validated['withdrawal_type'];
        
        // Use domain service for withdrawal validation
        $eligibility = $this->withdrawalPolicyService->validateWithdrawal($request->user(), $validated['amount'] ?? 0);
        
        if (!$eligibility['is_eligible']) {
            return back()->withErrors([
                'withdrawal_type' => 'Withdrawal not allowed: ' . implode(', ', $eligibility['reasons'])
            ]);
        }

        $amount = $validated['amount'] ?? $investment->getWithdrawableAmount($withdrawalType);
        $penalties = $this->withdrawalPolicyService->calculatePenalties($investment);

        DB::beginTransaction();
        try {
            // Create withdrawal request
            $withdrawalRequest = $investment->withdrawalRequests()->create([
                'user_id' => $investment->user_id,
                'amount' => $amount,
                'type' => $withdrawalType,
                'status' => $withdrawalType === 'emergency' ? 'pending_approval' : 'pending',
                'penalty_amount' => $penalties['total_penalty'],
                'net_amount' => $amount - $penalties['total_penalty'],
                'reason' => $request->reason,
                'requested_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('withdrawals.show', $withdrawalRequest)
                ->with('success', 'Withdrawal request submitted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to process withdrawal request.']);
        }
    }

    public function approve(Investment $investment)
    {
        $this->authorize('approve', $investment);

        DB::beginTransaction();
        try {
            $investment->update([
                'status' => 'active',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);

            // Process referral commissions if investment is approved
            if ($investment->user->referrer_id) {
                $this->referralService->processReferralCommission($investment);
            }

            // Check for automatic tier upgrade
            $this->checkAndProcessTierUpgrade($investment->user);

            DB::commit();

            return back()->with('success', 'Investment approved successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to approve investment.']);
        }
    }

    public function reject(Investment $investment)
    {
        $this->authorize('reject', $investment);

        $investment->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
        ]);

        return back()->with('success', 'Investment rejected.');
    }

    // Helper methods

    protected function getInvestmentStats(User $user): array
    {
        $query = Investment::query();
        
        if (!$user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        return [
            'total_investments' => $query->count(),
            'active_investments' => $query->where('status', 'active')->count(),
            'pending_investments' => $query->where('status', 'pending')->count(),
            'total_amount' => $query->sum('amount'),
            'total_current_value' => $query->where('status', 'active')->get()->sum('current_value'),
            'average_roi' => $query->where('status', 'active')->avg('roi') ?? 0,
        ];
    }

    protected function checkAndProcessTierUpgrade(User $user): void
    {
        $currentTier = $user->getCurrentInvestmentTier();
        $eligibleTier = InvestmentTier::where('minimum_investment', '<=', $user->total_investment_amount)
            ->orderBy('minimum_investment', 'desc')
            ->first();

        if ($eligibleTier && (!$currentTier || $eligibleTier->minimum_investment > $currentTier->minimum_investment)) {
            $user->update([
                'current_investment_tier' => $eligibleTier->name,
                'tier_upgraded_at' => now(),
            ]);

            // Add to tier history
            $tierHistory = $user->tier_history ?? [];
            $tierHistory[] = [
                'tier_id' => $eligibleTier->id,
                'tier_name' => $eligibleTier->name,
                'upgraded_at' => now()->toISOString(),
                'total_investment_at_upgrade' => $user->total_investment_amount,
            ];
            $user->update(['tier_history' => $tierHistory]);
        }
    }

    protected function calculatePortfolioPerformance(User $user): array
    {
        $investments = $user->investments()->where('status', 'active')->get();
        
        if ($investments->isEmpty()) {
            return [
                'total_invested' => 0,
                'current_value' => 0,
                'total_profit' => 0,
                'average_roi' => 0,
                'best_performing' => null,
                'worst_performing' => null,
            ];
        }

        $totalInvested = $investments->sum('amount');
        $currentValue = $investments->sum('current_value');
        $totalProfit = $currentValue - $totalInvested;
        $averageRoi = $investments->avg('roi');

        return [
            'total_invested' => $totalInvested,
            'current_value' => $currentValue,
            'total_profit' => $totalProfit,
            'average_roi' => $averageRoi,
            'best_performing' => $investments->sortByDesc('roi')->first(),
            'worst_performing' => $investments->sortBy('roi')->first(),
        ];
    }

    protected function calculateUserPerformanceMetrics(User $user, string $period): array
    {
        // Implementation for user-specific performance metrics
        return [
            'period_return' => 0, // Calculate based on period
            'volatility' => 0,
            'sharpe_ratio' => 0,
            'max_drawdown' => 0,
        ];
    }

    protected function getPortfolioBreakdown(User $user): array
    {
        $investments = $user->investments()->where('status', 'active')->with('tier')->get();
        
        $breakdown = $investments->groupBy('tier.name')->map(function ($tierInvestments, $tierName) {
            return [
                'tier' => $tierName,
                'count' => $tierInvestments->count(),
                'total_amount' => $tierInvestments->sum('amount'),
                'current_value' => $tierInvestments->sum('current_value'),
                'percentage' => 0, // Will be calculated after grouping
            ];
        });

        $totalValue = $breakdown->sum('current_value');
        
        return $breakdown->map(function ($item) use ($totalValue) {
            $item['percentage'] = $totalValue > 0 ? ($item['current_value'] / $totalValue) * 100 : 0;
            return $item;
        })->values();
    }

    protected function verifyOTP(User $user, string $otpCode): bool
    {
        // Implement OTP verification logic
        // This is a placeholder - implement actual OTP verification
        return true;
    }

    /**
     * Display investment opportunities
     */
    public function opportunities()
    {
        $opportunities = InvestmentOpportunity::where('status', 'active')
            ->with('category')
            ->latest()
            ->paginate(10);

        return Inertia::render('Investment/Opportunities/Index', [
            'opportunities' => $opportunities
        ]);
    }

    /**
     * Show specific investment opportunity
     */
    public function showOpportunity(InvestmentOpportunity $opportunity)
    {
        return Inertia::render('Investment/Opportunities/Show', [
            'opportunity' => $opportunity->load('category')
        ]);
    }

    /**
     * Display user portfolio
     */
    public function portfolio()
    {
        $user = auth()->user();
        
        // Get comprehensive portfolio data
        $activeInvestments = $user->investments()->where('status', 'active')->with('tier')->get();
        $totalInvested = $activeInvestments->sum('amount');
        $currentValue = $activeInvestments->sum(function ($inv) { return $inv->getCurrentValue(); });
        
        // Get earnings breakdown
        $earnings = $user->calculateTotalEarningsDetailed();
        
        // Get referral statistics
        $referralStats = $user->getReferralStats();
        
        // Portfolio performance metrics
        $portfolio = [
            'total_investment' => $totalInvested,
            'current_value' => $currentValue,
            'total_profit' => $currentValue - $totalInvested,
            'roi_percentage' => $totalInvested > 0 ? (($currentValue - $totalInvested) / $totalInvested) * 100 : 0,
            'total_earnings' => $earnings['total_earnings'],
            'referral_earnings' => $earnings['referral_commissions'],
            'profit_earnings' => $earnings['profit_shares'],
            'matrix_earnings' => $earnings['matrix_commissions'],
            'pending_earnings' => $earnings['pending_earnings'],
            'active_referrals' => $referralStats['active_referrals'],
            'total_referrals' => $referralStats['total_referrals'],
            'investment_count' => $activeInvestments->count(),
            'tier_distribution' => $activeInvestments->groupBy('tier.name')->map->count(),
            'average_investment' => $activeInvestments->count() > 0 ? $totalInvested / $activeInvestments->count() : 0
        ];

        // Get investment performance details
        $investmentDetails = $activeInvestments->map(function ($investment) {
            return [
                'id' => $investment->id,
                'amount' => $investment->amount,
                'current_value' => $investment->getCurrentValue(),
                'profit' => $investment->getCurrentValue() - $investment->amount,
                'roi' => $investment->getRoiAttribute(),
                'tier' => $investment->tier?->name,
                'investment_date' => $investment->investment_date,
                'lock_in_status' => $investment->getLockInPeriodRemaining(),
                'withdrawal_eligibility' => $investment->canWithdraw(),
                'performance_metrics' => $investment->getInvestmentPerformanceMetrics()
            ];
        });

        // Get recent transactions
        $recentTransactions = $user->transactions()
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('Investors/Portfolio/Index', [
            'portfolio' => $portfolio,
            'investments' => $investmentDetails,
            'recent_transactions' => $recentTransactions,
            'tier_information' => $user->checkTierUpgradeEligibility(),
            'matrix_information' => [
                'position' => $user->getMatrixPosition(),
                'downline_counts' => $user->getMatrixDownlineCount(),
                'structure' => $user->buildMatrixStructure(2) // Limit to 2 levels for portfolio view
            ]
        ]);
    }
}
