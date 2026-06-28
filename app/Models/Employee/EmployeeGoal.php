<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'title',
        'description',
        'category',
        'progress',
        'status',
        'start_date',
        'due_date',
        'completed_at',
        'milestones',
        'approved_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'milestones' => 'array',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function updateProgress(int $progress): void
    {
        $this->update([
            'progress' => min(100, max(0, $progress)),
            'status' => $progress >= 100 ? 'completed' : 'in_progress',
            'completed_at' => $progress >= 100 ? now() : null,
        ]);
    }

    public function isOverdue(): bool
    {
        return $this->due_date->isPast() && $this->status !== 'completed';
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress']);
    }
}
