<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowFinanceBudgetModel extends Model
{
    protected $table = 'growfinance_budgets';

    protected $fillable = [
        'business_id',
        'name',
        'category',
        'account_id',
        'period',
        'start_date',
        'end_date',
        'budgeted_amount',
        'spent_amount',
        'is_active',
        'rollover_unused',
        'alert_threshold',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budgeted_amount' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'rollover_unused' => 'boolean',
    ];

    protected $appends = ['remaining_amount', 'spent_percentage', 'status'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'business_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(GrowFinanceAccountModel::class, 'account_id');
    }

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent($query)
    {
        $today = now()->toDateString();
        return $query->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->budgeted_amount - $this->spent_amount);
    }

    public function getSpentPercentageAttribute(): float
    {
        if ($this->budgeted_amount <= 0) {
            return 0;
        }
        return min(100, round(($this->spent_amount / $this->budgeted_amount) * 100, 1));
    }

    public function getStatusAttribute(): string
    {
        $percentage = $this->spent_percentage;

        if ($percentage >= 100) {
            return 'exceeded';
        }
        if ($percentage >= $this->alert_threshold) {
            return 'warning';
        }
        return 'on_track';
    }

    public function isOverBudget(): bool
    {
        return $this->spent_amount > $this->budgeted_amount;
    }

    public function isNearLimit(): bool
    {
        return $this->spent_percentage >= $this->alert_threshold && !$this->isOverBudget();
    }

    /**
     * Get period display label
     */
    public function getPeriodLabelAttribute(): string
    {
        return match ($this->period) {
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'yearly' => 'Yearly',
            'custom' => 'Custom',
            default => ucfirst($this->period),
        };
    }
}
