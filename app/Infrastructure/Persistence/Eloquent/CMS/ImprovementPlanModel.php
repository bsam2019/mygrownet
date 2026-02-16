<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImprovementPlanModel extends Model
{
    protected $table = 'cms_improvement_plans';

    protected $fillable = [
        'company_id',
        'worker_id',
        'created_by_user_id',
        'plan_title',
        'performance_issues',
        'improvement_actions',
        'support_provided',
        'start_date',
        'review_date',
        'end_date',
        'status',
        'outcome_notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'review_date' => 'date',
        'end_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(WorkerModel::class, 'worker_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'created_by_user_id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(PipMilestoneModel::class, 'improvement_plan_id')->orderBy('target_date');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
