<?php

namespace App\Infrastructure\Persistence\Eloquent\BMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskTriggerExecutionModel extends Model
{
    protected $table = 'cms_task_trigger_executions';

    protected $fillable = [
        'trigger_id',
        'task_id',
        'trigger_data',
        'action_result',
        'status',
        'error_message',
        'executed_at',
    ];

    protected $casts = [
        'trigger_data' => 'array',
        'action_result' => 'array',
        'executed_at' => 'datetime',
    ];

    public function trigger(): BelongsTo
    {
        return $this->belongsTo(TaskTriggerModel::class, 'trigger_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(TaskModel::class, 'task_id');
    }
}
