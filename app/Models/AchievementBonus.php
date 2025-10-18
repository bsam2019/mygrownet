<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AchievementBonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'achievement_id',
        'bonus_type',
        'amount',
        'status',
        'earned_at',
        'paid_at',
        'payment_transaction_id',
        'payment_method',
        'tier_at_earning',
        'team_volume_at_earning',
        'active_referrals_at_earning',
        'description',
        'metadata'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'team_volume_at_earning' => 'decimal:2',
        'active_referrals_at_earning' => 'integer',
        'earned_at' => 'datetime',
        'paid_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public const BONUS_TYPES = [
        'tier_advancement' => 'Tier Advancement',
        'performance' => 'Performance',
        'leadership' => 'Leadership',
        'milestone' => 'Milestone',
        'special' => 'Special Event'
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'cancelled' => 'Cancelled',
        'expired' => 'Expired'
    ];

    public const PAYMENT_METHODS = [
        'wallet' => 'Wallet Balance',
        'mobile_money' => 'Mobile Money',
        'bank_transfer' => 'Bank Transfer'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class);
    }

    /**
     * Scope for specific bonus types
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('bonus_type', $type);
    }

    /**
     * Scope for specific statuses
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for pending bonuses
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for paid bonuses
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope for bonuses within date range
     */
    public function scopeWithinPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('earned_at', [$startDate, $endDate]);
    }

    /**
     * Scope for bonuses earned in specific period
     */
    public function scopeEarnedInPeriod($query, string $period = 'month')
    {
        $startDate = match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth()
        };

        return $query->where('earned_at', '>=', $startDate);
    }

    /**
     * Scope for tier advancement bonuses
     */
    public function scopeTierAdvancement($query)
    {
        return $query->where('bonus_type', 'tier_advancement');
    }

    /**
     * Scope for performance bonuses
     */
    public function scopePerformance($query)
    {
        return $query->where('bonus_type', 'performance');
    }

    /**
     * Scope for leadership bonuses
     */
    public function scopeLeadership($query)
    {
        return $query->where('bonus_type', 'leadership');
    }

    /**
     * Check if bonus is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if bonus is paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if bonus is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if bonus is expired
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' || 
               ($this->isPending() && $this->earned_at->addDays(30) < now());
    }

    /**
     * Check if bonus is eligible for payment
     */
    public function isEligibleForPayment(): bool
    {
        return $this->isPending() && 
               !$this->isExpired() &&
               $this->amount > 0 &&
               $this->user &&
               !$this->user->is_blocked;
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'K' . number_format($this->amount, 2);
    }

    /**
     * Get bonus type label
     */
    public function getBonusTypeLabelAttribute(): string
    {
        return self::BONUS_TYPES[$this->bonus_type] ?? $this->bonus_type;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get payment method label
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Get days since earned
     */
    public function getDaysSinceEarnedAttribute(): int
    {
        return $this->earned_at->diffInDays(now());
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiryAttribute(): int
    {
        $expiryDate = $this->earned_at->addDays(30);
        return max(0, now()->diffInDays($expiryDate, false));
    }

    /**
     * Mark bonus as paid
     */
    public function markAsPaid(string $paymentMethod = 'wallet', ?int $transactionId = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => $paymentMethod,
            'payment_transaction_id' => $transactionId
        ]);
    }

    /**
     * Cancel bonus
     */
    public function cancel(string $reason = null): void
    {
        $metadata = $this->metadata ?? [];
        $metadata['cancellation_reason'] = $reason;
        $metadata['cancelled_at'] = now()->toISOString();

        $this->update([
            'status' => 'cancelled',
            'metadata' => $metadata
        ]);
    }

    /**
     * Expire bonus
     */
    public function expire(): void
    {
        $this->update([
            'status' => 'expired'
        ]);
    }

    /**
     * Get bonus summary for user
     */
    public static function getUserBonusSummary(int $userId, string $period = 'month'): array
    {
        $startDate = match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth()
        };

        $bonuses = self::where('user_id', $userId)
            ->where('earned_at', '>=', $startDate)
            ->get();

        return [
            'total_bonuses' => $bonuses->count(),
            'total_amount' => $bonuses->sum('amount'),
            'paid_bonuses' => $bonuses->where('status', 'paid')->count(),
            'paid_amount' => $bonuses->where('status', 'paid')->sum('amount'),
            'pending_bonuses' => $bonuses->where('status', 'pending')->count(),
            'pending_amount' => $bonuses->where('status', 'pending')->sum('amount'),
            'by_type' => $bonuses->groupBy('bonus_type')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('amount'),
                    'paid' => $group->where('status', 'paid')->count()
                ];
            })
        ];
    }

    /**
     * Get recent bonuses for user
     */
    public static function getRecentBonuses(int $userId, int $limit = 10): array
    {
        return self::where('user_id', $userId)
            ->with('achievement')
            ->orderBy('earned_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($bonus) {
                return [
                    'id' => $bonus->id,
                    'type' => $bonus->bonus_type_label,
                    'achievement' => $bonus->achievement?->name,
                    'amount' => $bonus->formatted_amount,
                    'status' => $bonus->status_label,
                    'earned_at' => $bonus->earned_at,
                    'paid_at' => $bonus->paid_at,
                    'description' => $bonus->description
                ];
            })
            ->toArray();
    }

    /**
     * Get bonus statistics for period
     */
    public static function getStatistics(string $period = 'month'): array
    {
        $startDate = match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth()
        };

        $bonuses = self::where('earned_at', '>=', $startDate)->get();

        return [
            'total_bonuses' => $bonuses->count(),
            'total_amount' => $bonuses->sum('amount'),
            'paid_bonuses' => $bonuses->where('status', 'paid')->count(),
            'paid_amount' => $bonuses->where('status', 'paid')->sum('amount'),
            'pending_bonuses' => $bonuses->where('status', 'pending')->count(),
            'pending_amount' => $bonuses->where('status', 'pending')->sum('amount'),
            'success_rate' => $bonuses->count() > 0 
                ? ($bonuses->where('status', 'paid')->count() / $bonuses->count()) * 100 
                : 0,
            'average_amount' => $bonuses->avg('amount'),
            'by_type' => $bonuses->groupBy('bonus_type')->map(function ($group, $type) {
                return [
                    'type' => self::BONUS_TYPES[$type] ?? $type,
                    'count' => $group->count(),
                    'amount' => $group->sum('amount'),
                    'paid' => $group->where('status', 'paid')->count(),
                    'success_rate' => $group->count() > 0 
                        ? ($group->where('status', 'paid')->count() / $group->count()) * 100 
                        : 0
                ];
            }),
            'top_earners' => $bonuses->groupBy('user_id')
                ->map(function ($group) {
                    return [
                        'user_id' => $group->first()->user_id,
                        'user_name' => $group->first()->user->name,
                        'total_amount' => $group->sum('amount'),
                        'bonus_count' => $group->count()
                    ];
                })
                ->sortByDesc('total_amount')
                ->take(10)
                ->values()
        ];
    }

    /**
     * Expire old pending bonuses
     */
    public static function expireOldBonuses(): int
    {
        $expiredCount = self::where('status', 'pending')
            ->where('earned_at', '<', now()->subDays(30))
            ->update(['status' => 'expired']);

        return $expiredCount;
    }
}