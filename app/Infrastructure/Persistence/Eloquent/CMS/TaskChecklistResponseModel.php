<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskChecklistResponseModel extends Model
{
    protected $table = 'cms_task_checklist_responses';

    protected $fillable = [
        'task_id',
        'checklist_item_id',
        'completed',
        'completed_by',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(TaskModel::class, 'task_id');
    }

    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplateItemModel::class, 'checklist_item_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'completed_by');
    }
}
