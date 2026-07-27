<?php

namespace App\Domain\FinancialServicesCore\Infrastructure;

use Illuminate\Database\Eloquent\Model;

class ExchangeRateModel extends Model
{
    protected $table = 'exchange_rates';

    protected $fillable = [
        'from_currency',
        'to_currency',
        'rate',
        'date',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:8',
            'date' => 'date:Y-m-d',
        ];
    }
}
