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
        'trial_ends_at',
        'documents_used',
        'is_active',
        'billing_cycle',
        'last_payment_at',
        'last_payment_amount',
        'payment_method',
        'payment_reference',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'documents_used' => 'integer',
        'is_active' => 'boolean',
        'last_payment_at' => 'datetime',
        'last_payment_amount' => 'decimal:2',
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
     * Get or create free subscription for user.
     * If trial settings exist and this is a new user, start the trial period.
     */
    public static function getOrCreateFreeSubscription(int $userId): self
    {
        $existing = self::getCurrentSubscription($userId);
        
        if ($existing) {
            return $existing;
        }

        $freeTier = SubscriptionTier::getFreeTier();

        // Check if trial should be applied
        $trialSettings = AdminSetting::get('trial_settings', []);
        $trialDays = $trialSettings['trial_days'] ?? 0;
        $tierOnTrial = $trialSettings['tier_on_trial'] ?? null;

        if ($trialDays > 0 && $tierOnTrial) {
            $trialTier = SubscriptionTier::where('name', $tierOnTrial)
                ->where('is_active', true)
                ->first();

            if ($trialTier && $trialTier->id !== $freeTier->id) {
                return self::create([
                    'user_id' => $userId,
                    'tier_id' => $trialTier->id,
                    'starts_at' => now(),
                    'trial_ends_at' => now()->addDays($trialDays),
                    'expires_at' => now()->addDays($trialDays), // trial = first billing period
                    'documents_used' => 0,
                    'is_active' => true,
                ]);
            }
        }

        return self::create([
            'user_id' => $userId,
            'tier_id' => $freeTier->id,
            'starts_at' => now(),
            'expires_at' => null,
            'documents_used' => 0,
            'is_active' => true,
        ]);
    }

    /**
     * Check if user is on trial
     */
    public function onTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if trial has expired
     */
    public function trialExpired(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    /**
     * Check if subscription is paid (not trial, not free)
     */
    public function isPaid(): bool
    {
        return $this->last_payment_at !== null && $this->tier->price > 0;
    }

    /**
     * Check if user can create more documents
     */
    public function canCreateDocument(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Allow during trial period regardless of expiry
        if ($this->onTrial()) {
            return $this->checkDocumentLimit();
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return $this->checkDocumentLimit();
    }

    private function checkDocumentLimit(): bool
    {
        if ($this->tier->documents_per_month == -1) {
            return true;
        }

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