<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ModuleSpecialOffer extends Model
{
    protected $fillable = [
        'name',
        'description',
        'offer_type',
        'module_ids',
        'tier_key',
        'original_price',
        'offer_price',
        'savings_display',
        'billing_cycle',
        'bonus_features',
        'starts_at',
        'ends_at',
        'max_purchases',
        'current_purchases',
        'is_featured',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'module_ids' => 'array',
        'bonus_features' => 'array',
        'original_price' => 'decimal:2',
        'offer_price' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'max_purchases' => 'integer',
        'current_purchases' => 'integer',
        'is_featured' => 'boolean',
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

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeValid($query)
    {
        $now = Carbon::now();
        return $query->where('is_active', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now)
            ->where(function ($q) {
                $q->whereNull('max_purchases')
                    ->orWhereColumn('current_purchases', '<', 'max_purchases');
            });
    }

    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();

        if ($this->starts_at > $now || $this->ends_at < $now) {
            return false;
        }

        if ($this->max_purchases !== null && $this->current_purchases >= $this->max_purchases) {
            return false;
        }

        return true;
    }

    public function incrementPurchases(): void
    {
        $this->increment('current_purchases');
    }

    public function getSavingsAmountAttribute(): float
    {
        return $this->original_price - $this->offer_price;
    }

    public function getSavingsPercentAttribute(): float
    {
        if ($this->original_price <= 0) {
            return 0;
        }
        return round((($this->original_price - $this->offer_price) / $this->original_price) * 100, 1);
    }

    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        $now = Carbon::now();

        if ($this->starts_at > $now) {
            return 'scheduled';
        }

        if ($this->ends_at < $now) {
            return 'expired';
        }

        if ($this->max_purchases !== null && $this->current_purchases >= $this->max_purchases) {
            return 'sold_out';
        }

        return 'active';
    }

    public function getRemainingAttribute(): ?int
    {
        if ($this->max_purchases === null) {
            return null;
        }
        return max(0, $this->max_purchases - $this->current_purchases);
    }

    public function getTimeRemainingAttribute(): string
    {
        if ($this->ends_at < Carbon::now()) {
            return 'Expired';
        }
        return $this->ends_at->diffForHumans();
    }
}
