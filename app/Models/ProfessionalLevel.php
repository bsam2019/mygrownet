<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfessionalLevel extends Model
{
    protected $fillable = [
        'level',
        'name',
        'slug',
        'network_size',
        'role',
        'bp_required',
        'lp_required',
        'min_time',
        'additional_requirements',
        'milestone_bonus',
        'profit_share_multiplier',
        'commission_rate',
        'color',
        'benefits',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'level' => 'integer',
        'bp_required' => 'integer',
        'lp_required' => 'integer',
        'benefits' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Scope to get only active levels
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by level
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('level');
    }
}
