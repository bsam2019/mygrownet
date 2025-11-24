<?php

namespace App\Infrastructure\Persistence\Eloquent\Investor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Investment Round Eloquent Model
 */
class InvestmentRoundModel extends Model
{
    use HasFactory;

    protected $table = 'investment_rounds';

    protected $fillable = [
        'name',
        'description',
        'goal_amount',
        'raised_amount',
        'minimum_investment',
        'valuation',
        'equity_percentage',
        'expected_roi',
        'use_of_funds',
        'status',
        'start_date',
        'end_date',
        'is_featured',
    ];

    protected $casts = [
        'goal_amount' => 'float',
        'raised_amount' => 'float',
        'minimum_investment' => 'float',
        'valuation' => 'float',
        'equity_percentage' => 'float',
        'use_of_funds' => 'array',
        'is_featured' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
