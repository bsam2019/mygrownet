<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleTier extends Model
{
    protected $fillable = [
        'module_id',
        'tier_key',
        'name',
        'description',
        'price_monthly',
        'price_annual',
        'currency',
        'user_limit',
        'storage_limit_mb',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_annual' => 'decimal:2',
        'user_limit' => 'integer',
        'storage_limit_mb' => 'integer',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function features(): HasMany
    {
        return $this->hasMany(ModuleTierFeature::class);
    }

    public function activeFeatures(): HasMany
    {
        return $this->hasMany(ModuleTierFeature::class)->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForModule($query, string $moduleId)
    {
        return $query->where('module_id', $moduleId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price_monthly');
    }

    public function getAnnualSavingsAttribute(): float
    {
        $monthlyAnnual = $this->price_monthly * 12;
        return $monthlyAnnual - $this->price_annual;
    }

    public function getAnnualSavingsPercentAttribute(): float
    {
        if ($this->price_monthly <= 0) {
            return 0;
        }
        $monthlyAnnual = $this->price_monthly * 12;
        return round((($monthlyAnnual - $this->price_annual) / $monthlyAnnual) * 100, 1);
    }

    public function hasFeature(string $featureKey): bool
    {
        $feature = $this->features()->where('feature_key', $featureKey)->first();
        
        if (!$feature || !$feature->is_active) {
            return false;
        }

        return $feature->feature_type === 'boolean' ? $feature->value_boolean : true;
    }

    public function getFeatureLimit(string $featureKey): ?int
    {
        $feature = $this->features()
            ->where('feature_key', $featureKey)
            ->where('feature_type', 'limit')
            ->where('is_active', true)
            ->first();

        return $feature?->value_limit;
    }

    public function toConfigArray(): array
    {
        $features = [];
        foreach ($this->activeFeatures as $feature) {
            $features[$feature->feature_key] = match($feature->feature_type) {
                'boolean' => $feature->value_boolean,
                'limit' => $feature->value_limit,
                'text' => $feature->value_text,
            };
        }

        return [
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price_monthly,
            'price_monthly' => $this->price_monthly,
            'price_annual' => $this->price_annual,
            'billing_cycle' => 'monthly',
            'user_limit' => $this->user_limit,
            'storage_limit_mb' => $this->storage_limit_mb,
            'features' => $features,
        ];
    }
}
