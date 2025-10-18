<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectInvestment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'community_project_id',
        'amount',
        'status',
        'invested_at',
        'expected_return',
        'actual_return',
        'auto_reinvest',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'invested_at' => 'date',
        'expected_return' => 'decimal:2',
        'actual_return' => 'decimal:2',
        'auto_reinvest' => 'boolean'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(CommunityProject::class, 'community_project_id');
    }

    // Business Logic Methods
    public function calculateROI(): float
    {
        if ($this->amount <= 0) {
            return 0;
        }

        return (($this->actual_return - $this->amount) / $this->amount) * 100;
    }

    public function isActive(): bool
    {
        return $this->status === 'confirmed' && $this->project->status === 'active';
    }

    public function getInvestmentSummary(): array
    {
        return [
            'investment_id' => $this->id,
            'project_name' => $this->project->name,
            'amount' => $this->amount,
            'status' => $this->status,
            'invested_at' => $this->invested_at,
            'expected_return' => $this->expected_return,
            'actual_return' => $this->actual_return,
            'roi_percentage' => $this->calculateROI(),
            'auto_reinvest' => $this->auto_reinvest,
            'project_status' => $this->project->status
        ];
    }
}