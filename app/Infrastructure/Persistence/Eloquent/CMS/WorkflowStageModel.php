<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowStageModel extends Model
{
    protected $table = 'cms_workflow_stages';

    protected $fillable = [
        'workflow_id',
        'name',
        'description',
        'sequence_order',
        'requires_approval',
        'color',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'sequence_order' => 'integer',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(WorkflowModel::class, 'workflow_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(TaskModel::class, 'workflow_stage_id');
    }

    public function getNextStage(): ?self
    {
        return self::where('workflow_id', $this->workflow_id)
            ->where('sequence_order', '>', $this->sequence_order)
            ->orderBy('sequence_order')
            ->first();
    }

    public function getPreviousStage(): ?self
    {
        return self::where('workflow_id', $this->workflow_id)
            ->where('sequence_order', '<', $this->sequence_order)
            ->orderBy('sequence_order', 'desc')
            ->first();
    }
}
