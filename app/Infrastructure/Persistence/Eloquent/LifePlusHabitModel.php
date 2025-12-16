<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LifePlusHabitModel extends Model
{
    protected $table = 'lifeplus_habits';

    protected $fillable = [
        'user_id',
        'name',
        'icon',
        'color',
        'frequency',
        'reminder_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(LifePlusHabitLogModel::class, 'habit_id');
    }
}
