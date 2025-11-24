<?php

namespace App\Infrastructure\Persistence\Eloquent\Investor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorAccountModel extends Model
{
    protected $table = 'investor_accounts';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'investment_amount',
        'investment_date',
        'investment_round_id',
        'status',
        'equity_percentage',
    ];

    protected $casts = [
        'investment_amount' => 'decimal:2',
        'equity_percentage' => 'decimal:4',
        'investment_date' => 'date',
    ];

    public function investmentRound(): BelongsTo
    {
        return $this->belongsTo(InvestmentRoundModel::class, 'investment_round_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
