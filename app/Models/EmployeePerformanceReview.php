<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePerformanceReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'reviewer_id',
        'review_period',
        'review_type',
        'status',
        'overall_rating',
        'ratings',
        'strengths',
        'improvements',
        'goals_for_next_period',
        'employee_comments',
        'manager_comments',
        'due_date',
        'submitted_at',
        'completed_at',
    ];

    protected $casts = [
        'ratings' => 'array',
        'overall_rating' => 'decimal:2',
        'due_date' => 'date',
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reviewer_id');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'submitted', 'in_review']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereIn('status', ['draft', 'submitted', 'in_review']);
    }
}
