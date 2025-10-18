<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatrixPosition extends Model
{
    use HasFactory;
    
    // 7-Level Professional Progression System
    public const MAX_LEVELS = 7;
    public const MAX_CHILDREN = 3; // 3x3 matrix
    
    // Professional level names
    public const LEVEL_NAMES = [
        1 => 'Associate',
        2 => 'Professional',
        3 => 'Senior',
        4 => 'Manager',
        5 => 'Director',
        6 => 'Executive',
        7 => 'Ambassador',
    ];
    
    // Network capacity per level
    public const LEVEL_CAPACITY = [
        1 => 3,      // Associate: 3 positions
        2 => 9,      // Professional: 9 positions
        3 => 27,     // Senior: 27 positions
        4 => 81,     // Manager: 81 positions
        5 => 243,    // Director: 243 positions
        6 => 729,    // Executive: 729 positions
        7 => 2187,   // Ambassador: 2,187 positions
    ];
    
    protected $fillable = [
        'user_id',
        'sponsor_id',
        'level',
        'position',
        'left_child_id',
        'middle_child_id',
        'right_child_id',
        'is_active',
        'placed_at',
        'professional_level_name',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'placed_at' => 'datetime',
    ];
    
    protected $appends = ['level_name', 'network_capacity'];

    /**
     * Get the user who owns this matrix position
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sponsor who placed this user
     */
    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sponsor_id');
    }

    /**
     * Get the left child user
     */
    public function leftChild(): BelongsTo
    {
        return $this->belongsTo(User::class, 'left_child_id');
    }

    /**
     * Get the middle child user
     */
    public function middleChild(): BelongsTo
    {
        return $this->belongsTo(User::class, 'middle_child_id');
    }

    /**
     * Get the right child user
     */
    public function rightChild(): BelongsTo
    {
        return $this->belongsTo(User::class, 'right_child_id');
    }

    /**
     * Get the professional level name for this position
     */
    public function getLevelNameAttribute(): string
    {
        return self::LEVEL_NAMES[$this->level] ?? "Level {$this->level}";
    }
    
    /**
     * Get the network capacity for this level
     */
    public function getNetworkCapacityAttribute(): int
    {
        return self::LEVEL_CAPACITY[$this->level] ?? 0;
    }
    
    /**
     * Get professional level name by level number
     */
    public static function getProfessionalLevelName(int $level): string
    {
        return self::LEVEL_NAMES[$level] ?? "Level {$level}";
    }
    
    /**
     * Get total network capacity up to a specific level
     */
    public static function getTotalNetworkCapacity(int $maxLevel = self::MAX_LEVELS): int
    {
        $total = 0;
        for ($i = 1; $i <= min($maxLevel, self::MAX_LEVELS); $i++) {
            $total += self::LEVEL_CAPACITY[$i];
        }
        return $total;
    }
    
    /**
     * Scope to get level 1 positions
     */
    public function scopeIsLevel1($query)
    {
        return $query->where('level', 1);
    }
    
    /**
     * Scope to get positions by professional level
     */
    public function scopeByProfessionalLevel($query, string $levelName)
    {
        $level = array_search($levelName, self::LEVEL_NAMES);
        return $level !== false ? $query->where('level', $level) : $query->whereRaw('1 = 0');
    }

    /**
     * Scope to get active positions
     */
    public function scopeIsActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get positions for a specific sponsor
     */
    public function scopeForSponsor($query, $sponsorId)
    {
        return $query->where('sponsor_id', $sponsorId);
    }

    /**
     * Check if this position is full (has all 3 children)
     */
    public function isFull(): bool
    {
        return $this->left_child_id !== null && 
               $this->middle_child_id !== null && 
               $this->right_child_id !== null;
    }

    /**
     * Get available positions (empty child slots)
     */
    public function availablePositions(): array
    {
        $available = [];
        
        if ($this->left_child_id === null) {
            $available[] = 'left';
        }
        
        if ($this->middle_child_id === null) {
            $available[] = 'middle';
        }
        
        if ($this->right_child_id === null) {
            $available[] = 'right';
        }
        
        return $available;
    }

    /**
     * Get all children users
     */
    public function getChildren(): array
    {
        $children = [];
        
        if ($this->left_child_id) {
            $children[] = $this->leftChild;
        }
        
        if ($this->middle_child_id) {
            $children[] = $this->middleChild;
        }
        
        if ($this->right_child_id) {
            $children[] = $this->rightChild;
        }
        
        return $children;
    }

    /**
     * Get position path information
     */
    public function getPositionPath(): array
    {
        return [
            'level' => $this->level,
            'position' => $this->position,
            'coordinates' => $this->getMatrixCoordinates(),
            'path' => $this->calculatePositionPath()
        ];
    }

    /**
     * Check if this position can add a child
     */
    public function canAddChild(): bool
    {
        return $this->is_active && !$this->isFull();
    }

    /**
     * Add a child to the next available position
     */
    public function addChild(int $childId): bool
    {
        if (!$this->canAddChild()) {
            return false;
        }

        if ($this->left_child_id === null) {
            $this->left_child_id = $childId;
        } elseif ($this->middle_child_id === null) {
            $this->middle_child_id = $childId;
        } elseif ($this->right_child_id === null) {
            $this->right_child_id = $childId;
        } else {
            return false;
        }

        $this->save();
        return true;
    }

    /**
     * Get matrix coordinates for visualization
     */
    public function getMatrixCoordinates(): array
    {
        // Calculate row and column based on level and position
        $row = $this->level;
        
        // Calculate column based on position within level
        $positionsPerLevel = pow(3, $this->level - 1);
        $column = ($this->position - 1) % 3;
        
        return [
            'row' => $row,
            'column' => $column,
            'x' => $column * 100, // For visual positioning
            'y' => $row * 100
        ];
    }

    /**
     * Calculate position path from root
     */
    protected function calculatePositionPath(): array
    {
        $path = [];
        $currentLevel = 1;
        $currentPosition = $this->position;
        
        while ($currentLevel <= $this->level) {
            $path[] = [
                'level' => $currentLevel,
                'level_name' => self::getProfessionalLevelName($currentLevel),
                'position' => $currentPosition
            ];
            
            // Calculate parent position for next level up
            $currentPosition = ceil($currentPosition / 3);
            $currentLevel++;
        }
        
        return array_reverse($path);
    }
    
    /**
     * Get network statistics for this position
     */
    public function getNetworkStatistics(): array
    {
        return [
            'level' => $this->level,
            'level_name' => $this->level_name,
            'position' => $this->position,
            'is_full' => $this->isFull(),
            'available_positions' => count($this->availablePositions()),
            'children_count' => count($this->getChildren()),
            'network_capacity' => $this->network_capacity,
            'is_active' => $this->is_active,
        ];
    }
    
    /**
     * Check if this position is at maximum depth
     */
    public function isAtMaxDepth(): bool
    {
        return $this->level >= self::MAX_LEVELS;
    }
    
    /**
     * Get the next level information
     */
    public function getNextLevelInfo(): ?array
    {
        if ($this->isAtMaxDepth()) {
            return null;
        }
        
        $nextLevel = $this->level + 1;
        return [
            'level' => $nextLevel,
            'level_name' => self::getProfessionalLevelName($nextLevel),
            'capacity' => self::LEVEL_CAPACITY[$nextLevel],
        ];
    }
    
    /**
     * Get all level information for the matrix
     */
    public static function getAllLevelsInfo(): array
    {
        $levels = [];
        foreach (self::LEVEL_NAMES as $level => $name) {
            $levels[] = [
                'level' => $level,
                'name' => $name,
                'capacity' => self::LEVEL_CAPACITY[$level],
                'max_children' => self::MAX_CHILDREN,
            ];
        }
        return $levels;
    }
}
