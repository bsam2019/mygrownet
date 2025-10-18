<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MatrixPosition;
use App\Services\MatrixService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class MatrixController extends Controller
{
    protected $matrixService;

    public function __construct(MatrixService $matrixService)
    {
        $this->matrixService = $matrixService;
    }

    public function index(Request $request)
    {
        $stats = $this->getMatrixStats();
        $recentPlacements = $this->getRecentPlacements();
        $spilloverQueue = $this->getSpilloverQueue();
        $matrixIssues = $this->getMatrixIssues();

        return Inertia::render('Admin/Matrix/Index', [
            'stats' => $stats,
            'recent_placements' => $recentPlacements,
            'spillover_queue' => $spilloverQueue,
            'matrix_issues' => $matrixIssues,
            'filters' => $request->only(['search', 'status', 'level'])
        ]);
    }

    public function show(User $user)
    {
        $position = $user->getMatrixPosition();
        $networkStats = $position ? $this->matrixService->getNetworkStatistics($user) : null;

        $matrixData = [
            'user' => $user->load(['matrixPositions', 'referrer']),
            'structure' => $user->buildMatrixStructure(3),
            'downline_counts' => $user->getMatrixDownlineCount(7), // Show all 7 levels
            'position_details' => $position,
            'network_stats' => $networkStats,
            'commission_history' => $user->referralCommissions()
                ->with(['investment', 'referee'])
                ->latest()
                ->paginate(20),
        ];

        return Inertia::render('Admin/Matrix/Show', $matrixData);
    }

    public function reassignPosition(Request $request, User $user)
    {
        $request->validate([
            'new_sponsor_id' => 'required|exists:users,id',
            'reason' => 'required|string|max:255'
        ]);

        try {
            $newSponsor = User::findOrFail($request->new_sponsor_id);
            
            // Deactivate old position
            $oldPosition = $user->getMatrixPosition();
            if ($oldPosition) {
                $oldPosition->update(['is_active' => false]);
            }

            // Create new position under new sponsor
            $this->matrixService->placeUserInMatrix($user, $newSponsor);

            // Log the reassignment
            $user->recordActivity(
                'matrix_position_reassigned',
                "Position reassigned to sponsor {$newSponsor->name}. Reason: {$request->reason}"
            );

            return back()->with('success', 'Matrix position reassigned successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to reassign position: ' . $e->getMessage());
        }
    }

    public function processSpillover(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $processed = 0;
        $errors = [];

        foreach ($request->user_ids as $userId) {
            try {
                $user = User::with('referrer')->find($userId);
                
                if (!$user) {
                    $errors[] = "User {$userId}: Not found";
                    continue;
                }

                if ($user->getMatrixPosition()) {
                    $errors[] = "User {$userId}: Already has matrix position";
                    continue;
                }

                if (!$user->referrer) {
                    $errors[] = "User {$userId}: No referrer/sponsor found";
                    continue;
                }

                // Place user in matrix with their sponsor
                $this->matrixService->placeUserInMatrix($user, $user->referrer);
                $processed++;
            } catch (\Exception $e) {
                $errors[] = "User {$userId}: " . $e->getMessage();
            }
        }

        $message = "Successfully processed {$processed} spillover placement(s)";
        if (!empty($errors)) {
            $message .= ". Errors: " . implode('; ', $errors);
        }

        return back()->with($processed > 0 ? 'success' : 'error', $message);
    }

    public function matrixAnalytics(Request $request)
    {
        $period = $request->get('period', 'month');
        $analytics = $this->getMatrixAnalytics($period);

        return Inertia::render('Admin/Matrix/Analytics', [
            'analytics' => $analytics,
            'period' => $period
        ]);
    }

    private function getMatrixStats()
    {
        // Get stats for all 7 levels
        $levelStats = [];
        for ($level = 1; $level <= MatrixPosition::MAX_LEVELS; $level++) {
            $count = MatrixPosition::where('level', $level)
                ->where('is_active', true)
                ->count();
            
            $levelStats[] = [
                'level' => $level,
                'name' => MatrixPosition::LEVEL_NAMES[$level],
                'count' => $count,
                'capacity' => MatrixPosition::LEVEL_CAPACITY[$level],
                'fill_percentage' => MatrixPosition::LEVEL_CAPACITY[$level] > 0 
                    ? round(($count / MatrixPosition::LEVEL_CAPACITY[$level]) * 100, 2)
                    : 0,
            ];
        }

        return [
            'total_positions' => MatrixPosition::where('is_active', true)->count(),
            'filled_positions' => MatrixPosition::where('is_active', true)->whereNotNull('user_id')->count(),
            'total_users_in_matrix' => MatrixPosition::where('is_active', true)->distinct('user_id')->count('user_id'),
            'max_network_capacity' => array_sum(MatrixPosition::LEVEL_CAPACITY),
            'matrix_levels' => $levelStats,
            'commission_stats' => [
                'total_paid' => DB::table('referral_commissions')
                    ->where('status', 'paid')
                    ->sum('amount'),
                'pending' => DB::table('referral_commissions')
                    ->where('status', 'pending')
                    ->sum('amount'),
                'total_commissions' => DB::table('referral_commissions')->count(),
            ],
            'recent_activity' => [
                'placements_today' => MatrixPosition::whereDate('placed_at', today())->count(),
                'placements_this_week' => MatrixPosition::whereBetween('placed_at', [now()->startOfWeek(), now()])->count(),
                'placements_this_month' => MatrixPosition::whereBetween('placed_at', [now()->startOfMonth(), now()])->count(),
            ]
        ];
    }

    private function getRecentPlacements()
    {
        return MatrixPosition::with(['user', 'sponsor'])
            ->where('is_active', true)
            ->whereNotNull('user_id')
            ->latest('placed_at')
            ->take(20)
            ->get()
            ->map(function ($position) {
                return [
                    'id' => $position->id,
                    'user' => [
                        'id' => $position->user?->id,
                        'name' => $position->user?->name,
                        'email' => $position->user?->email,
                    ],
                    'sponsor' => [
                        'id' => $position->sponsor?->id,
                        'name' => $position->sponsor?->name,
                    ],
                    'position' => $position->position,
                    'level' => $position->level,
                    'level_name' => $position->professional_level_name ?? MatrixPosition::LEVEL_NAMES[$position->level] ?? 'Unknown',
                    'placed_at' => $position->placed_at,
                    'is_active' => $position->is_active,
                ];
            });
    }

    private function getSpilloverQueue()
    {
        return User::whereNotNull('referrer_id')
            ->whereDoesntHave('matrixPositions')
            ->with(['referrer'])
            ->orderBy('created_at')
            ->take(50)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'sponsor' => $user->referrer?->name,
                    'waiting_since' => $user->created_at,
                    'investment_amount' => $user->total_investment_amount ?? 0
                ];
            });
    }

    private function getMatrixIssues()
    {
        $issues = [];

        // Find orphaned positions
        $orphanedPositions = MatrixPosition::whereNull('sponsor_id')
            ->whereNotNull('user_id')
            ->where('level', '>', 1)
            ->count();

        if ($orphanedPositions > 0) {
            $issues[] = [
                'type' => 'orphaned_positions',
                'count' => $orphanedPositions,
                'description' => 'Matrix positions without proper sponsor linkage',
                'severity' => 'high'
            ];
        }

        // Find users without matrix positions
        $usersWithoutPositions = User::whereNull('matrix_position')
            ->whereNotNull('referrer_id')
            ->where('created_at', '<', now()->subDays(1))
            ->count();

        if ($usersWithoutPositions > 0) {
            $issues[] = [
                'type' => 'missing_positions',
                'count' => $usersWithoutPositions,
                'description' => 'Users waiting for matrix placement over 24 hours',
                'severity' => 'medium'
            ];
        }

        return $issues;
    }

    private function getMatrixAnalytics($period)
    {
        $startDate = match($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
            default => now()->subMonth()
        };

        return [
            'placement_trends' => $this->getPlacementTrends($startDate),
            'commission_trends' => $this->getCommissionTrends($startDate),
            'level_distribution' => $this->getLevelDistribution(),
            'top_sponsors' => $this->getTopSponsors($startDate),
            'spillover_analysis' => $this->getSpilloverAnalysis($startDate)
        ];
    }

    private function getPlacementTrends($startDate)
    {
        return MatrixPosition::where('updated_at', '>=', $startDate)
            ->whereNotNull('user_id')
            ->selectRaw('DATE(updated_at) as date')
            ->selectRaw('COUNT(*) as placements')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getCommissionTrends($startDate)
    {
        return DB::table('referral_commissions')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('SUM(amount) as total_amount')
            ->selectRaw('COUNT(*) as commission_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getLevelDistribution()
    {
        return MatrixPosition::whereNotNull('user_id')
            ->selectRaw('level')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('level')
            ->get();
    }

    private function getTopSponsors($startDate)
    {
        return User::withCount(['directReferrals' => function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }])
            ->having('direct_referrals_count', '>', 0)
            ->orderBy('direct_referrals_count', 'desc')
            ->take(10)
            ->get();
    }

    private function getSpilloverAnalysis($startDate)
    {
        return [
            'total_spillovers' => MatrixPosition::where('placement_type', 'spillover')
                ->where('updated_at', '>=', $startDate)
                ->count(),
            'spillover_by_level' => MatrixPosition::where('placement_type', 'spillover')
                ->where('updated_at', '>=', $startDate)
                ->selectRaw('level')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('level')
                ->get(),
            'average_wait_time' => $this->calculateAverageSpilloverWaitTime($startDate)
        ];
    }

    private function calculateAverageSpilloverWaitTime($startDate)
    {
        $spillovers = MatrixPosition::where('placement_type', 'spillover')
            ->where('updated_at', '>=', $startDate)
            ->with('user')
            ->get();

        if ($spillovers->isEmpty()) return 0;

        $totalWaitTime = $spillovers->sum(function ($position) {
            return $position->user->created_at->diffInHours($position->updated_at);
        });

        return round($totalWaitTime / $spillovers->count(), 2);
    }
}