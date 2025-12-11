<?php

namespace App\Models\GrowStart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stage extends Model
{
    protected $table = 'growstart_stages';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'order',
        'icon',
        'color',
        'estimated_days',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'estimated_days' => 'integer',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'stage_id');
    }

    public function journeys(): HasMany
    {
        return $this->hasMany(UserJourney::class, 'current_stage_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function isFirst(): bool
    {
        return $this->order === 1;
    }

    public function isLast(): bool
    {
        return $this->order === 8;
    }

    public function getNextStage(): ?self
    {
        return static::where('order', $this->order + 1)
            ->where('is_active', true)
            ->first();
    }

    public function getPreviousStage(): ?self
    {
        return static::where('order', $this->order - 1)
            ->where('is_active', true)
            ->first();
    }
}
