<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskTriggerModel extends Model
{
    protected $table = 'cms_task_triggers';

    protected $fillable = [
        'company_id',
        'trigger_type',
        'action_type',
        'trigger_conditions',
        'action_config',
        'is_active',
    ];

    protected $casts = [
        'trigger_conditions' => 'array',
        'action_config' => 'array',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function executions(): HasMany
    {
        return $this->hasMany(TaskTriggerExecutionModel::class, 'trigger_id');
    }
}
