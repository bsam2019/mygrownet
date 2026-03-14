<?php

namespace App\Models\QuickInvoice;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSubscription extends Model
{
    use HasUuids;

    protected $table = 'quick_invoice_user_subscriptions';

    protected $fillable = [
        'user_id',
        'tier_id',
        'starts_at',
        'expires_at',
        'documents_used',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'documents_used' => 'integer',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(SubscriptionTier::class, 'tier_id');
    }

    /**
     * Get user's current active subscription
     */
    public static function getCurrentSubscription(int $userId): ?self
    {
        return self::where('user_id', $userId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with('tier')
            ->first();
    }

    /**
     * Get or create free subscription for user
     */
    public static function getOrCreateFreeSubscription(int $userId): self
    {
        $existing = self::getCurrentSubscription($userId);
        
        if ($existing) {
            return $existing;
        }

        $freeTier = SubscriptionTier::getFreeTier();
        
        return self::create([
            'user_id' => $userId,
            'tier_id' => $freeTier->id,
            'starts_at' => now(),
            'expires_at' => null, // Free tier never expires
            'documents_used' => 0,
            'is_active' => true,
        ]);
    }

    /**
     * Check if user can create more documents
     */
    public function canCreateDocument(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        // If documents_per_month is -1, it means unlimited
        if ($this->tier->documents_per_month == -1) {
            return true;
        }

        // Get current month usage
        $monthlyUsage = UsageTracking::getUserMonthlyUsage($this->user_id);
        
        return $monthlyUsage < $this->tier->documents_per_month;
    }

    /**
     * Get remaining documents for current month
     */
    public function getRemainingDocuments(): int
    {
        // If documents_per_month is -1, it means unlimited
        if ($this->tier->documents_per_month == -1) {
            return 999999; // Show a large number for unlimited
        }

        $monthlyUsage = UsageTracking::getUserMonthlyUsage($this->user_id);
        return max(0, $this->tier->documents_per_month - $monthlyUsage);
    }

    /**
     * Get usage percentage for current month
     */
    public function getUsagePercentage(): float
    {
        // If documents_per_month is -1, it means unlimited (0% usage)
        if ($this->tier->documents_per_month == -1) {
            return 0;
        }

        $monthlyUsage = UsageTracking::getUserMonthlyUsage($this->user_id);
        
        if ($this->tier->documents_per_month == 0) {
            return 0;
        }
        
        return min(100, ($monthlyUsage / $this->tier->documents_per_month) * 100);
    }

    /**
     * Increment document usage
     */
    public function incrementUsage(): void
    {
        $this->increment('documents_used');
    }
}