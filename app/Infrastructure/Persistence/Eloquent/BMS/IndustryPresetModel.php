<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;

class IndustryPresetModel extends Model
{
    protected $table = 'cms_industry_presets';

    protected $fillable = [
        'code',
        'name',
        'description',
        'icon',
        'roles',
        'expense_categories',
        'job_types',
        'inventory_categories',
        'asset_types',
        'default_settings',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'roles' => 'array',
        'expense_categories' => 'array',
        'job_types' => 'array',
        'inventory_categories' => 'array',
        'asset_types' => 'array',
        'default_settings' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope to get only active presets
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
