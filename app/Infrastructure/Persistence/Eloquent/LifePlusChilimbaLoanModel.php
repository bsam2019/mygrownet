<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LifePlusChilimbaLoanModel extends Model
{
    protected $table = 'lifeplus_chilimba_loans';

    protected $fillable = [
        'group_id',
        'member_id',
        'loan_amount',
        'interest_rate',
        'loan_date',
        'due_date',
        'purpose',
        'status',
        'total_repaid',
    ];

    protected $casts = [
        'loan_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'total_repaid' => 'decimal:2',
        'loan_date' => 'date',
        'due_date' => 'date',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(LifePlusChilimbaGroupModel::class, 'group_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(LifePlusChilimbaMemberModel::class, 'member_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LifePlusChilimbaLoanPaymentModel::class, 'loan_id');
    }

    public function getTotalToRepayAttribute(): float
    {
        return $this->loan_amount * (1 + $this->interest_rate / 100);
    }

    public function getRemainingBalanceAttribute(): float
    {
        return max(0, $this->total_to_repay - $this->total_repaid);
    }
}
