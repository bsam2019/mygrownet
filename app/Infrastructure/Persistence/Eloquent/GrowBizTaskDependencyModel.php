<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowBizTaskDependencyModel extends Model
{
    protected $table = 'growbiz_task_dependencies';

    protected $fillable = [
        'task_id',
        'depends_on_task_id',
        'type',
        'lag_days',
    ];

    protected $casts = [
        'task_id' => 'integer',
        'depends_on_task_id' => 'integer',
        'lag_days' => 'integer',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(GrowBizTaskModel::class, 'task_id');
    }

    public function dependsOnTask(): BelongsTo
    {
        return $this->belongsTo(GrowBizTaskModel::class, 'depends_on_task_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'finish_to_start' => 'Finish to Start',
            'start_to_start' => 'Start to Start',
            'finish_to_finish' => 'Finish to Finish',
            'start_to_finish' => 'Start to Finish',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }
}
