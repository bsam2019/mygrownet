<?php

namespace App\Domain\FinancialServicesCore\Infrastructure;

use Illuminate\Database\Eloquent\Model;

class CurrencyModel extends Model
{
    protected $table = 'currencies';

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'decimal_places',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'decimal_places' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
