<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingTaskProgressModel extends Model
{
    protected $table = 'cms_onboarding_task_progress';

    protected $fillable = [
        'onboarding_id',
        'task_id',
        'assigned_to_user_id',
        'due_date',
        'completed_date',
        'is_completed',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_date' => 'date',
        'is_completed' => 'boolean',
    ];

    public function onboarding(): BelongsTo
    {
        return $this->belongsTo(EmployeeOnboardingModel::class, 'onboarding_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(OnboardingTaskModel::class, 'task_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'assigned_to_user_id');
    }

    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }

    public function scopeOverdue($query)
    {
        return $query->where('is_completed', false)
            ->where('due_date', '<', now());
    }
}
