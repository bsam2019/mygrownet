<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ModuleDiscount extends Model
{
    protected $fillable = [
        'module_id',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'applies_to',
        'tier_keys',
        'code',
        'starts_at',
        'ends_at',
        'max_uses',
        'current_uses',
        'min_purchase_amount',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'tier_keys' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'max_uses' => 'integer',
        'current_uses' => 'integer',
        'min_purchase_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        $now = Carbon::now();
        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->where(function ($q) {
                $q->whereNull('max_uses')
                    ->orWhereColumn('current_uses', '<', 'max_uses');
            });
    }

    public function scopeForModule($query, ?string $moduleId)
    {
        return $query->where(function ($q) use ($moduleId) {
            $q->whereNull('module_id')->orWhere('module_id', $moduleId);
        });
    }

    public function scopeWithCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();

        if ($this->starts_at && $this->starts_at > $now) {
            return false;
        }

        if ($this->ends_at && $this->ends_at < $now) {
            return false;
        }

        if ($this->max_uses !== null && $this->current_uses >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function appliesToTier(string $tierKey): bool
    {
        if ($this->applies_to === 'all_tiers') {
            return true;
        }

        if ($this->applies_to === 'specific_tiers') {
            return in_array($tierKey, $this->tier_keys ?? []);
        }

        return true;
    }

    public function appliesToBillingCycle(string $billingCycle): bool
    {
        return match($this->applies_to) {
            'annual_only' => $billingCycle === 'annual',
            'monthly_only' => $billingCycle === 'monthly',
            default => true,
        };
    }

    public function calculateDiscount(float $originalPrice): float
    {
        if ($this->min_purchase_amount && $originalPrice < $this->min_purchase_amount) {
            return 0;
        }

        return match($this->discount_type) {
            'percentage' => $originalPrice * ($this->discount_value / 100),
            'fixed' => min($this->discount_value, $originalPrice),
            default => 0,
        };
    }

    public function incrementUsage(): void
    {
        $this->increment('current_uses');
    }

    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        $now = Carbon::now();

        if ($this->starts_at && $this->starts_at > $now) {
            return 'scheduled';
        }

        if ($this->ends_at && $this->ends_at < $now) {
            return 'expired';
        }

        if ($this->max_uses !== null && $this->current_uses >= $this->max_uses) {
            return 'exhausted';
        }

        return 'active';
    }
}
