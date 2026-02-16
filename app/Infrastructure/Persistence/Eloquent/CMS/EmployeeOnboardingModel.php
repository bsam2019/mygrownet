<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeOnboardingModel extends Model
{
    protected $table = 'cms_employee_onboarding';

    protected $fillable = [
        'worker_id',
        'template_id',
        'start_date',
        'expected_completion_date',
        'actual_completion_date',
        'status',
        'completion_percentage',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expected_completion_date' => 'date',
        'actual_completion_date' => 'date',
        'completion_percentage' => 'decimal:2',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(WorkerModel::class, 'worker_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(OnboardingTemplateModel::class, 'template_id');
    }

    public function taskProgress(): HasMany
    {
        return $this->hasMany(OnboardingTaskProgressModel::class, 'onboarding_id');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
