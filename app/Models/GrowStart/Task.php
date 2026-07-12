<?php

namespace App\Models\GrowStart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $table = 'growstart_tasks';

    protected $fillable = [
        'stage_id',
        'industry_id',
        'country_id',
        'title',
        'description',
        'instructions',
        'external_link',
        'estimated_hours',
        'order',
        'is_required',
        'is_premium',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_premium' => 'boolean',
        'estimated_hours' => 'integer',
        'order' => 'integer',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'stage_id');
    }

    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class, 'industry_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function userTasks(): HasMany
    {
        return $this->hasMany(UserTask::class, 'task_id');
    }

    public function scopeForStage($query, int $stageId)
    {
        return $query->where('stage_id', $stageId);
    }

    public function scopeForIndustry($query, ?int $industryId)
    {
        return $query->where(function ($q) use ($industryId) {
            $q->whereNull('industry_id')
              ->orWhere('industry_id', $industryId);
        });
    }

    public function scopeForCountry($query, ?int $countryId)
    {
        return $query->where(function ($q) use ($countryId) {
            $q->whereNull('country_id')
              ->orWhere('country_id', $countryId);
        });
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeFree($query)
    {
        return $query->where('is_premium', false);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function isGeneric(): bool
    {
        return $this->industry_id === null && $this->country_id === null;
    }
}
