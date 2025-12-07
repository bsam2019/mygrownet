<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowFinanceRecurringTransactionModel extends Model
{
    protected $table = 'growfinance_recurring_transactions';

    protected $fillable = [
        'business_id',
        'type',
        'account_id',
        'vendor_id',
        'customer_id',
        'description',
        'category',
        'amount',
        'payment_method',
        'frequency',
        'start_date',
        'end_date',
        'next_due_date',
        'last_processed_date',
        'is_active',
        'occurrences_count',
        'max_occurrences',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'next_due_date' => 'date',
        'last_processed_date' => 'date',
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'business_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(GrowFinanceAccountModel::class, 'account_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(GrowFinanceVendorModel::class, 'vendor_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(GrowFinanceCustomerModel::class, 'customer_id');
    }

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDueToday($query)
    {
        return $query->where('next_due_date', '<=', now()->toDateString())
            ->where('is_active', true);
    }

    public function scopeExpenses($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    /**
     * Calculate the next due date based on frequency
     */
    public function calculateNextDueDate(): \Carbon\Carbon
    {
        $current = $this->next_due_date ?? now();

        return match ($this->frequency) {
            'daily' => $current->addDay(),
            'weekly' => $current->addWeek(),
            'biweekly' => $current->addWeeks(2),
            'monthly' => $current->addMonth(),
            'quarterly' => $current->addMonths(3),
            'yearly' => $current->addYear(),
            default => $current->addMonth(),
        };
    }

    /**
     * Check if this recurring transaction should still run
     */
    public function shouldProcess(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->end_date && $this->next_due_date->gt($this->end_date)) {
            return false;
        }

        if ($this->max_occurrences && $this->occurrences_count >= $this->max_occurrences) {
            return false;
        }

        return $this->next_due_date->lte(now());
    }

    /**
     * Get frequency display label
     */
    public function getFrequencyLabelAttribute(): string
    {
        return match ($this->frequency) {
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'biweekly' => 'Every 2 Weeks',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'yearly' => 'Yearly',
            default => ucfirst($this->frequency),
        };
    }
}
