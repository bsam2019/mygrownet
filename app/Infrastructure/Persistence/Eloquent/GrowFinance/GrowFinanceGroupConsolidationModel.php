<?php

namespace App\Infrastructure\Persistence\Eloquent\GrowFinance;

use Illuminate\Database\Eloquent\Model;

class GrowFinanceGroupConsolidationModel extends Model
{
    protected $table = 'growfinance_group_consolidations';

    protected $fillable = [
        'group_id',
        'business_id',
        'period',
        'consolidated_data',
        'functional_currency',
        'reporting_currency',
        'exchange_rate',
        'elimination_entries',
        'status',
        'consolidated_at',
    ];

    protected $casts = [
        'consolidated_data' => 'array',
        'elimination_entries' => 'array',
        'exchange_rate' => 'float',
    ];
}
