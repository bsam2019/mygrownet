<?php

namespace App\Infrastructure\Persistence\Eloquent\BMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskDependencyModel extends Model
{
    protected $table = 'cms_task_dependencies';

    protected $fillable = [
        'task_id',
        'depends_on_task_id',
        'dependency_type',
        'lag_days',
    ];

    protected $casts = [
        'lag_days' => 'integer',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(TaskModel::class, 'task_id');
    }

    public function dependsOnTask(): BelongsTo
    {
        return $this->belongsTo(TaskModel::class, 'depends_on_task_id');
    }
}
