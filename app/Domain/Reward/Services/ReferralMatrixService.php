<?php

namespace App\Domain\Reward\Services;

use App\Models\User;
use App\Models\Investment;
use App\Models\MatrixPosition;
use App\Models\ReferralCommission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReferralMatrixService
{
    /**
     * Build 3x3 matrix structure for a user
     *
     * @param User $user
     * @param int $maxLevel
     * @return array
     */
    public function buildMatrix(User $user, int $maxLevel = 3): array
    {
        $position = $user->getMatrixPosition();
        
        if (!$position) {
            return [
                'user_id' => $user->id,
                'has_position' => false,
                'matrix_structure' => [],
                'total_downline' => 0,
                'levels' => []
            ];
        }

        $structure = $this->buildMatrixLevel($position, 1, $maxLevel);
        $downlineCounts = $this->calculateDownlineCounts($user, $maxLevel);

        return [
            'user_id' => $user->id,
            'has_position' => true,
            'matrix_position' => [
                'level' => $position->level,
                'position' => $position->position,
                'sponsor_id' => $position->sponsor_id,
                'placed_at' => $position->placed_at
            ],
            'matrix_structure' => $structure,
            'downline_counts' => $downlineCounts,
            'total_downline' => array_sum($downlineCounts),
            'matrix_capacity' => $this->getMatrixCapacity($maxLevel),
            'available_positions' => $this->calculateAvailablePositions($user, $maxLevel)
        ];
    }

    /**
     * Build matrix level recursively
     *
     * @param MatrixPosition $position
     * @param int $currentLevel
     * @param int $maxLevel
     * @return array
     */
    protected function buildMatrixLevel(MatrixPosition $position, int $currentLevel, int $maxLevel): array
    {
        if ($currentLevel > $maxLevel) {
            return [];
        }

        $user = $position->user;
        $structure = [
            'level' => $currentLevel,
            'position' => $position->position,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'total_investment' => $user->total_investment_amount,
                'tier' => $user->currentInvestmentTier?->name,
                'joined_at' => $position->placed_at
            ],
            'children' => []
        ];

        // Get direct children (3x3 matrix allows 3 children per position)
        $children = MatrixPosition::where('sponsor_id', $user->id)
            ->where('level', $currentLevel + 1)
            ->where('is_active', true)
            ->with(['user.currentInvestmentTier'])
            ->orderBy('position')
            ->get();

        foreach ($children as $child) {
            $structure['children'][] = $this->buildMatrixLevel($child, $currentLevel + 1, $maxLevel);
        }

        return $structure;
    }

    /**
     * Find next available position for spillover
     *
     * @param User $sponsor
     * @return array|null
     */
    public function findNextAvailablePosition(User $sponsor): ?array
    {
        // Check if sponsor has available direct positions (max 3 in 3x3 matrix)
        $directChildren = MatrixPosition::where('sponsor_id', $sponsor->id)
            ->where('level', 1)
            ->where('is_active', true)
            ->count();

        if ($directChildren < 3) {
            return [
                'sponsor_id' => $sponsor->id,
                'level' => 1,
                'position' => $directChildren + 1,
                'placement_type' => 'direct'
            ];
        }

        // Implement spillover logic - find next available position in downline
        return $this->findSpilloverPosition($sponsor, 1, 3);
    }

    /**
     * Find spillover position recursively
     *
     * @param User $sponsor
     * @param int $currentLevel
     * @param int $maxLevel
     * @return array|null
     */
    protected function findSpilloverPosition(User $sponsor, int $currentLevel, int $maxLevel): ?array
    {
        if ($currentLevel >= $maxLevel) {
            return null;
        }

        // Get all positions at current level under this sponsor
        $positions = MatrixPosition::where('sponsor_id', $sponsor->id)
            ->where('level', $currentLevel)
            ->where('is_active', true)
            ->with('user')
            ->orderBy('position')
            ->get();

        foreach ($positions as $position) {
            // Check if this position has available slots
            $childrenCount = MatrixPosition::where('sponsor_id', $position->user_id)
                ->where('level', $currentLevel + 1)
                ->where('is_active', true)
                ->count();

            if ($childrenCount < 3) {
                return [
                    'sponsor_id' => $position->user_id,
                    'level' => $currentLevel + 1,
                    'position' => $childrenCount + 1,
                    'placement_type' => 'spillover',
                    'spillover_from' => $sponsor->id,
                    'spillover_level' => $currentLevel + 1
                ];
            }

            // Recursively check deeper levels
            $spillover = $this->findSpilloverPosition($position->user, $currentLevel + 1, $maxLevel);
            if ($spillover) {
                $spillover['spillover_from'] = $sponsor->id;
                return $spillover;
            }
        }

        return null;
    }

    /**
     * Process spillover placement for new user
     *
     * @param User $newUser
     * @param User $sponsor
     * @return bool
     */
    public function processSpillover(User $newUser, User $sponsor): bool
    {
        try {
            DB::beginTransaction();

            $position = $this->findNextAvailablePosition($sponsor);
            
            if (!$position) {
                Log::warning("No available matrix position found for user {$newUser->id} under sponsor {$sponsor->id}");
                DB::rollBack();
                return false;
            }

            // Create matrix position
            $matrixPosition = MatrixPosition::create([
                'user_id' => $newUser->id,
                'sponsor_id' => $position['sponsor_id'],
                'level' => $position['level'],
                'position' => $position['position'],
                'is_active' => true,
                'placed_at' => now(),
                'placement_type' => $position['placement_type']
            ]);

            // Update user's matrix position reference
            $newUser->update(['matrix_position' => [
                'id' => $matrixPosition->id,
                'level' => $position['level'],
                'position' => $position['position'],
                'sponsor_id' => $position['sponsor_id'],
                'placement_type' => $position['placement_type']
            ]]);

            // Notify affected upline members about spillover
            if ($position['placement_type'] === 'spillover') {
                $this->notifySpilloverBeneficiaries($newUser, $position);
            }

            DB::commit();
            
            Log::info("User {$newUser->id} placed in matrix at level {$position['level']}, position {$position['position']} under sponsor {$position['sponsor_id']}");
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to process spillover for user {$newUser->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Notify upline members about spillover benefits
     *
     * @param User $newUser
     * @param array $position
     * @return void
     */
    protected function notifySpilloverBeneficiaries(User $newUser, array $position): void
    {
        // Find all upline members who benefit from this spillover
        $beneficiaries = $this->getSpilloverBeneficiaries($newUser, $position);
        
        foreach ($beneficiaries as $beneficiary) {
            // Record spillover notification/activity
            $beneficiary['user']->recordActivity(
                'matrix_spillover_received',
                "Received spillover placement: {$newUser->name} placed at level {$position['level']}"
            );
        }
    }

    /**
     * Get users who benefit from spillover
     *
     * @param User $newUser
     * @param array $position
     * @return array
     */
    protected function getSpilloverBeneficiaries(User $newUser, array $position): array
    {
        $beneficiaries = [];
        $currentSponsorId = $position['sponsor_id'];
        $level = 1;

        // Trace upline to find original sponsor and intermediate beneficiaries
        while ($currentSponsorId && $level <= 3) {
            $sponsor = User::find($currentSponsorId);
            if (!$sponsor) break;

            $beneficiaries[] = [
                'user' => $sponsor,
                'level' => $level,
                'benefit_type' => $level === 1 ? 'direct_spillover' : 'indirect_spillover'
            ];

            // Get sponsor's sponsor for next level
            $sponsorPosition = $sponsor->getMatrixPosition();
            $currentSponsorId = $sponsorPosition?->sponsor_id;
            $level++;
        }

        return $beneficiaries;
    }

    /**
     * Add matrix position tracking and visualization data generation
     *
     * @param User $user
     * @return array
     */
    public function generateMatrixVisualizationData(User $user): array
    {
        $matrix = $this->buildMatrix($user);
        
        if (!$matrix['has_position']) {
            return [
                'visualization_type' => '3x3_matrix',
                'user_position' => null,
                'matrix_data' => [],
                'statistics' => [
                    'total_positions' => 0,
                    'filled_positions' => 0,
                    'available_positions' => 39, // 3 + 9 + 27
                    'completion_percentage' => 0
                ]
            ];
        }

        $visualizationData = $this->formatMatrixForVisualization($matrix['matrix_structure']);
        $statistics = $this->calculateMatrixStatistics($matrix);

        return [
            'visualization_type' => '3x3_matrix',
            'user_position' => $matrix['matrix_position'],
            'matrix_data' => $visualizationData,
            'downline_counts' => $matrix['downline_counts'],
            'statistics' => $statistics,
            'spillover_opportunities' => $this->identifySpilloverOpportunities($user)
        ];
    }

    /**
     * Format matrix structure for frontend visualization
     *
     * @param array $structure
     * @return array
     */
    protected function formatMatrixForVisualization(array $structure): array
    {
        if (empty($structure)) {
            return [];
        }

        $formatted = [
            'level' => $structure['level'],
            'position' => $structure['position'],
            'user' => $structure['user'],
            'has_children' => !empty($structure['children']),
            'children_count' => count($structure['children']),
            'children' => []
        ];

        foreach ($structure['children'] as $child) {
            $formatted['children'][] = $this->formatMatrixForVisualization($child);
        }

        return $formatted;
    }

    /**
     * Calculate matrix statistics
     *
     * @param array $matrix
     * @return array
     */
    protected function calculateMatrixStatistics(array $matrix): array
    {
        $totalPositions = $matrix['total_downline'];
        $maxCapacity = $matrix['matrix_capacity'];
        $completionPercentage = $maxCapacity > 0 ? ($totalPositions / $maxCapacity) * 100 : 0;

        return [
            'total_positions' => $totalPositions,
            'filled_positions' => $totalPositions,
            'available_positions' => $maxCapacity - $totalPositions,
            'max_capacity' => $maxCapacity,
            'completion_percentage' => round($completionPercentage, 2),
            'level_breakdown' => $matrix['downline_counts']
        ];
    }

    /**
     * Identify spillover opportunities
     *
     * @param User $user
     * @return array
     */
    protected function identifySpilloverOpportunities(User $user): array
    {
        $opportunities = [];
        $position = $this->findNextAvailablePosition($user);
        
        if ($position) {
            $opportunities[] = [
                'type' => $position['placement_type'],
                'level' => $position['level'],
                'position' => $position['position'],
                'sponsor_id' => $position['sponsor_id'],
                'available_slots' => 3 - ($position['position'] - 1)
            ];
        }

        return $opportunities;
    }

    /**
     * Calculate downline counts by level
     *
     * @param User $user
     * @param int $maxLevel
     * @return array
     */
    protected function calculateDownlineCounts(User $user, int $maxLevel): array
    {
        $counts = [];
        
        for ($level = 1; $level <= $maxLevel; $level++) {
            $counts["level_{$level}"] = $this->getDownlineCountAtLevel($user, $level);
        }

        return $counts;
    }

    /**
     * Get downline count at specific level
     *
     * @param User $user
     * @param int $level
     * @return int
     */
    protected function getDownlineCountAtLevel(User $user, int $level): int
    {
        return MatrixPosition::whereHas('sponsor', function($query) use ($user) {
            $this->buildSponsorQuery($query, $user->id, 1);
        })
        ->where('level', $level)
        ->where('is_active', true)
        ->count();
    }

    /**
     * Build sponsor query recursively
     *
     * @param $query
     * @param int $userId
     * @param int $currentLevel
     * @return void
     */
    protected function buildSponsorQuery($query, int $userId, int $currentLevel): void
    {
        if ($currentLevel === 1) {
            $query->where('sponsor_id', $userId);
        } else {
            $query->whereIn('sponsor_id', function($subQuery) use ($userId, $currentLevel) {
                $subQuery->select('user_id')
                    ->from('matrix_positions')
                    ->where('sponsor_id', $userId)
                    ->where('level', $currentLevel - 1)
                    ->where('is_active', true);
            });
        }
    }

    /**
     * Get matrix capacity for given levels
     *
     * @param int $maxLevel
     * @return int
     */
    protected function getMatrixCapacity(int $maxLevel): int
    {
        $capacity = 0;
        for ($level = 1; $level <= $maxLevel; $level++) {
            $capacity += pow(3, $level); // 3^1 + 3^2 + 3^3 = 3 + 9 + 27 = 39
        }
        return $capacity;
    }

    /**
     * Calculate available positions
     *
     * @param User $user
     * @param int $maxLevel
     * @return int
     */
    protected function calculateAvailablePositions(User $user, int $maxLevel): int
    {
        $totalCapacity = $this->getMatrixCapacity($maxLevel);
        $filledPositions = array_sum($this->calculateDownlineCounts($user, $maxLevel));
        
        return $totalCapacity - $filledPositions;
    }

    /**
     * Calculate commission for matrix-based referrals
     *
     * @param Investment $investment
     * @return array
     */
    public function calculateMatrixCommissions(Investment $investment): array
    {
        $user = $investment->user;
        $commissions = [];
        
        // Get user's matrix position
        $position = $user->getMatrixPosition();
        if (!$position) {
            return $commissions;
        }

        // Calculate commissions for upline members (up to 3 levels)
        $currentSponsorId = $position->sponsor_id;
        $level = 1;

        while ($currentSponsorId && $level <= 3) {
            $sponsor = User::find($currentSponsorId);
            if (!$sponsor || !$sponsor->canReceiveMatrixCommission($level)) {
                // Move to next level even if current sponsor can't receive commission
                $sponsorPosition = $sponsor?->getMatrixPosition();
                $currentSponsorId = $sponsorPosition?->sponsor_id;
                $level++;
                continue;
            }

            $tier = $sponsor->currentInvestmentTier;
            if ($tier) {
                $commissionAmount = $tier->calculateMatrixCommission(
                    $investment->amount, 
                    $level, 
                    $position->position
                );

                if ($commissionAmount > 0) {
                    $commissions[] = [
                        'referrer_id' => $sponsor->id,
                        'referee_id' => $user->id,
                        'investment_id' => $investment->id,
                        'level' => $level,
                        'amount' => $commissionAmount,
                        'commission_type' => 'matrix',
                        'matrix_position' => $position->position,
                        'tier_name' => $tier->name,
                        'calculation_details' => [
                            'investment_amount' => $investment->amount,
                            'tier_rate' => $tier->getReferralRateForLevel($level),
                            'matrix_multiplier' => $this->getMatrixPositionMultiplier($level, $position->position),
                            'formula' => "({$investment->amount} * {$tier->getReferralRateForLevel($level)}% * {$this->getMatrixPositionMultiplier($level, $position->position)}) / 100"
                        ]
                    ];
                }
            }

            // Move to next level
            $sponsorPosition = $sponsor->getMatrixPosition();
            $currentSponsorId = $sponsorPosition?->sponsor_id;
            $level++;
        }

        return $commissions;
    }

    /**
     * Get matrix position multiplier
     *
     * @param int $level
     * @param int $position
     * @return float
     */
    protected function getMatrixPositionMultiplier(int $level, int $position): float
    {
        // Matrix position multipliers for 3x3 structure
        return match($level) {
            1 => 1.0, // Direct referrals get full commission
            2 => 0.8, // Level 2 gets 80% of base rate
            3 => 0.6, // Level 3 gets 60% of base rate
            default => 0
        };
    }

    /**
     * Process matrix commissions and create records
     *
     * @param Investment $investment
     * @return array
     */
    public function processMatrixCommissions(Investment $investment): array
    {
        $commissions = $this->calculateMatrixCommissions($investment);
        $processedCommissions = [];

        try {
            DB::beginTransaction();

            foreach ($commissions as $commissionData) {
                $commission = ReferralCommission::create([
                    'referrer_id' => $commissionData['referrer_id'],
                    'referred_id' => $commissionData['referee_id'],
                    'investment_id' => $commissionData['investment_id'],
                    'level' => $commissionData['level'],
                    'amount' => $commissionData['amount'],
                    'percentage' => $commissionData['calculation_details']['tier_rate'],
                    'status' => 'pending',
                    'commission_type' => 'matrix',
                    'created_at' => now()
                ]);

                $processedCommissions[] = [
                    'commission_id' => $commission->id,
                    'referrer_id' => $commissionData['referrer_id'],
                    'amount' => $commissionData['amount'],
                    'level' => $commissionData['level'],
                    'status' => 'pending'
                ];

                // Record activity for referrer
                $referrer = User::find($commissionData['referrer_id']);
                $referrer?->recordActivity(
                    'matrix_commission_earned',
                    "Earned matrix commission: K{$commissionData['amount']} from {$investment->user->name} (Level {$commissionData['level']})"
                );
            }

            DB::commit();
            
            Log::info("Processed " . count($processedCommissions) . " matrix commissions for investment {$investment->id}");
            
            return [
                'success' => true,
                'commissions_processed' => count($processedCommissions),
                'total_amount' => collect($processedCommissions)->sum('amount'),
                'commissions' => $processedCommissions
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to process matrix commissions for investment {$investment->id}: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'commissions_processed' => 0,
                'total_amount' => 0,
                'commissions' => []
            ];
        }
    }

    /**
     * Get matrix performance metrics for a user
     *
     * @param User $user
     * @return array
     */
    public function getMatrixPerformanceMetrics(User $user): array
    {
        $matrix = $this->buildMatrix($user);
        
        if (!$matrix['has_position']) {
            return [
                'has_matrix_position' => false,
                'performance_metrics' => []
            ];
        }

        // Calculate total matrix earnings
        $matrixEarnings = ReferralCommission::where('referrer_id', $user->id)
            ->where('commission_type', 'matrix')
            ->where('status', 'paid')
            ->sum('amount');

        $pendingEarnings = ReferralCommission::where('referrer_id', $user->id)
            ->where('commission_type', 'matrix')
            ->where('status', 'pending')
            ->sum('amount');

        // Calculate earnings by level
        $earningsByLevel = [];
        for ($level = 1; $level <= 3; $level++) {
            $earningsByLevel["level_{$level}"] = ReferralCommission::where('referrer_id', $user->id)
                ->where('commission_type', 'matrix')
                ->where('level', $level)
                ->where('status', 'paid')
                ->sum('amount');
        }

        return [
            'has_matrix_position' => true,
            'matrix_position' => $matrix['matrix_position'],
            'performance_metrics' => [
                'total_matrix_earnings' => $matrixEarnings,
                'pending_matrix_earnings' => $pendingEarnings,
                'earnings_by_level' => $earningsByLevel,
                'downline_statistics' => $matrix['downline_counts'],
                'matrix_completion' => $matrix['statistics']['completion_percentage'] ?? 0,
                'spillover_received' => $this->countSpilloverReceived($user),
                'spillover_given' => $this->countSpilloverGiven($user),
                'average_earnings_per_referral' => $matrix['total_downline'] > 0 
                    ? $matrixEarnings / $matrix['total_downline'] 
                    : 0
            ]
        ];
    }

    /**
     * Count spillover received by user
     *
     * @param User $user
     * @return int
     */
    protected function countSpilloverReceived(User $user): int
    {
        return MatrixPosition::where('sponsor_id', $user->id)
            ->where('placement_type', 'spillover')
            ->where('is_active', true)
            ->count();
    }

    /**
     * Count spillover given by user
     *
     * @param User $user
     * @return int
     */
    protected function countSpilloverGiven(User $user): int
    {
        // Count positions where user benefited from spillover from others
        return MatrixPosition::where('user_id', $user->id)
            ->where('placement_type', 'spillover')
            ->where('is_active', true)
            ->count();
    }

    /**
     * Get matrix genealogy report
     *
     * @param User $user
     * @param int $maxLevel
     * @return array
     */
    public function getMatrixGenealogyReport(User $user, int $maxLevel = 3): array
    {
        $matrix = $this->buildMatrix($user, $maxLevel);
        
        if (!$matrix['has_position']) {
            return [
                'user_id' => $user->id,
                'has_matrix' => false,
                'genealogy' => []
            ];
        }

        $genealogy = $this->buildGenealogyData($matrix['matrix_structure']);
        
        return [
            'user_id' => $user->id,
            'has_matrix' => true,
            'matrix_position' => $matrix['matrix_position'],
            'genealogy' => $genealogy,
            'summary' => [
                'total_downline' => $matrix['total_downline'],
                'levels_deep' => $maxLevel,
                'completion_rate' => $matrix['statistics']['completion_percentage'] ?? 0,
                'total_investment_volume' => $this->calculateDownlineInvestmentVolume($genealogy)
            ]
        ];
    }

    /**
     * Build genealogy data from matrix structure
     *
     * @param array $structure
     * @return array
     */
    protected function buildGenealogyData(array $structure): array
    {
        if (empty($structure)) {
            return [];
        }

        $genealogy = [
            'level' => $structure['level'],
            'position' => $structure['position'],
            'user' => $structure['user'],
            'children' => []
        ];

        foreach ($structure['children'] as $child) {
            $genealogy['children'][] = $this->buildGenealogyData($child);
        }

        return $genealogy;
    }

    /**
     * Calculate total investment volume in downline
     *
     * @param array $genealogy
     * @return float
     */
    protected function calculateDownlineInvestmentVolume(array $genealogy): float
    {
        if (empty($genealogy)) {
            return 0;
        }

        $volume = $genealogy['user']['total_investment'] ?? 0;
        
        foreach ($genealogy['children'] as $child) {
            $volume += $this->calculateDownlineInvestmentVolume($child);
        }

        return $volume;
    }

    /**
     * Get matrix depth for a user
     *
     * @param User $user
     * @return int
     */
    public function getMatrixDepth(User $user): int
    {
        $maxDepth = 0;
        $positions = MatrixPosition::where('sponsor_id', $user->id)
            ->where('is_active', true)
            ->get();

        foreach ($positions as $position) {
            $depth = $this->calculatePositionDepth($position->user, 1);
            $maxDepth = max($maxDepth, $depth);
        }

        return $maxDepth;
    }

    /**
     * Calculate position depth recursively
     *
     * @param User $user
     * @param int $currentDepth
     * @return int
     */
    protected function calculatePositionDepth(User $user, int $currentDepth): int
    {
        $children = MatrixPosition::where('sponsor_id', $user->id)
            ->where('is_active', true)
            ->get();

        if ($children->isEmpty()) {
            return $currentDepth;
        }

        $maxChildDepth = $currentDepth;
        foreach ($children as $child) {
            $childDepth = $this->calculatePositionDepth($child->user, $currentDepth + 1);
            $maxChildDepth = max($maxChildDepth, $childDepth);
        }

        return $maxChildDepth;
    }

    /**
     * Get matrix statistics for a user
     *
     * @param User $user
     * @return array
     */
    public function getMatrixStatistics(User $user): array
    {
        $totalPositions = MatrixPosition::where('sponsor_id', $user->id)->count();
        $activePositions = MatrixPosition::where('sponsor_id', $user->id)
            ->where('is_active', true)
            ->count();

        $level1Count = MatrixPosition::where('sponsor_id', $user->id)
            ->where('level', 1)
            ->where('is_active', true)
            ->count();

        $level2Count = MatrixPosition::where('sponsor_id', $user->id)
            ->where('level', 2)
            ->where('is_active', true)
            ->count();

        $level3Count = MatrixPosition::where('sponsor_id', $user->id)
            ->where('level', 3)
            ->where('is_active', true)
            ->count();

        $maxCapacity = 39; // 3 + 9 + 27 for 3x3 matrix
        $availablePositions = $maxCapacity - $activePositions;

        return [
            'total_positions' => $totalPositions,
            'active_positions' => $activePositions,
            'level_1_count' => $level1Count,
            'level_2_count' => $level2Count,
            'level_3_count' => $level3Count,
            'available_positions' => $availablePositions,
            'max_capacity' => $maxCapacity,
            'completion_percentage' => $maxCapacity > 0 ? ($activePositions / $maxCapacity) * 100 : 0
        ];
    }
}