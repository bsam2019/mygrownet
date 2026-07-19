<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PipMilestoneModel extends Model
{
    protected $table = 'cms_pip_milestones';

    protected $fillable = [
        'improvement_plan_id',
        'milestone_title',
        'description',
        'target_date',
        'is_completed',
        'completed_date',
        'completion_notes',
    ];

    protected $casts = [
        'target_date' => 'date',
        'completed_date' => 'date',
        'is_completed' => 'boolean',
    ];

    public function improvementPlan(): BelongsTo
    {
        return $this->belongsTo(ImprovementPlanModel::class, 'improvement_plan_id');
    }

    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }
}
