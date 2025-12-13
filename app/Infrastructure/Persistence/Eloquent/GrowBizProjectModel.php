<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GrowBizProjectModel extends Model
{
    use SoftDeletes;

    protected $table = 'growbiz_projects';

    protected $fillable = [
        'manager_id',
        'name',
        'description',
        'color',
        'status',
        'start_date',
        'end_date',
        'budget',
        'currency',
        'progress_percentage',
        'settings',
        'sort_order',
    ];

    protected $casts = [
        'manager_id' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'progress_percentage' => 'integer',
        'settings' => 'array',
        'sort_order' => 'integer',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'manager_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(GrowBizTaskModel::class, 'project_id');
    }

    public function columns(): HasMany
    {
        return $this->hasMany(GrowBizKanbanColumnModel::class, 'project_id')->orderBy('sort_order');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(GrowBizMilestoneModel::class, 'project_id')->orderBy('sort_order');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            GrowBizEmployeeModel::class,
            'growbiz_project_members',
            'project_id',
            'employee_id'
        )->withPivot('role')->withTimestamps();
    }

    public function scopeForManager($query, int $managerId)
    {
        return $query->where('manager_id', $managerId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'planning' => 'bg-gray-100 text-gray-800',
            'active' => 'bg-blue-100 text-blue-800',
            'on_hold' => 'bg-yellow-100 text-yellow-800',
            'completed' => 'bg-green-100 text-green-800',
            'archived' => 'bg-gray-100 text-gray-500',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'planning' => 'Planning',
            'active' => 'Active',
            'on_hold' => 'On Hold',
            'completed' => 'Completed',
            'archived' => 'Archived',
            default => ucfirst($this->status),
        };
    }

    public function getTaskCountAttribute(): int
    {
        return $this->tasks()->count();
    }

    public function getCompletedTaskCountAttribute(): int
    {
        return $this->tasks()->where('status', 'completed')->count();
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->end_date) return null;
        return max(0, now()->diffInDays($this->end_date, false));
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->end_date && $this->end_date->isPast() && $this->status !== 'completed';
    }

    public function calculateProgress(): int
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks === 0) return 0;
        
        $completedTasks = $this->tasks()->where('status', 'completed')->count();
        return (int) round(($completedTasks / $totalTasks) * 100);
    }

    public function updateProgress(): void
    {
        $this->update(['progress_percentage' => $this->calculateProgress()]);
    }
}
