<?php

namespace App\Services;

use App\Models\User;
use App\Models\MatrixPosition;
use App\Models\ReferralCommission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MatrixService
{
    /**
     * Place a new user in the matrix
     */
    public function placeUserInMatrix(User $user, ?User $sponsor = null): MatrixPosition
    {
        DB::beginTransaction();
        
        try {
            // If no sponsor, user is at root level
            if (!$sponsor) {
                $position = $this->createRootPosition($user);
            } else {
                $position = $this->findAndPlaceInMatrix($user, $sponsor);
            }
            
            DB::commit();
            
            Log::info('User placed in matrix', [
                'user_id' => $user->id,
                'sponsor_id' => $sponsor?->id,
                'level' => $position->level,
                'position' => $position->position,
            ]);
            
            return $position;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to place user in matrix', [
                'user_id' => $user->id,
                'sponsor_id' => $sponsor?->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
    
    /**
     * Create root position for user without sponsor
     */
    protected function createRootPosition(User $user): MatrixPosition
    {
        return MatrixPosition::create([
            'user_id' => $user->id,
            'sponsor_id' => null,
            'level' => 1,
            'position' => 1,
            'is_active' => true,
            'placed_at' => now(),
        ]);
    }
    
    /**
     * Find available position and place user
     */
    protected function findAndPlaceInMatrix(User $user, User $sponsor): MatrixPosition
    {
        // Find sponsor's position
        $sponsorPosition = MatrixPosition::where('user_id', $sponsor->id)
            ->where('is_active', true)
            ->first();
        
        if (!$sponsorPosition) {
            throw new \Exception('Sponsor does not have an active matrix position');
        }
        
        // Try to place directly under sponsor
        if (!$sponsorPosition->isFull()) {
            return $this->placeUnderPosition($user, $sponsorPosition, $sponsor->id);
        }
        
        // Find next available position in sponsor's downline (spillover)
        $availablePosition = $this->findNextAvailablePosition($sponsorPosition);
        
        if (!$availablePosition) {
            throw new \Exception('No available positions found in matrix');
        }
        
        return $this->placeUnderPosition($user, $availablePosition, $sponsor->id);
    }
    
    /**
     * Place user under a specific position
     */
    protected function placeUnderPosition(User $user, MatrixPosition $parentPosition, int $sponsorId): MatrixPosition
    {
        // Determine which child slot to use
        $childSlot = null;
        if ($parentPosition->left_child_id === null) {
            $childSlot = 'left';
        } elseif ($parentPosition->middle_child_id === null) {
            $childSlot = 'middle';
        } elseif ($parentPosition->right_child_id === null) {
            $childSlot = 'right';
        }
        
        if (!$childSlot) {
            throw new \Exception('Parent position is full');
        }
        
        // Calculate new position details
        $newLevel = $parentPosition->level + 1;
        
        // Check if we've exceeded max levels
        if ($newLevel > MatrixPosition::MAX_LEVELS) {
            throw new \Exception('Maximum matrix depth reached');
        }
        
        $newPosition = $this->calculateChildPosition($parentPosition, $childSlot);
        
        // Create new matrix position
        $matrixPosition = MatrixPosition::create([
            'user_id' => $user->id,
            'sponsor_id' => $sponsorId,
            'level' => $newLevel,
            'position' => $newPosition,
            'is_active' => true,
            'placed_at' => now(),
        ]);
        
        // Update parent position
        $parentPosition->update([
            "{$childSlot}_child_id" => $user->id,
        ]);
        
        return $matrixPosition;
    }
    
    /**
     * Find next available position using breadth-first search
     */
    protected function findNextAvailablePosition(MatrixPosition $startPosition): ?MatrixPosition
    {
        $queue = [$startPosition];
        $visited = [];
        
        while (!empty($queue)) {
            $currentPosition = array_shift($queue);
            
            // Skip if already visited
            if (in_array($currentPosition->id, $visited)) {
                continue;
            }
            $visited[] = $currentPosition->id;
            
            // Check if this position has space
            if (!$currentPosition->isFull() && $currentPosition->level < MatrixPosition::MAX_LEVELS) {
                return $currentPosition;
            }
            
            // Add children to queue
            foreach ($currentPosition->getChildren() as $child) {
                if ($child && !in_array($child->id, $visited)) {
                    $childPosition = MatrixPosition::where('user_id', $child->id)->first();
                    if ($childPosition) {
                        $queue[] = $childPosition;
                    }
                }
            }
        }
        
        return null;
    }
    
    /**
     * Calculate child position number
     */
    protected function calculateChildPosition(MatrixPosition $parent, string $childSlot): int
    {
        $basePosition = ($parent->position - 1) * 3;
        
        return match($childSlot) {
            'left' => $basePosition + 1,
            'middle' => $basePosition + 2,
            'right' => $basePosition + 3,
            default => $basePosition + 1,
        };
    }
    
    /**
     * Get user's complete network tree
     */
    public function getUserNetworkTree(User $user, int $maxDepth = 7): array
    {
        $position = MatrixPosition::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
        
        if (!$position) {
            return [];
        }
        
        return $this->buildNetworkTree($position, $maxDepth);
    }
    
    /**
     * Build network tree recursively
     */
    protected function buildNetworkTree(MatrixPosition $position, int $maxDepth, int $currentDepth = 0): array
    {
        if ($currentDepth >= $maxDepth) {
            return [];
        }
        
        $tree = [
            'user_id' => $position->user_id,
            'user' => $position->user,
            'level' => $position->level,
            'level_name' => $position->level_name,
            'position' => $position->position,
            'is_full' => $position->isFull(),
            'children' => [],
        ];
        
        // Get children
        $childIds = array_filter([
            $position->left_child_id,
            $position->middle_child_id,
            $position->right_child_id,
        ]);
        
        foreach ($childIds as $childId) {
            $childPosition = MatrixPosition::where('user_id', $childId)
                ->where('is_active', true)
                ->first();
            
            if ($childPosition) {
                $tree['children'][] = $this->buildNetworkTree($childPosition, $maxDepth, $currentDepth + 1);
            }
        }
        
        return $tree;
    }
    
    /**
     * Get network statistics for a user
     */
    public function getNetworkStatistics(User $user): array
    {
        $position = MatrixPosition::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
        
        if (!$position) {
            return [
                'has_position' => false,
                'total_network_size' => 0,
                'by_level' => [],
            ];
        }
        
        $networkSize = $this->calculateNetworkSize($position);
        
        return [
            'has_position' => true,
            'current_level' => $position->level,
            'current_level_name' => $position->level_name,
            'position_number' => $position->position,
            'is_full' => $position->isFull(),
            'available_slots' => count($position->availablePositions()),
            'total_network_size' => $networkSize['total'],
            'by_level' => $networkSize['by_level'],
            'max_possible_network' => MatrixPosition::getTotalNetworkCapacity(),
            'network_completion_percentage' => ($networkSize['total'] / MatrixPosition::getTotalNetworkCapacity()) * 100,
        ];
    }
    
    /**
     * Calculate total network size
     */
    protected function calculateNetworkSize(MatrixPosition $position): array
    {
        $total = 0;
        $byLevel = [];
        
        for ($level = 1; $level <= MatrixPosition::MAX_LEVELS; $level++) {
            $byLevel[$level] = [
                'level_name' => MatrixPosition::getProfessionalLevelName($level),
                'count' => 0,
                'capacity' => MatrixPosition::LEVEL_CAPACITY[$level],
            ];
        }
        
        $this->countNetworkMembers($position, $byLevel, $total);
        
        return [
            'total' => $total,
            'by_level' => $byLevel,
        ];
    }
    
    /**
     * Count network members recursively
     */
    protected function countNetworkMembers(MatrixPosition $position, array &$byLevel, int &$total): void
    {
        $childIds = array_filter([
            $position->left_child_id,
            $position->middle_child_id,
            $position->right_child_id,
        ]);
        
        foreach ($childIds as $childId) {
            $childPosition = MatrixPosition::where('user_id', $childId)
                ->where('is_active', true)
                ->first();
            
            if ($childPosition) {
                $total++;
                $byLevel[$childPosition->level]['count']++;
                $this->countNetworkMembers($childPosition, $byLevel, $total);
            }
        }
    }
    
    /**
     * Get matrix overview statistics
     */
    public function getMatrixOverview(): array
    {
        return [
            'total_positions' => MatrixPosition::where('is_active', true)->count(),
            'filled_positions' => MatrixPosition::where('is_active', true)
                ->where(function($q) {
                    $q->whereNotNull('left_child_id')
                      ->orWhereNotNull('middle_child_id')
                      ->orWhereNotNull('right_child_id');
                })
                ->count(),
            'full_positions' => MatrixPosition::where('is_active', true)
                ->whereNotNull('left_child_id')
                ->whereNotNull('middle_child_id')
                ->whereNotNull('right_child_id')
                ->count(),
            'by_level' => $this->getPositionsByLevel(),
            'spillover_count' => $this->getSpilloverCount(),
        ];
    }
    
    /**
     * Get positions grouped by level
     */
    protected function getPositionsByLevel(): array
    {
        $positions = MatrixPosition::where('is_active', true)
            ->selectRaw('level, COUNT(*) as count')
            ->groupBy('level')
            ->get();
        
        $result = [];
        foreach (MatrixPosition::LEVEL_NAMES as $level => $name) {
            $count = $positions->firstWhere('level', $level)?->count ?? 0;
            $result[$level] = [
                'level' => $level,
                'name' => $name,
                'count' => $count,
                'capacity' => MatrixPosition::LEVEL_CAPACITY[$level],
                'fill_percentage' => MatrixPosition::LEVEL_CAPACITY[$level] > 0 
                    ? ($count / MatrixPosition::LEVEL_CAPACITY[$level]) * 100 
                    : 0,
            ];
        }
        
        return $result;
    }
    
    /**
     * Get spillover count (users placed outside direct sponsor line)
     */
    protected function getSpilloverCount(): int
    {
        // Count positions where the direct parent is not the sponsor
        return MatrixPosition::where('is_active', true)
            ->whereColumn('sponsor_id', '!=', DB::raw('(
                SELECT user_id FROM matrix_positions mp2 
                WHERE mp2.left_child_id = matrix_positions.user_id 
                   OR mp2.middle_child_id = matrix_positions.user_id 
                   OR mp2.right_child_id = matrix_positions.user_id 
                LIMIT 1
            )'))
            ->count();
    }
    
    /**
     * Get professional level progression for a user
     */
    public function getUserLevelProgression(User $user): array
    {
        $stats = $this->getNetworkStatistics($user);
        
        if (!$stats['has_position']) {
            return [
                'current_level' => null,
                'progression' => [],
            ];
        }
        
        $progression = [];
        foreach (MatrixPosition::LEVEL_NAMES as $level => $name) {
            $levelStats = $stats['by_level'][$level] ?? ['count' => 0, 'capacity' => 0];
            $progression[] = [
                'level' => $level,
                'name' => $name,
                'current_count' => $levelStats['count'],
                'capacity' => $levelStats['capacity'],
                'completion_percentage' => $levelStats['capacity'] > 0 
                    ? ($levelStats['count'] / $levelStats['capacity']) * 100 
                    : 0,
                'is_current_level' => $level == $stats['current_level'],
                'commission_rate' => ReferralCommission::getCommissionRate($level),
            ];
        }
        
        return [
            'current_level' => $stats['current_level'],
            'current_level_name' => $stats['current_level_name'],
            'progression' => $progression,
            'total_network_size' => $stats['total_network_size'],
            'network_completion' => $stats['network_completion_percentage'],
        ];
    }
}
