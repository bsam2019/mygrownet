<?php

namespace App\Models\GrowStart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Industry extends Model
{
    protected $table = 'growstart_industries';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'is_active',
        'estimated_startup_cost_min',
        'estimated_startup_cost_max',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'estimated_startup_cost_min' => 'decimal:2',
        'estimated_startup_cost_max' => 'decimal:2',
    ];

    public function journeys(): HasMany
    {
        return $this->hasMany(UserJourney::class, 'industry_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'industry_id');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(Template::class, 'industry_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getEstimatedCostRange(): string
    {
        if (!$this->estimated_startup_cost_min && !$this->estimated_startup_cost_max) {
            return 'Varies';
        }

        $min = number_format($this->estimated_startup_cost_min ?? 0);
        $max = number_format($this->estimated_startup_cost_max ?? 0);

        return "K{$min} - K{$max}";
    }
}
