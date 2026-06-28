<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrowBizMilestoneModel extends Model
{
    protected $table = 'growbiz_milestones';

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'due_date',
        'status',
        'completed_at',
        'sort_order',
    ];

    protected $casts = [
        'project_id' => 'integer',
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(GrowBizProjectModel::class, 'project_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(GrowBizTaskModel::class, 'milestone_id');
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-gray-100 text-gray-800',
            'in_progress' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'completed';
    }

    public function getProgressAttribute(): int
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks === 0) return 0;
        
        $completedTasks = $this->tasks()->where('status', 'completed')->count();
        return (int) round(($completedTasks / $totalTasks) * 100);
    }
}
