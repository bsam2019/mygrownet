<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

/**
 * Module Usage Model
 * 
 * Tracks usage metrics for freemium modules (transactions, storage, etc.)
 */
class ModuleUsageModel extends Model
{
    protected $table = 'module_usage';

    protected $fillable = [
        'user_id',
        'module_id',
        'usage_type',
        'count',
        'period_start',
        'period_end',
    ];

    protected $casts = [
        'count' => 'integer',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    /**
     * Get the user that owns this usage record
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the module this usage belongs to
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(ModuleModel::class, 'module_id', 'id');
    }

    /**
     * Scope to filter by user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by module
     */
    public function scopeForModule($query, string $moduleId)
    {
        return $query->where('module_id', $moduleId);
    }

    /**
     * Scope to filter by usage type
     */
    public function scopeOfType($query, string $usageType)
    {
        return $query->where('usage_type', $usageType);
    }

    /**
     * Scope to filter by current period
     */
    public function scopeCurrentPeriod($query)
    {
        $now = now();
        return $query->where('period_start', '<=', $now)
                     ->where('period_end', '>=', $now);
    }

    /**
     * Scope to filter by specific period
     */
    public function scopeForPeriod($query, $periodStart, $periodEnd)
    {
        return $query->where('period_start', $periodStart)
                     ->where('period_end', $periodEnd);
    }

    /**
     * Get or create usage record for current period
     */
    public static function getOrCreateForCurrentPeriod(
        int $userId,
        string $moduleId,
        string $usageType
    ): self {
        $periodStart = now()->startOfMonth();
        $periodEnd = now()->endOfMonth();

        return self::firstOrCreate(
            [
                'user_id' => $userId,
                'module_id' => $moduleId,
                'usage_type' => $usageType,
                'period_start' => $periodStart,
            ],
            [
                'period_end' => $periodEnd,
                'count' => 0,
            ]
        );
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(int $amount = 1): self
    {
        $this->increment('count', $amount);
        return $this;
    }

    /**
     * Check if usage is within limit
     */
    public function isWithinLimit(int $limit): bool
    {
        if ($limit === -1) {
            return true; // Unlimited
        }
        return $this->count < $limit;
    }

    /**
     * Get remaining usage
     */
    public function getRemainingUsage(int $limit): int
    {
        if ($limit === -1) {
            return PHP_INT_MAX; // Unlimited
        }
        return max(0, $limit - $this->count);
    }
}
