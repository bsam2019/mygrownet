<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowBottleneckModel extends Model
{
    protected $table = 'cms_workflow_bottlenecks';

    protected $fillable = [
        'company_id',
        'workflow_id',
        'workflow_stage_id',
        'detected_at',
        'task_count',
        'avg_duration_days',
        'severity',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
        'task_count' => 'integer',
        'avg_duration_days' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(WorkflowModel::class, 'workflow_id');
    }

    public function workflowStage(): BelongsTo
    {
        return $this->belongsTo(WorkflowStageModel::class, 'workflow_stage_id');
    }
}
