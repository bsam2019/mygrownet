<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskTemplateModel extends Model
{
    protected $table = 'cms_task_templates';

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'type',
        'priority',
        'workflow_id',
        'estimated_hours',
        'checklist_items',
        'default_assignees',
        'is_active',
    ];

    protected $casts = [
        'estimated_hours' => 'decimal:2',
        'checklist_items' => 'array',
        'default_assignees' => 'array',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(WorkflowModel::class, 'workflow_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(TaskModel::class, 'template_id');
    }
}
