<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Benefit extends Model
{
    protected $table = 'benefits';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'benefit_type',
        'description',
        'icon',
        'unit',
        'is_active',
        'is_coming_soon',
        'sort_order',
        'tier_allocations',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_coming_soon' => 'boolean',
        'tier_allocations' => 'array',
    ];

    public function starterKits(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\StarterKitPurchase::class,
            'starter_kit_benefits',
            'benefit_id',
            'starter_kit_id'
        )->withPivot('included', 'limit_value');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeComingSoon($query)
    {
        return $query->where('is_coming_soon', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeStarterKitBenefits($query)
    {
        return $query->where('benefit_type', 'starter_kit');
    }

    public function scopeMonthlyServices($query)
    {
        return $query->where('benefit_type', 'monthly_service');
    }

    public function scopePhysicalItems($query)
    {
        return $query->where('benefit_type', 'physical_item');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
