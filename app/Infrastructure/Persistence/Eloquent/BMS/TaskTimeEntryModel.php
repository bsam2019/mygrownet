<?php

namespace App\Infrastructure\Persistence\Eloquent\BMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskTimeEntryModel extends Model
{
    protected $table = 'cms_task_time_entries';

    protected $fillable = [
        'task_id',
        'user_id',
        'started_at',
        'ended_at',
        'hours',
        'description',
        'is_billable',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'hours' => 'decimal:2',
        'is_billable' => 'boolean',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(TaskModel::class, 'task_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
