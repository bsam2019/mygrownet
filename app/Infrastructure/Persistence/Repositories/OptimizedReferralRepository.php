<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Models\User;
use App\Models\MatrixPosition;
use App\Models\ReferralCommission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class OptimizedReferralRepository
{
    /**
     * Get referral tree with optimized queries and caching
     */
    public function getReferralTreeOptimized(User $user, int $maxLevel = 3): array
    {
        $cacheKey = "referral_tree_{$user->id}_{$maxLevel}";
        
        return Cache::remember($cacheKey, 300, function () use ($user, $maxLevel) {
            return $this->buildReferralTreeRecursive($user, 1, $maxLevel);
        });
    }

    /**
     * Build referral tree recursively with optimized queries
     */
    private function buildReferralTreeRecursive(User $user, int $currentLevel, int $maxLevel): array
    {
        if ($currentLevel > $maxLevel) {
            return [];
        }

        // Use optimized query with indexes
        $directReferrals = User::select([
                'id', 'name', 'email', 'referrer_id', 'referral_count', 
                'total_referral_earnings', 'created_at', 'last_referral_at'
            ])
            ->where('referrer_id', $user->id)
            ->orderBy('created_at')
            ->get();

        $tree = [];
        foreach ($directReferrals as $referral) {
            $tree[] = [
                'user' => $referral,
                'level' => $currentLevel,
                'children' => $this->buildReferralTreeRecursive($referral, $currentLevel + 1, $maxLevel),
                'commission_stats' => $this->getReferralCommissionStats($referral->id)
            ];
        }

        return $tree;
    }

    /**
     * Get referrals by specific level with pagination
     */
    public function getReferralsByLevelPaginated(User $user, int $level, int $perPage = 50): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        // Use recursive CTE for efficient level-based queries
        $query = "
            WITH RECURSIVE referral_levels AS (
                SELECT id, referrer_id, name, email, total_referral_earnings, 1 as level
                FROM users 
                WHERE referrer_id = ?
                
                UNION ALL
                
                SELECT u.id, u.referrer_id, u.name, u.email, u.total_referral_earnings, rl.level + 1
                FROM users u
                INNER JOIN referral_levels rl ON u.referrer_id = rl.id
                WHERE rl.level < ?
            )
            SELECT * FROM referral_levels WHERE level = ?
            ORDER BY total_referral_earnings DESC
        ";

        return DB::table(DB::raw("({$query}) as referral_data"))
            ->setBindings([$user->id, $level, $level])
            ->paginate($perPage);
    }

    /**
     * Get matrix structure with optimized queries
     */
    public function getMatrixStructureOptimized(User $user): array
    {
        $cacheKey = "matrix_structure_{$user->id}";
        
        return Cache::remember($cacheKey, 600, function () use ($user) {
            // Get all matrix positions for the user efficiently
            $positions = MatrixPosition::select([
                    'id', 'user_id', 'sponsor_id', 'level', 'position',
                    'left_child_id', 'middle_child_id', 'right_child_id',
                    'is_active', 'placed_at'
                ])
                ->where('user_id', $user->id)
                ->orderBy('level')
                ->orderBy('position')
                ->get();

            $matrix = [];
            foreach ($positions as $position) {
                $matrix[$position->level][$position->position] = [
                    'position' => $position,
                    'children' => $this->getMatrixChildren($position),
                    'commission_potential' => $this->calculateMatrixCommissionPotential($position)
                ];
            }

            return $matrix;
        });
    }

    /**
     * Get matrix children efficiently
     */
    private function getMatrixChildren(MatrixPosition $position): array
    {
        $childIds = array_filter([
            $position->left_child_id,
            $position->middle_child_id,
            $position->right_child_id
        ]);

        if (empty($childIds)) {
            return [];
        }

        return User::select(['id', 'name', 'email', 'total_referral_earnings'])
            ->whereIn('id', $childIds)
            ->get()
            ->keyBy('id')
            ->toArray();
    }

    /**
     * Find next available matrix position with optimized query
     */
    public function findNextAvailablePositionOptimized(User $sponsor): ?array
    {
        // Use optimized query to find available positions
        $availablePosition = DB::select("
            SELECT mp.level, mp.position + 1 as next_position
            FROM matrix_positions mp
            WHERE mp.sponsor_id = ?
            AND mp.is_active = 1
            AND (
                mp.left_child_id IS NULL OR 
                mp.middle_child_id IS NULL OR 
                mp.right_child_id IS NULL
            )
            ORDER BY mp.level, mp.position
            LIMIT 1
        ", [$sponsor->id]);

        if (!empty($availablePosition)) {
            return [
                'level' => $availablePosition[0]->level,
                'position' => $availablePosition[0]->next_position
            ];
        }

        // If no available positions, find next level
        $maxPosition = MatrixPosition::where('sponsor_id', $sponsor->id)
            ->where('is_active', true)
            ->max('position');

        return [
            'level' => 1, // Start from level 1 for new matrix
            'position' => ($maxPosition ?? 0) + 1
        ];
    }

    /**
     * Get referral commission stats efficiently
     */
    private function getReferralCommissionStats(int $userId): array
    {
        return Cache::remember("commission_stats_{$userId}", 300, function () use ($userId) {
            return ReferralCommission::select([
                    DB::raw('COUNT(*) as total_commissions'),
                    DB::raw('SUM(amount) as total_amount'),
                    DB::raw('AVG(amount) as average_amount'),
                    DB::raw('MAX(amount) as highest_amount'),
                    'level'
                ])
                ->where('referrer_id', $userId)
                ->where('status', 'paid')
                ->groupBy('level')
                ->get()
                ->keyBy('level')
                ->toArray();
        });
    }

    /**
     * Calculate matrix commission potential
     */
    private function calculateMatrixCommissionPotential(MatrixPosition $position): array
    {
        $childCount = collect([
            $position->left_child_id,
            $position->middle_child_id,
            $position->right_child_id
        ])->filter()->count();

        $maxChildren = 3;
        $potentialSlots = $maxChildren - $childCount;

        return [
            'filled_slots' => $childCount,
            'available_slots' => $potentialSlots,
            'completion_percentage' => ($childCount / $maxChildren) * 100,
            'level' => $position->level
        ];
    }

    /**
     * Get top performers with optimized query
     */
    public function getTopPerformersOptimized(int $limit = 10): Collection
    {
        return Cache::remember("top_performers_{$limit}", 900, function () use ($limit) {
            return User::select([
                    'id', 'name', 'email', 'total_referral_earnings',
                    'referral_count', 'last_referral_at'
                ])
                ->where('total_referral_earnings', '>', 0)
                ->orderBy('total_referral_earnings', 'desc')
                ->orderBy('referral_count', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get referral activity with date range and pagination
     */
    public function getReferralActivityPaginated(
        User $user, 
        ?\Carbon\Carbon $startDate = null, 
        ?\Carbon\Carbon $endDate = null,
        int $perPage = 20
    ): \Illuminate\Contracts\Pagination\LengthAwarePaginator {
        $query = ReferralCommission::select([
                'id', 'referrer_id', 'referred_id', 'investment_id',
                'amount', 'percentage', 'level', 'status', 'paid_at', 'created_at'
            ])
            ->with(['referred:id,name,email', 'investment:id,amount,tier'])
            ->where('referrer_id', $user->id);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Bulk update matrix positions efficiently
     */
    public function bulkUpdateMatrixPositions(array $updates): bool
    {
        if (empty($updates)) {
            return true;
        }

        try {
            DB::beginTransaction();

            foreach ($updates as $update) {
                MatrixPosition::where('id', $update['id'])
                    ->update($update['data']);
            }

            // Clear related caches
            $this->clearMatrixCaches($updates);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Clear matrix-related caches
     */
    private function clearMatrixCaches(array $updates): void
    {
        $userIds = collect($updates)->pluck('user_id')->unique();
        
        foreach ($userIds as $userId) {
            Cache::forget("matrix_structure_{$userId}");
            Cache::forget("referral_tree_{$userId}_3");
            Cache::forget("commission_stats_{$userId}");
        }
    }
}