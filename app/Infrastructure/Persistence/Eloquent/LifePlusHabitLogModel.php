<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifePlusHabitLogModel extends Model
{
    protected $table = 'lifeplus_habit_logs';

    protected $fillable = [
        'habit_id',
        'completed_date',
    ];

    protected $casts = [
        'completed_date' => 'date',
    ];

    public function habit(): BelongsTo
    {
        return $this->belongsTo(LifePlusHabitModel::class, 'habit_id');
    }
}
