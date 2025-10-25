<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Reward\Repositories\ReferralRepositoryInterface;
use App\Models\User;
use App\Models\ReferralCommission;
use App\Models\MatrixPosition;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EloquentReferralRepository implements ReferralRepositoryInterface
{
    public function buildReferralTree(User $user, int $maxLevel = 3): array
    {
        return $this->buildReferralTreeRecursive($user, 1, $maxLevel);
    }

    public function getReferralsByLevel(User $user, int $level): Collection
    {
        if ($level === 1) {
            return $this->getDirectReferrals($user);
        }

        // For deeper levels, we need to traverse the tree
        $currentLevel = $this->getDirectReferrals($user);
        
        for ($currentDepth = 2; $currentDepth <= $level; $currentDepth++) {
            $nextLevel = new Collection();
            foreach ($currentLevel as $referral) {
                $directReferrals = $this->getDirectReferrals($referral);
                foreach ($directReferrals as $directReferral) {
                    $nextLevel->push($directReferral);
                }
            }
            $currentLevel = $nextLevel;
        }

        return $currentLevel;
    }

    public function getDirectReferrals(User $user): Collection
    {
        return User::where('referrer_id', $user->id)
            ->with(['investments' => function($query) {
                $query->where('status', 'active');
            }, 'currentInvestmentTier'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function buildMatrixStructure(User $user, int $maxLevel = 3): array
    {
        $position = $this->getMatrixPosition($user);
        
        if (!$position) {
            return [
                'user' => $this->formatUserData($user),
                'position' => null,
                'level' => 0,
                'children' => []
            ];
        }

        return $this->buildMatrixLevel($position, 1, $maxLevel);
    }

    public function getAllReferrals(User $user, int $maxDepth = 3): Collection
    {
        $allReferrals = new Collection();
        
        for ($level = 1; $level <= $maxDepth; $level++) {
            $levelReferrals = $this->getReferralsByLevel($user, $level);
            foreach ($levelReferrals as $referral) {
                if (!$allReferrals->contains('id', $referral->id)) {
                    $allReferrals->push($referral);
                }
            }
        }

        return $allReferrals;
    }

    public function getReferralCommissions(User $user): Collection
    {
        return ReferralCommission::where('referrer_id', $user->id)
            ->with(['referee', 'investment'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getMatrixPosition(User $user): ?MatrixPosition
    {
        return MatrixPosition::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
    }

    public function getMatrixDownlineCount(User $user, int $maxLevel = 3): array
    {
        $counts = [];
        
        for ($level = 1; $level <= $maxLevel; $level++) {
            $counts["level_{$level}"] = $this->getMatrixCountAtLevel($user, $level);
        }

        $counts['total'] = array_sum($counts);
        return $counts;
    }

    public function findNextAvailableMatrixPosition(User $sponsor, int $maxLevel = 3): ?array
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
                'position' => $directChildren + 1
            ];
        }

        // Implement spillover logic
        return $this->findSpilloverPosition($sponsor, 1, $maxLevel);
    }

    public function getReferralStatistics(User $user): array
    {
        $directReferrals = $this->getDirectReferrals($user);
        $allReferrals = $this->getAllReferrals($user);
        $commissions = $this->getReferralCommissions($user);
        
        // For MyGrowNet: Active referrals are those who have paid registration/subscription
        $activeReferrals = $directReferrals->filter(function($referral) {
            return $referral->hasActiveSubscription();
        });

        $totalCommissionEarned = $commissions->where('status', 'paid')->sum('amount');
        $pendingCommission = $commissions->where('status', 'pending')->sum('amount');
        $monthlyCommission = $commissions->where('status', 'paid')
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('amount');
        $pendingTransactionsCount = $commissions->where('status', 'pending')->count();

        // Group commissions by level
        $commissionsByLevel = $commissions->groupBy('level')->map(function($levelCommissions) {
            return [
                'count' => $levelCommissions->count(),
                'total_amount' => $levelCommissions->sum('amount'),
                'paid_amount' => $levelCommissions->where('status', 'paid')->sum('amount'),
                'pending_amount' => $levelCommissions->where('status', 'pending')->sum('amount')
            ];
        });

        return [
            'total_referrals_count' => $directReferrals->count(), // Show direct referrals as total for now
            'active_referrals_count' => $activeReferrals->count(),
            'direct_referrals_count' => $directReferrals->count(),
            'total_commission_earned' => $totalCommissionEarned,
            'monthly_commission' => $monthlyCommission,
            'pending_commission' => $pendingCommission,
            'pending_transactions_count' => $pendingTransactionsCount,
            'commissions_by_level' => $commissionsByLevel->toArray(),
            'matrix_downline' => $this->getMatrixDownlineCount($user),
            'matrix_earnings' => 0, // Placeholder for matrix earnings
            'matrix_positions_filled' => 0, // Placeholder for matrix positions
            'referral_conversion_rate' => $directReferrals->count() > 0 
                ? ($activeReferrals->count() / $directReferrals->count()) * 100 
                : 0
        ];
    }

    public function getEligibleForMatrixCommission(User $user, int $level): Collection
    {
        $tier = $user->currentInvestmentTier;
        
        if (!$tier) {
            return collect();
        }

        // Check tier eligibility for different commission levels
        $isEligible = match($level) {
            1 => true, // All tiers eligible for level 1
            2 => in_array($tier->name, ['Starter', 'Builder', 'Leader', 'Elite']),
            3 => in_array($tier->name, ['Builder', 'Leader', 'Elite']),
            default => false
        };

        if (!$isEligible) {
            return new Collection();
        }

        return $this->getReferralsByLevel($user, $level);
    }

    public function buildReferralTreeWithInvestments(User $user, int $maxLevel = 3): array
    {
        return $this->buildReferralTreeWithInvestmentsRecursive($user, 1, $maxLevel);
    }

    /**
     * Private helper methods
     */
    private function buildReferralTreeRecursive(User $user, int $currentLevel, int $maxLevel): array
    {
        $directReferrals = $this->getDirectReferrals($user);
        
        $children = [];
        if ($currentLevel < $maxLevel) {
            $children = $directReferrals->map(function($referral) use ($currentLevel, $maxLevel) {
                return $this->buildReferralTreeRecursive($referral, $currentLevel + 1, $maxLevel);
            })->toArray();
        } else {
            // At max level, still include user data but no children
            $children = $directReferrals->map(function($referral) use ($currentLevel) {
                return [
                    'user' => $this->formatUserData($referral),
                    'level' => $currentLevel + 1,
                    'children' => []
                ];
            })->toArray();
        }
        
        return [
            'user' => $this->formatUserData($user),
            'level' => $currentLevel,
            'children' => $children
        ];
    }

    private function buildReferralTreeWithInvestmentsRecursive(User $user, int $currentLevel, int $maxLevel): array
    {
        if ($currentLevel > $maxLevel) {
            return [];
        }

        $directReferrals = $this->getDirectReferrals($user);
        $userInvestments = $user->investments()->where('status', 'active')->get();
        
        return [
            'user' => $this->formatUserDataWithInvestments($user, $userInvestments),
            'level' => $currentLevel,
            'children' => $directReferrals->map(function($referral) use ($currentLevel, $maxLevel) {
                return $this->buildReferralTreeWithInvestmentsRecursive($referral, $currentLevel + 1, $maxLevel);
            })->toArray()
        ];
    }

    private function buildMatrixLevel(MatrixPosition $position, int $currentLevel, int $maxLevel): array
    {
        if ($currentLevel > $maxLevel) {
            return [];
        }

        $structure = [
            'user' => $this->formatUserData($position->user),
            'position' => [
                'id' => $position->id,
                'level' => $position->level,
                'position' => $position->position,
                'placed_at' => $position->placed_at
            ],
            'level' => $currentLevel,
            'children' => []
        ];

        // Get direct children (3x3 matrix allows 3 children per position)
        $children = MatrixPosition::where('sponsor_id', $position->user_id)
            ->where('level', $currentLevel + 1)
            ->where('is_active', true)
            ->with('user.currentInvestmentTier')
            ->orderBy('position')
            ->get();

        foreach ($children as $child) {
            $structure['children'][] = $this->buildMatrixLevel($child, $currentLevel + 1, $maxLevel);
        }

        return $structure;
    }

    private function findSpilloverPosition(User $sponsor, int $currentLevel, int $maxLevel): ?array
    {
        if ($currentLevel >= $maxLevel) {
            return null;
        }

        // Get all positions at current level under this sponsor
        $positions = MatrixPosition::where('sponsor_id', $sponsor->id)
            ->where('level', $currentLevel)
            ->where('is_active', true)
            ->with('user')
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
                    'position' => $childrenCount + 1
                ];
            }

            // Recursively check deeper levels
            $spillover = $this->findSpilloverPosition($position->user, $currentLevel + 1, $maxLevel);
            if ($spillover) {
                return $spillover;
            }
        }

        return null;
    }

    private function getMatrixCountAtLevel(User $user, int $level): int
    {
        if ($level === 1) {
            return MatrixPosition::where('sponsor_id', $user->id)
                ->where('level', 1)
                ->where('is_active', true)
                ->count();
        }

        // For deeper levels, we need to count recursively
        $currentLevelUsers = collect([$user]);
        
        for ($currentDepth = 1; $currentDepth <= $level; $currentDepth++) {
            $nextLevelUsers = collect();
            foreach ($currentLevelUsers as $currentUser) {
                $children = MatrixPosition::where('sponsor_id', $currentUser->id)
                    ->where('level', $currentDepth)
                    ->where('is_active', true)
                    ->with('user')
                    ->get()
                    ->pluck('user');
                $nextLevelUsers = $nextLevelUsers->merge($children);
            }
            $currentLevelUsers = $nextLevelUsers;
            
            // If we've reached the target level, return the count
            if ($currentDepth === $level) {
                return $currentLevelUsers->count();
            }
        }

        return 0;
    }

    private function formatUserData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'referral_code' => $user->referral_code,
            'total_investment_amount' => $user->total_investment_amount,
            'total_referral_earnings' => $user->total_referral_earnings,
            'total_profit_earnings' => $user->total_profit_earnings,
            'tier' => $user->currentInvestmentTier?->name,
            'active_investments_count' => $user->investments()->where('status', 'active')->count(),
            'joined_at' => $user->created_at
        ];
    }

    private function formatUserDataWithInvestments(User $user, Collection $investments): array
    {
        $userData = $this->formatUserData($user);
        
        $userData['investments'] = $investments->map(function($investment) {
            return [
                'id' => $investment->id,
                'amount' => $investment->amount,
                'tier' => $investment->tier,
                'status' => $investment->status,
                'created_at' => $investment->created_at,
                'current_value' => $investment->getCurrentValue()
            ];
        })->toArray();

        $userData['total_invested'] = $investments->sum('amount');
        $userData['total_current_value'] = $investments->sum(function($investment) {
            return $investment->getCurrentValue();
        });

        return $userData;
    }
}