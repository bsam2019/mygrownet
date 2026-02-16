<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformanceReviewModel extends Model
{
    protected $table = 'cms_performance_reviews';

    protected $fillable = [
        'cycle_id',
        'worker_id',
        'reviewer_id',
        'review_type',
        'due_date',
        'submitted_date',
        'status',
        'overall_rating',
        'strengths',
        'areas_for_improvement',
        'achievements',
        'goals_met',
        'reviewer_comments',
        'employee_comments',
    ];

    protected $casts = [
        'due_date' => 'date',
        'submitted_date' => 'date',
        'overall_rating' => 'decimal:2',
    ];

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(PerformanceCycleModel::class, 'cycle_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(WorkerModel::class, 'worker_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'reviewer_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(PerformanceRatingModel::class, 'review_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress'])
            ->where('due_date', '<', now());
    }
}
