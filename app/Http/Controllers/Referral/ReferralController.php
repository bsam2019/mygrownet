<?php

namespace App\Http\Controllers\Referral;

use App\Http\Controllers\Controller;
use App\Models\ReferralCommission;
use App\Models\User;
use App\Domain\Reward\Services\ReferralMatrixService;
use App\Infrastructure\Persistence\Repositories\EloquentReferralRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ReferralController extends Controller
{
    public function __construct(
        protected ReferralMatrixService $matrixService,
        protected EloquentReferralRepository $referralRepository
    ) {}

    /**
     * Display referral dashboard with tree visualization
     */
    public function index(): Response
    {
        $user = auth()->user();
        
        // Get comprehensive referral statistics
        $referralStats = $this->referralRepository->getReferralStatistics($user);
        
        // Get matrix visualization data - build directly from referrer relationships
        $matrixData = $this->buildSimpleMatrixVisualization($user);
        
        // Get all direct referrals (team members) with their details
        $teamMembers = $this->referralRepository->getDirectReferrals($user)
            ->map(function($member) {
                // A member is truly active only if they have an active subscription (paid registration)
                $isActive = $member->hasActiveSubscription();
                
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'phone' => $member->phone ?? 'N/A',
                    'email' => $member->email,
                    'status' => $member->status,
                    'joined_at' => $member->created_at->format('M d, Y'),
                    'is_active' => $isActive,
                ];
            });
        
        // Get recent referrals with investment data
        $recentReferrals = $this->referralRepository->getDirectReferrals($user)
            ->take(10);
        
        // Get recent commissions
        $recentCommissions = $this->referralRepository->getReferralCommissions($user)
            ->take(10);

        // Get earnings breakdown
        $earningsBreakdown = $this->getEarningsBreakdown($user);
        
        // Get 7-level network statistics
        $networkStats = $this->get7LevelNetworkStats($user);
        
        // Get matrix performance metrics (for backward compatibility)
        $matrixPerformance = [
            'level_1_count' => $networkStats['level_1_count'],
            'level_2_count' => $networkStats['level_2_count'],
            'level_3_count' => $networkStats['level_3_count'],
            'level_4_count' => $networkStats['level_4_count'] ?? 0,
            'level_5_count' => $networkStats['level_5_count'] ?? 0,
            'level_6_count' => $networkStats['level_6_count'] ?? 0,
            'level_7_count' => $networkStats['level_7_count'] ?? 0,
            'total_earnings' => $networkStats['total_earnings'],
            'filled_positions' => $networkStats['total_network'],
            'total_positions' => 3279 // 3+9+27+81+243+729+2187
        ];
        
        // Get spillover information
        $spilloverInfo = $this->identifySpilloverOpportunities($user);
        
        // Get performance metrics
        $performance = [
            'conversion_rate' => $this->calculateConversionRate($user),
            'average_investment' => $this->calculateAverageInvestment($user),
            'retention_rate' => $this->calculateRetentionRate($user),
            'growth_rate' => $this->calculateMonthlyGrowthRate($user)
        ];
        
        // Get recent activity
        $recentActivity = $this->getRecentActivity($user);
        
        // Get tier distribution
        $tierDistribution = $this->getTierDistribution($user);
        
        // Get code and link stats
        $codeStats = $this->getCodeStats($user);
        $linkStats = $this->getLinkStats($user);
        
        // Get message templates
        $messageTemplates = $this->getMessageTemplates();

        return Inertia::render('Referrals/Index', [
            'referralStats' => $referralStats,
            'earningsBreakdown' => $earningsBreakdown,
            'performance' => $performance,
            'recentActivity' => $recentActivity,
            'tierDistribution' => $tierDistribution,
            'matrixData' => $matrixData,
            'spilloverInfo' => $spilloverInfo,
            'matrixStats' => $matrixPerformance,
            'spilloverData' => [], // Placeholder for now
            'level1Referrals' => $recentReferrals,
            'spilloverPlacements' => [], // Placeholder for now
            'spilloverHistory' => [], // Placeholder for now
            'spilloverOpportunities' => [], // Placeholder for now
            'spilloverStats' => [], // Placeholder for now
            'referralCode' => $user->referral_code ?? '',
            'referralLink' => $this->generateReferralLink($user),
            'shortLink' => null, // Placeholder for now
            'codeStats' => $codeStats,
            'linkStats' => $linkStats,
            'messageTemplates' => $messageTemplates,
            'currentUserTier' => $user->currentInvestmentTier?->name,
            'teamMembers' => $teamMembers, // Add team members list
            'totalTeamMembers' => $teamMembers->count(), // Add total count
        ]);
    }

    /**
     * Get referral tree visualization data
     */
    public function tree(): JsonResponse
    {
        $user = auth()->user();
        $maxLevel = request('max_level', 3);
        
        $referralTree = $this->referralRepository->buildReferralTreeWithInvestments($user, $maxLevel);
        
        return response()->json([
            'success' => true,
            'data' => [
                'tree' => $referralTree,
                'statistics' => $this->referralRepository->getReferralStatistics($user),
                'max_level' => $maxLevel
            ]
        ]);
    }

    /**
     * Get referral statistics and commission history
     */
    public function statistics(): JsonResponse
    {
        $user = auth()->user();
        
        $statistics = $this->referralRepository->getReferralStatistics($user);
        $matrixPerformance = $this->matrixService->getMatrixPerformanceMetrics($user);
        
        return response()->json([
            'success' => true,
            'data' => [
                'referral_statistics' => $statistics,
                'matrix_performance' => $matrixPerformance,
                'earnings_breakdown' => $user->calculateTotalEarningsDetailed()
            ]
        ]);
    }

    /**
     * Get commission history with filtering
     */
    public function commissions(Request $request)
    {
        $user = auth()->user();
        
        $query = $user->referralCommissions()
            ->with(['referee', 'investment']);
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $commissions = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);
        
        // For GET requests, always return Inertia response (page view)
        if ($request->isMethod('GET')) {
            return Inertia::render('Referrals/Commissions', [
                'commissions' => $commissions,
                'filters' => $request->only(['status', 'level', 'date_from', 'date_to'])
            ]);
        }
        
        // Return JSON for API requests (POST, etc.)
        return response()->json([
            'success' => true,
            'data' => $commissions
        ]);
    }

    /**
     * Generate or regenerate referral code
     */
    public function generateReferralCode()
    {
        $user = auth()->user();
        
        try {
            $referralCode = $user->generateUniqueReferralCode();
            
            $user->recordActivity(
                'referral_code_generated',
                "Generated new referral code: {$referralCode}"
            );
            
            return back()->with('success', 'Business ID generated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate Business ID: ' . $e->getMessage());
        }
    }

    /**
     * Validate referral code
     */
    public function validateReferralCode(Request $request): JsonResponse
    {
        $request->validate([
            'referral_code' => 'required|string|max:20'
        ]);
        
        $user = auth()->user();
        $isValid = $user->validateReferralCode($request->referral_code);
        
        if ($isValid) {
            $referrer = $user->getReferralByCode($request->referral_code);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'is_valid' => true,
                    'referrer' => [
                        'id' => $referrer->id,
                        'name' => $referrer->name,
                        'tier' => $referrer->currentInvestmentTier?->name
                    ]
                ]
            ]);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'is_valid' => false,
                'message' => 'Invalid referral code'
            ]
        ]);
    }

    /**
     * Get matrix position display and management data
     */
    public function matrixPosition(): JsonResponse
    {
        $user = auth()->user();
        
        $matrixData = $this->matrixService->buildMatrix($user, 3);
        $visualizationData = $this->matrixService->generateMatrixVisualizationData($user);
        
        return response()->json([
            'success' => true,
            'data' => [
                'matrix_structure' => $matrixData,
                'visualization_data' => $visualizationData,
                'position_details' => $user->getMatrixPosition(),
                'downline_counts' => $user->getMatrixDownlineCount(),
                'spillover_opportunities' => $this->identifySpilloverOpportunities($user)
            ]
        ]);
    }

    /**
     * Get matrix genealogy report
     */
    public function matrixGenealogy(Request $request)
    {
        $user = auth()->user();
        $maxLevel = request('max_level', 3);
        
        $genealogy = $this->matrixService->getMatrixGenealogyReport($user, $maxLevel);
        
        // For GET requests, always return Inertia response (page view)
        if ($request->isMethod('GET')) {
            return Inertia::render('Referrals/MatrixGenealogy', [
                'genealogy' => $genealogy,
                'maxLevel' => $maxLevel,
                'user' => $user
            ]);
        }
        
        // Return JSON for API requests (POST, etc.)
        return response()->json([
            'success' => true,
            'data' => $genealogy
        ]);
    }

    /**
     * Get referrals by specific level
     */
    public function referralsByLevel(Request $request): JsonResponse
    {
        $request->validate([
            'level' => 'required|integer|min:1|max:3'
        ]);
        
        $user = auth()->user();
        $level = $request->level;
        
        $referrals = $this->referralRepository->getReferralsByLevel($user, $level);
        
        return response()->json([
            'success' => true,
            'data' => [
                'level' => $level,
                'referrals' => $referrals,
                'count' => $referrals->count(),
                'total_investment' => $referrals->sum('total_investment_amount'),
                'active_count' => $referrals->filter(function($referral) {
                    return $referral->investments()->where('status', 'active')->exists();
                })->count()
            ]
        ]);
    }

    /**
     * Calculate potential commission for investment amount
     */
    public function calculateCommission(Request $request): JsonResponse
    {
        $request->validate([
            'investment_amount' => 'required|numeric|min:500',
            'tier_id' => 'nullable|exists:investment_tiers,id'
        ]);
        
        $user = auth()->user();
        $investmentAmount = $request->investment_amount;
        
        // Get user's current tier or specified tier
        $tier = $request->tier_id 
            ? \App\Models\InvestmentTier::find($request->tier_id)
            : $user->currentInvestmentTier;
        
        if (!$tier) {
            return response()->json([
                'success' => false,
                'message' => 'No investment tier found'
            ], 400);
        }
        
        // Calculate commissions for all levels
        $commissions = [];
        for ($level = 1; $level <= 3; $level++) {
            if ($tier->isEligibleForReferralLevel($level)) {
                $commissions["level_{$level}"] = [
                    'rate' => $tier->getReferralRateForLevel($level),
                    'amount' => $tier->calculateMultiLevelReferralCommission($investmentAmount, $level),
                    'eligible' => true
                ];
            } else {
                $commissions["level_{$level}"] = [
                    'rate' => 0,
                    'amount' => 0,
                    'eligible' => false
                ];
            }
        }
        
        // Calculate matrix commissions
        $matrixCommissions = [];
        if ($user->getMatrixPosition()) {
            $matrixStructure = $tier->getMatrixCommissionStructure();
            foreach ($matrixStructure as $level => $config) {
                $matrixCommissions[$level] = [
                    'per_position' => ($investmentAmount * $config['effective_rate']) / 100,
                    'max_positions' => $config['positions'],
                    'max_earnings' => (($investmentAmount * $config['effective_rate']) / 100) * $config['positions']
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'investment_amount' => $investmentAmount,
                'tier' => $tier->name,
                'referral_commissions' => $commissions,
                'matrix_commissions' => $matrixCommissions,
                'total_potential_direct' => array_sum(array_column($commissions, 'amount')),
                'has_matrix_position' => $user->getMatrixPosition() !== null
            ]
        ]);
    }

    /**
     * Get comprehensive referral performance report
     */
    public function performanceReport(Request $request)
    {
        $user = auth()->user();
        
        $report = [
            'overview' => $this->referralRepository->getReferralStatistics($user),
            'matrix_performance' => $this->matrixService->getMatrixPerformanceMetrics($user),
            'earnings_breakdown' => $user->calculateTotalEarningsDetailed(),
            'tier_analysis' => $this->analyzeTierPerformance($user),
            'growth_metrics' => $this->calculateGrowthMetrics($user),
            'commission_trends' => $this->getCommissionTrends($user)
        ];
        
        // For GET requests, always return Inertia response (page view)
        if ($request->isMethod('GET')) {
            return Inertia::render('Referrals/PerformanceReport', [
                'report' => $report,
                'user' => $user
            ]);
        }
        
        // Return JSON for API requests (POST, etc.)
        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }

    /**
     * Export referral data
     */
    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'format' => 'required|in:csv,excel,pdf',
            'type' => 'required|in:referrals,commissions,matrix,performance'
        ]);
        
        $user = auth()->user();
        
        try {
            $exportData = match($request->type) {
                'referrals' => $this->exportReferralData($user),
                'commissions' => $this->exportCommissionData($user),
                'matrix' => $this->exportMatrixData($user),
                'performance' => $this->exportPerformanceData($user)
            };
            
            return response()->json([
                'success' => true,
                'data' => $exportData,
                'message' => 'Export data prepared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Private helper methods
     */
    private function generateReferralLink(User $user): string
    {
        return route('register') . '?ref=' . $user->referral_code;
    }

    private function identifySpilloverOpportunities(User $user): array
    {
        $position = $this->matrixService->findNextAvailablePosition($user);
        
        if (!$position) {
            return [];
        }
        
        return [
            'has_opportunities' => true,
            'next_position' => $position,
            'placement_type' => $position['placement_type'] ?? 'direct',
            'available_slots' => 3 - ($position['position'] - 1)
        ];
    }

    private function analyzeTierPerformance(User $user): array
    {
        $tier = $user->currentInvestmentTier;
        
        if (!$tier) {
            return [];
        }
        
        $directReferrals = $this->referralRepository->getDirectReferrals($user);
        $tierDistribution = $directReferrals->groupBy(function($referral) {
            return $referral->currentInvestmentTier?->name ?? 'No Tier';
        })->map->count();
        
        return [
            'current_tier' => $tier->name,
            'tier_benefits' => $tier->getTierSpecificBenefits(),
            'referral_tier_distribution' => $tierDistribution,
            'upgrade_potential' => $user->checkTierUpgradeEligibility(),
            'performance_vs_tier' => $this->comparePerformanceToTier($user, $tier)
        ];
    }

    private function calculateGrowthMetrics(User $user): array
    {
        $commissions = $user->referralCommissions()
            ->where('status', 'paid')
            ->orderBy('created_at')
            ->get();
        
        if ($commissions->isEmpty()) {
            return [];
        }
        
        $monthlyGrowth = $commissions->groupBy(function($commission) {
            return $commission->created_at->format('Y-m');
        })->map(function($monthCommissions) {
            return [
                'count' => $monthCommissions->count(),
                'total_amount' => $monthCommissions->sum('amount'),
                'average_amount' => $monthCommissions->avg('amount')
            ];
        });
        
        return [
            'monthly_growth' => $monthlyGrowth,
            'total_growth_rate' => $this->calculateTotalGrowthRate($commissions),
            'average_monthly_earnings' => $monthlyGrowth->avg('total_amount'),
            'best_performing_month' => $monthlyGrowth->sortByDesc('total_amount')->first()
        ];
    }

    private function getCommissionTrends(User $user): array
    {
        $commissions = $user->referralCommissions()
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subMonths(12))
            ->get();
        
        $trends = [
            'by_level' => $commissions->groupBy('level')->map(function($levelCommissions) {
                return [
                    'count' => $levelCommissions->count(),
                    'total_amount' => $levelCommissions->sum('amount'),
                    'average_amount' => $levelCommissions->avg('amount')
                ];
            }),
            'by_month' => $commissions->groupBy(function($commission) {
                return $commission->created_at->format('Y-m');
            })->map->sum('amount'),
            'recent_performance' => [
                'last_30_days' => $commissions->where('created_at', '>=', now()->subDays(30))->sum('amount'),
                'last_90_days' => $commissions->where('created_at', '>=', now()->subDays(90))->sum('amount'),
                'last_year' => $commissions->sum('amount')
            ]
        ];
        
        return $trends;
    }

    private function comparePerformanceToTier(User $user, $tier): array
    {
        $userStats = $this->referralRepository->getReferralStatistics($user);
        
        // This would ideally compare against average performance for the tier
        // For now, we'll provide tier-specific benchmarks
        $tierBenchmarks = [
            'Basic' => ['referrals' => 5, 'monthly_commission' => 100],
            'Starter' => ['referrals' => 10, 'monthly_commission' => 300],
            'Builder' => ['referrals' => 20, 'monthly_commission' => 750],
            'Leader' => ['referrals' => 35, 'monthly_commission' => 1500],
            'Elite' => ['referrals' => 50, 'monthly_commission' => 3000]
        ];
        
        $benchmark = $tierBenchmarks[$tier->name] ?? $tierBenchmarks['Basic'];
        
        return [
            'referrals_vs_benchmark' => [
                'actual' => $userStats['total_referrals_count'],
                'benchmark' => $benchmark['referrals'],
                'performance_ratio' => $benchmark['referrals'] > 0 
                    ? $userStats['total_referrals_count'] / $benchmark['referrals'] 
                    : 0
            ],
            'commission_vs_benchmark' => [
                'actual_monthly' => $userStats['total_commission_earned'] / 12, // Rough monthly average
                'benchmark_monthly' => $benchmark['monthly_commission'],
                'performance_ratio' => $benchmark['monthly_commission'] > 0 
                    ? ($userStats['total_commission_earned'] / 12) / $benchmark['monthly_commission'] 
                    : 0
            ]
        ];
    }

    private function calculateTotalGrowthRate($commissions): float
    {
        if ($commissions->count() < 2) {
            return 0;
        }
        
        $firstMonth = $commissions->first()->created_at->format('Y-m');
        $lastMonth = $commissions->last()->created_at->format('Y-m');
        
        $firstMonthTotal = $commissions->filter(function($commission) use ($firstMonth) {
            return $commission->created_at->format('Y-m') === $firstMonth;
        })->sum('amount');
        
        $lastMonthTotal = $commissions->filter(function($commission) use ($lastMonth) {
            return $commission->created_at->format('Y-m') === $lastMonth;
        })->sum('amount');
        
        if ($firstMonthTotal <= 0) {
            return 0;
        }
        
        return (($lastMonthTotal - $firstMonthTotal) / $firstMonthTotal) * 100;
    }

    private function exportReferralData(User $user): array
    {
        return $this->referralRepository->buildReferralTreeWithInvestments($user, 3);
    }

    private function exportCommissionData(User $user): array
    {
        return $user->referralCommissions()
            ->with(['referee', 'investment'])
            ->get()
            ->toArray();
    }

    private function exportMatrixData(User $user): array
    {
        return $this->matrixService->getMatrixGenealogyReport($user, 3);
    }

    private function exportPerformanceData(User $user): array
    {
        return [
            'statistics' => $this->referralRepository->getReferralStatistics($user),
            'matrix_performance' => $this->matrixService->getMatrixPerformanceMetrics($user),
            'earnings_breakdown' => $user->calculateTotalEarningsDetailed()
        ];
    }

    private function calculateConversionRate(User $user): float
    {
        // Conversion rate: referrals who have paid registration (active subscription)
        $directReferrals = $this->referralRepository->getDirectReferrals($user);
        $activeMembers = $directReferrals->filter(function($referral) {
            return $referral->hasActiveSubscription();
        });
        
        return $directReferrals->count() > 0 
            ? round(($activeMembers->count() / $directReferrals->count()) * 100, 1)
            : 0;
    }

    private function calculateAverageInvestment(User $user): float
    {
        // Average LP (Lifetime Points) per active member
        $directReferrals = $this->referralRepository->getDirectReferrals($user);
        $activeMembers = $directReferrals->filter(function($referral) {
            return $referral->hasActiveSubscription();
        });
        
        $totalLP = $activeMembers->sum(function($referral) {
            return $referral->points?->lifetime_points ?? 0;
        });
        
        return $activeMembers->count() > 0 ? round($totalLP / $activeMembers->count(), 0) : 0;
    }

    private function calculateRetentionRate(User $user): float
    {
        // Retention: members who are still active after 3+ months
        $directReferrals = $this->referralRepository->getDirectReferrals($user);
        $oldMembers = $directReferrals->filter(function($referral) {
            return $referral->created_at <= now()->subMonths(3);
        });
        
        if ($oldMembers->count() === 0) {
            return 0;
        }
        
        $retainedMembers = $oldMembers->filter(function($referral) {
            return $referral->status === 'active' && $referral->hasActiveSubscription();
        });
        
        return round(($retainedMembers->count() / $oldMembers->count()) * 100, 1);
    }

    private function calculateMonthlyGrowthRate(User $user): float
    {
        // Growth rate based on new referrals
        $thisMonthReferrals = User::where('referrer_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
            
        $lastMonthReferrals = User::where('referrer_id', $user->id)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        
        if ($lastMonthReferrals === 0) {
            return $thisMonthReferrals > 0 ? 100 : 0;
        }
        
        return round((($thisMonthReferrals - $lastMonthReferrals) / $lastMonthReferrals) * 100, 1);
    }

    private function getRecentActivity(User $user): array
    {
        $activities = [];
        
        // Recent referrals
        $recentReferrals = $this->referralRepository->getDirectReferrals($user)
            ->take(5);
        
        foreach ($recentReferrals as $referral) {
            $activities[] = [
                'id' => 'ref_' . $referral->id,
                'type' => 'referral',
                'description' => "New referral: {$referral->name} joined",
                'created_at' => $referral->created_at->toISOString()
            ];
        }
        
        // Recent commissions
        $recentCommissions = $user->referralCommissions()
            ->where('status', 'paid')
            ->latest()
            ->take(5)
            ->get();
        
        foreach ($recentCommissions as $commission) {
            $activities[] = [
                'id' => 'comm_' . $commission->id,
                'type' => 'commission',
                'description' => "Commission earned from Level {$commission->level}",
                'amount' => $commission->amount,
                'created_at' => $commission->created_at->toISOString()
            ];
        }
        
        // Sort by date and take latest 10
        usort($activities, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($activities, 0, 10);
    }

    private function getTierDistribution(User $user): array
    {
        // MyGrowNet uses professional levels, not investment tiers
        $directReferrals = $this->referralRepository->getDirectReferrals($user);
        
        $distribution = [];
        $levels = [
            'associate' => 'Associate',
            'professional' => 'Professional',
            'senior' => 'Senior',
            'manager' => 'Manager',
            'director' => 'Director',
            'executive' => 'Executive',
            'ambassador' => 'Ambassador'
        ];
        
        foreach ($levels as $levelKey => $levelName) {
            $levelReferrals = $directReferrals->filter(function($referral) use ($levelKey) {
                return $referral->current_professional_level === $levelKey;
            });
            
            // Count total LP for this level
            $totalLP = $levelReferrals->sum(function($referral) {
                return $referral->points?->lifetime_points ?? 0;
            });
            
            $distribution[] = [
                'name' => $levelName,
                'count' => $levelReferrals->count(),
                'total_lp' => $totalLP
            ];
        }
        
        return $distribution;
    }

    private function getCodeStats(User $user): array
    {
        $directReferrals = $this->referralRepository->getDirectReferrals($user);
        $activeInvestors = $directReferrals->filter(function($referral) {
            return $referral->investments()->where('status', 'active')->exists();
        });
        
        return [
            'uses_count' => $directReferrals->count(),
            'successful_registrations' => $directReferrals->count(),
            'active_investors' => $activeInvestors->count(),
            'total_earnings' => $user->referralCommissions()->where('status', 'paid')->sum('amount')
        ];
    }

    private function getLinkStats(User $user): array
    {
        // Placeholder - would need tracking implementation
        return [
            'clicks' => 0,
            'conversion_rate' => 0
        ];
    }

    private function getMessageTemplates(): array
    {
        $user = auth()->user();
        $referralCode = $user->referral_code ?? '';
        $referralLink = $this->generateReferralLink($user);
        
        $templates = [
            [
                'id' => 1,
                'title' => 'Growth & Learning',
                'description' => 'Share the learning and earning opportunity',
                'template' => 'Hi! I wanted to share MyGrowNet with you - a platform where you can Learn, Earn, and Grow! Get access to skills training, mentorship, and multiple income streams. Join using my referral code: {referral_code}',
                'message' => 'Hi! I wanted to share MyGrowNet with you - a platform where you can Learn, Earn, and Grow! Get access to skills training, mentorship, and multiple income streams. Join using my referral code: ' . $referralCode
            ],
            [
                'id' => 2,
                'title' => 'Community Empowerment',
                'description' => 'Focus on community and empowerment',
                'template' => 'Join MyGrowNet and be part of a community that empowers members through education, mentorship, and profit-sharing. Build your network and grow together! Use my link: {referral_link}',
                'message' => 'Join MyGrowNet and be part of a community that empowers members through education, mentorship, and profit-sharing. Build your network and grow together! Use my link: ' . $referralLink
            ],
            [
                'id' => 3,
                'title' => 'Multiple Income Streams',
                'description' => 'Emphasize earning opportunities',
                'template' => 'Discover 6 powerful income streams with MyGrowNet! Earn through referrals, monthly bonus pools, quarterly profit-sharing, and more. Start your journey with my referral: {referral_code}',
                'message' => 'Discover 6 powerful income streams with MyGrowNet! Earn through referrals, monthly bonus pools, quarterly profit-sharing, and more. Start your journey with my referral: ' . $referralCode
            ],
            [
                'id' => 4,
                'title' => 'Skills & Business',
                'description' => 'Highlight skills and business development',
                'template' => 'Want to learn practical skills while building a business? MyGrowNet offers training, coaching, and a 7-level professional growth system. Join me on this journey: {referral_link}',
                'message' => 'Want to learn practical skills while building a business? MyGrowNet offers training, coaching, and a 7-level professional growth system. Join me on this journey: ' . $referralLink
            ]
        ];
        
        return $templates;
    }

    private function getEarningsBreakdown(User $user): array
    {
        // Get commissions by level
        $commissionsByLevel = [];
        for ($level = 1; $level <= 3; $level++) {
            $levelCommissions = $user->referralCommissions()
                ->where('level', $level)
                ->where('status', 'paid')
                ->get();
            
            $commissionsByLevel[] = [
                'level' => $level,
                'amount' => $levelCommissions->sum('amount'),
                'count' => $levelCommissions->count()
            ];
        }

        // Calculate different earning types
        $directReferrals = $user->referralCommissions()
            ->where('level', 1)
            ->where('status', 'paid')
            ->sum('amount');

        $spillover = $user->referralCommissions()
            ->where('level', '>', 1)
            ->where('status', 'paid')
            ->sum('amount');

        // Matrix bonuses (placeholder - would need proper implementation)
        $matrixBonuses = 0;

        // Reinvestment bonuses (placeholder - would need proper implementation)
        $reinvestmentBonuses = 0;

        $total = $directReferrals + $spillover + $matrixBonuses + $reinvestmentBonuses;

        return [
            'by_level' => $commissionsByLevel,
            'direct_referrals' => $directReferrals,
            'spillover' => $spillover,
            'matrix_bonuses' => $matrixBonuses,
            'reinvestment_bonuses' => $reinvestmentBonuses,
            'total' => $total
        ];
    }

    /**
     * Get 7-level network statistics
     */
    protected function get7LevelNetworkStats(User $user): array
    {
        $stats = [
            'level_1_count' => 0,
            'level_2_count' => 0,
            'level_3_count' => 0,
            'level_4_count' => 0,
            'level_5_count' => 0,
            'level_6_count' => 0,
            'level_7_count' => 0,
            'total_network' => 0,
            'total_earnings' => 0,
        ];

        // Level 1: Direct referrals
        $level1 = User::where('referrer_id', $user->id)->pluck('id');
        $stats['level_1_count'] = $level1->count();

        if ($level1->isEmpty()) {
            return $stats;
        }

        // Level 2: Referrals of level 1
        $level2 = User::whereIn('referrer_id', $level1)->pluck('id');
        $stats['level_2_count'] = $level2->count();

        if ($level2->isEmpty()) {
            $stats['total_network'] = $stats['level_1_count'];
            $stats['total_earnings'] = ReferralCommission::where('referrer_id', $user->id)->sum('amount');
            return $stats;
        }

        // Level 3
        $level3 = User::whereIn('referrer_id', $level2)->pluck('id');
        $stats['level_3_count'] = $level3->count();

        // Level 4
        if ($level3->isNotEmpty()) {
            $level4 = User::whereIn('referrer_id', $level3)->pluck('id');
            $stats['level_4_count'] = $level4->count();

            // Level 5
            if ($level4->isNotEmpty()) {
                $level5 = User::whereIn('referrer_id', $level4)->pluck('id');
                $stats['level_5_count'] = $level5->count();

                // Level 6
                if ($level5->isNotEmpty()) {
                    $level6 = User::whereIn('referrer_id', $level5)->pluck('id');
                    $stats['level_6_count'] = $level6->count();

                    // Level 7
                    if ($level6->isNotEmpty()) {
                        $level7 = User::whereIn('referrer_id', $level6)->pluck('id');
                        $stats['level_7_count'] = $level7->count();
                    }
                }
            }
        }

        // Calculate totals
        $stats['total_network'] = $stats['level_1_count'] + $stats['level_2_count'] + 
                                  $stats['level_3_count'] + $stats['level_4_count'] + 
                                  $stats['level_5_count'] + $stats['level_6_count'] + 
                                  $stats['level_7_count'];

        // Get total earnings from commissions
        $stats['total_earnings'] = ReferralCommission::where('referrer_id', $user->id)->sum('amount');

        return $stats;
    }

    /**
     * Build simple matrix visualization from referrer relationships
     */
    protected function buildSimpleMatrixVisualization(User $user): array
    {
        // Get user's points
        $userPoints = $user->points;
        
        // Root node (the user themselves)
        $root = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'professional_level' => ucfirst($user->current_professional_level ?? 'associate'),
            'lifetime_points' => $userPoints?->lifetime_points ?? 0,
            'bonus_points' => $userPoints?->bonus_points ?? 0,
            'is_empty' => false,
            'is_direct' => false,
            'is_spillover' => false,
            'status' => $user->status,
            'position' => 1,
            'level' => 0,
            'joined_at' => $user->created_at->format('M d, Y')
        ];

        // Level 1: Direct referrals (up to 3 for 3x3 matrix)
        $level1Users = User::where('referrer_id', $user->id)
            ->with('points')
            ->orderBy('created_at')
            ->limit(3)
            ->get();

        $level1 = [];
        for ($i = 0; $i < 3; $i++) {
            if (isset($level1Users[$i])) {
                $member = $level1Users[$i];
                $memberPoints = $member->points;
                $level1[] = [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'phone' => $member->phone,
                    'professional_level' => ucfirst($member->current_professional_level ?? 'associate'),
                    'lifetime_points' => $memberPoints?->lifetime_points ?? 0,
                    'bonus_points' => $memberPoints?->bonus_points ?? 0,
                    'is_empty' => false,
                    'is_direct' => true,
                    'is_spillover' => false,
                    'status' => $member->status,
                    'position' => $i + 1,
                    'level' => 1,
                    'joined_at' => $member->created_at->format('M d, Y')
                ];
            } else {
                // Empty position
                $level1[] = [
                    'id' => null,
                    'name' => 'Available',
                    'email' => '',
                    'phone' => '',
                    'professional_level' => '',
                    'lifetime_points' => 0,
                    'bonus_points' => 0,
                    'is_empty' => true,
                    'is_direct' => false,
                    'is_spillover' => false,
                    'status' => 'empty',
                    'position' => $i + 1,
                    'level' => 1,
                    'joined_at' => ''
                ];
            }
        }

        // Level 2: Referrals of level 1 (9 positions)
        $level2 = [];
        $position = 1;
        foreach ($level1Users as $level1User) {
            $level2Users = User::where('referrer_id', $level1User->id)
                ->with('points')
                ->orderBy('created_at')
                ->limit(3)
                ->get();

            for ($i = 0; $i < 3; $i++) {
                if (isset($level2Users[$i])) {
                    $member = $level2Users[$i];
                    $memberPoints = $member->points;
                    $level2[] = [
                        'id' => $member->id,
                        'name' => $member->name,
                        'email' => $member->email,
                        'phone' => $member->phone,
                        'professional_level' => ucfirst($member->current_professional_level ?? 'associate'),
                        'lifetime_points' => $memberPoints?->lifetime_points ?? 0,
                        'bonus_points' => $memberPoints?->bonus_points ?? 0,
                        'is_empty' => false,
                        'is_direct' => false,
                        'is_spillover' => false,
                        'status' => $member->status,
                        'position' => $position,
                        'level' => 2,
                        'joined_at' => $member->created_at->format('M d, Y')
                    ];
                } else {
                    $level2[] = [
                        'id' => null,
                        'name' => 'Available',
                        'email' => '',
                        'phone' => '',
                        'professional_level' => '',
                        'lifetime_points' => 0,
                        'bonus_points' => 0,
                        'is_empty' => true,
                        'is_direct' => false,
                        'is_spillover' => false,
                        'status' => 'empty',
                        'position' => $position,
                        'level' => 2,
                        'joined_at' => ''
                    ];
                }
                $position++;
            }
        }

        // Fill remaining level 2 positions if level 1 isn't full
        while (count($level2) < 9) {
            $level2[] = [
                'id' => null,
                'name' => 'Available',
                'email' => '',
                'phone' => '',
                'professional_level' => '',
                'lifetime_points' => 0,
                'bonus_points' => 0,
                'is_empty' => true,
                'is_direct' => false,
                'is_spillover' => false,
                'status' => 'empty',
                'position' => $position++,
                'level' => 2,
                'joined_at' => ''
            ];
        }

        // Level 3: 27 positions (simplified - just show available for now)
        $level3 = [];
        for ($i = 1; $i <= 27; $i++) {
            $level3[] = [
                'id' => null,
                'name' => 'Available',
                'email' => '',
                'phone' => '',
                'professional_level' => '',
                'lifetime_points' => 0,
                'bonus_points' => 0,
                'is_empty' => true,
                'is_direct' => false,
                'is_spillover' => false,
                'status' => 'empty',
                'position' => $i,
                'level' => 3,
                'joined_at' => ''
            ];
        }

        return [
            'root' => $root,
            'levels' => [
                'level_1' => $level1,
                'level_2' => $level2,
                'level_3' => $level3
            ]
        ];
    }
}
