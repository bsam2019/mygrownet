<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Investment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'type',
        'category_id',
        'maturity_date',
        'roi',
        'platform_fee',
        'tier',
        'expected_return',
        'total_earned',
        'interest_earned',
        'lock_in_period_end',
        'last_payout_date',
        'investment_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'roi' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'expected_return' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'interest_earned' => 'decimal:2',
        'maturity_date' => 'date',
        'investment_date' => 'datetime',
        'lock_in_period_end' => 'datetime',
        'last_payout_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InvestmentCategory::class, 'category_id');
    }
}
