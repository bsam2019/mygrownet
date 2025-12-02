<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GrowBizTaskModel extends Model
{
    use SoftDeletes;

    protected $table = 'growbiz_tasks';

    protected $fillable = [
        'manager_id',
        'title',
        'description',
        'due_date',
        'priority',
        'status',
        'category',
        'estimated_hours',
        'actual_hours',
        'progress_percentage',
        'started_at',
        'completed_at',
        'tags',
    ];

    protected $casts = [
        'manager_id' => 'integer',
        'due_date' => 'date',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'progress_percentage' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'tags' => 'array',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'manager_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(GrowBizTaskAssignmentModel::class, 'task_id');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(
            GrowBizEmployeeModel::class,
            'growbiz_task_assignments',
            'task_id',
            'employee_id'
        )->withPivot('assigned_at', 'completed_at')->withTimestamps();
    }

    public function updates(): HasMany
    {
        return $this->hasMany(GrowBizTaskUpdateModel::class, 'task_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(GrowBizTaskCommentModel::class, 'task_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(GrowBizTaskAttachmentModel::class, 'task_id');
    }

    public function scopeForManager($query, int $managerId)
    {
        return $query->where('manager_id', $managerId);
    }

    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now()->startOfDay())
                     ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', now()->toDateString());
    }
}
