<?php

namespace App\Domain\PlatformBilling\Infrastructure;

use Illuminate\Database\Eloquent\Model;

class PlanModel extends Model
{
    protected $table = 'subscription_plans';

    protected $fillable = [
        'name', 'slug', 'monthly_price', 'annual_price',
        'site_limit', 'storage_limit_mb', 'team_member_limit',
        'client_limit', 'features_json', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'annual_price' => 'decimal:2',
        'features_json' => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
