<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoalModel extends Model
{
    protected $table = 'cms_goals';

    protected $fillable = [
        'company_id',
        'worker_id',
        'set_by_user_id',
        'goal_title',
        'description',
        'goal_type',
        'category',
        'start_date',
        'target_date',
        'priority',
        'status',
        'progress_percentage',
        'success_criteria',
        'notes',
        'completed_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'target_date' => 'date',
        'completed_date' => 'date',
        'progress_percentage' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(WorkerModel::class, 'worker_id');
    }

    public function setBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'set_by_user_id');
    }

    public function progressUpdates(): HasMany
    {
        return $this->hasMany(GoalProgressModel::class, 'goal_id')->orderBy('update_date', 'desc');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['not_started', 'in_progress']);
    }

    public function scopeOverdue($query)
    {
        return $query->whereIn('status', ['not_started', 'in_progress'])
            ->where('target_date', '<', now());
    }
}
