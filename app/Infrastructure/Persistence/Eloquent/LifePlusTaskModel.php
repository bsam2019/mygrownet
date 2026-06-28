<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifePlusTaskModel extends Model
{
    protected $table = 'lifeplus_tasks';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'priority',
        'due_date',
        'due_time',
        'is_completed',
        'completed_at',
        'is_synced',
        'local_id',
    ];

    protected $casts = [
        'due_date' => 'date',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'is_synced' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
