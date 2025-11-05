<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Infrastructure\Persistence\Eloquent\PositionModel;

class HiringRoadmap extends Model
{
    protected $table = 'hiring_roadmap';

    protected $fillable = [
        'position_id',
        'phase',
        'target_hire_date',
        'priority',
        'headcount',
        'status',
        'budget_allocated',
        'notes',
    ];

    protected $casts = [
        'target_hire_date' => 'date',
        'headcount' => 'integer',
        'budget_allocated' => 'decimal:2',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(PositionModel::class, 'position_id');
    }

    public function scopePlanned($query)
    {
        return $query->where('status', 'planned');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeHired($query)
    {
        return $query->where('status', 'hired');
    }

    public function scopeByPhase($query, string $phase)
    {
        return $query->where('phase', $phase);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeOverdue($query)
    {
        return $query->where('target_hire_date', '<', now())
                     ->whereIn('status', ['planned', 'in_progress']);
    }

    /**
     * Check if hiring is overdue
     */
    public function isOverdue(): bool
    {
        return $this->target_hire_date < now() && 
               in_array($this->status, ['planned', 'in_progress']);
    }

    /**
     * Get phase label
     */
    public function getPhaseLabel(): string
    {
        return match($this->phase) {
            'phase_1' => 'Phase 1 (0-6 months)',
            'phase_2' => 'Phase 2 (6-18 months)',
            'phase_3' => 'Phase 3 (18+ months)',
            default => 'Unknown'
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'planned' => 'Planned',
            'in_progress' => 'In Progress',
            'hired' => 'Hired',
            'cancelled' => 'Cancelled',
            default => 'Unknown'
        };
    }
}
