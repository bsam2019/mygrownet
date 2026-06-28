<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Database\Factories\EmployeePerformanceModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePerformanceModel extends Model
{
    use HasFactory, SoftDeletes;

    protected static function newFactory()
    {
        return EmployeePerformanceModelFactory::new();
    }

    protected $table = 'employee_performance';

    protected $fillable = [
        'employee_id',
        'reviewer_id',
        'evaluation_period',
        'period_start',
        'period_end',
        'metrics',
        'overall_score',
        'rating',
        'strengths',
        'areas_for_improvement',
        'goals_next_period',
        'reviewer_comments',
        'employee_comments',
        'status',
        'submitted_at',
        'approved_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'metrics' => 'array',
        'overall_score' => 'float',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'employee_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'reviewer_id');
    }

    // Scopes
    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->where('period_start', '>=', $startDate)
                     ->where('period_end', '<=', $endDate);
    }

    public function scopeByReviewer($query, int $reviewerId)
    {
        return $query->where('reviewer_id', $reviewerId);
    }

    public function scopeWithMinScore($query, float $minScore)
    {
        return $query->where('overall_score', '>=', $minScore);
    }

    public function scopeRecent($query, int $months = 12)
    {
        return $query->where('period_end', '>=', now()->subMonths($months));
    }

    public function scopeOrderByScore($query, string $direction = 'desc')
    {
        return $query->orderBy('overall_score', $direction);
    }

    public function scopeOrderByPeriod($query, string $direction = 'desc')
    {
        return $query->orderBy('period_end', $direction);
    }

    // Accessors
    public function getEvaluationPeriodAttribute(): string
    {
        return $this->period_start->format('M Y') . ' - ' . $this->period_end->format('M Y');
    }

    public function getPerformanceGradeAttribute(): string
    {
        return ucfirst($this->rating);
    }

    public function getInvestmentsFacilitatedCountAttribute(): int
    {
        return $this->metrics['investments_facilitated_count'] ?? 0;
    }

    public function getCommissionGeneratedAttribute(): float
    {
        return $this->metrics['commission_generated'] ?? 0.0;
    }

    public function getCommissionPerInvestmentAttribute(): float
    {
        $count = $this->getInvestmentsFacilitatedCountAttribute();
        if ($count === 0) {
            return 0;
        }
        
        return $this->getCommissionGeneratedAttribute() / $count;
    }
}