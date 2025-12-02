<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowBizTaskUpdateModel extends Model
{
    protected $table = 'growbiz_task_updates';

    protected $fillable = [
        'task_id',
        'employee_id',
        'user_id',
        'update_type',
        'old_status',
        'new_status',
        'old_progress',
        'new_progress',
        'hours_logged',
        'notes',
    ];

    protected $casts = [
        'task_id' => 'integer',
        'employee_id' => 'integer',
        'user_id' => 'integer',
        'old_progress' => 'integer',
        'new_progress' => 'integer',
        'hours_logged' => 'decimal:2',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(GrowBizTaskModel::class, 'task_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(GrowBizEmployeeModel::class, 'employee_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function scopeStatusChanges($query)
    {
        return $query->where('update_type', 'status_change');
    }

    public function scopeProgressUpdates($query)
    {
        return $query->where('update_type', 'progress_update');
    }

    public function scopeTimeLogs($query)
    {
        return $query->where('update_type', 'time_log');
    }

    public function scopeNotes($query)
    {
        return $query->where('update_type', 'note');
    }

    public function getFormattedTypeAttribute(): string
    {
        return match($this->update_type) {
            'status_change' => 'Status Changed',
            'progress_update' => 'Progress Updated',
            'time_log' => 'Time Logged',
            'note' => 'Note Added',
            default => 'Update',
        };
    }
}
