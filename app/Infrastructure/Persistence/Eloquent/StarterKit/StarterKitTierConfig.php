<?php

namespace App\Infrastructure\Persistence\Eloquent\StarterKit;

use App\Infrastructure\Persistence\Eloquent\Benefit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StarterKitTierConfig extends Model
{
    protected $table = 'starter_kit_tier_configs';

    protected $fillable = [
        'tier_key',
        'tier_name',
        'description',
        'price',
        'storage_gb',
        'earning_potential_percentage',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'earning_potential_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function benefits(): BelongsToMany
    {
        return $this->belongsToMany(
            Benefit::class,
            'starter_kit_tier_benefits',
            'tier_config_id',
            'benefit_id'
        )->withPivot('is_included', 'limit_value')
         ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }
}
