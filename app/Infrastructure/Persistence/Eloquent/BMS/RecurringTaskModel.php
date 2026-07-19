<?php

namespace App\Infrastructure\Persistence\Eloquent\BMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringTaskModel extends Model
{
    protected $table = 'cms_recurring_tasks';

    protected $fillable = [
        'company_id',
        'template_id',
        'title',
        'description',
        'type',
        'priority',
        'workflow_id',
        'assigned_to',
        'recurrence_pattern',
        'recurrence_interval',
        'recurrence_days',
        'recurrence_day_of_month',
        'start_date',
        'end_date',
        'last_generated_at',
        'next_generation_at',
        'is_active',
    ];

    protected $casts = [
        'recurrence_days' => 'array',
        'recurrence_interval' => 'integer',
        'recurrence_day_of_month' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'last_generated_at' => 'datetime',
        'next_generation_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(TaskTemplateModel::class, 'template_id');
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(WorkflowModel::class, 'workflow_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    public function generatedTasks(): HasMany
    {
        return $this->hasMany(TaskModel::class, 'recurring_task_id');
    }
}
