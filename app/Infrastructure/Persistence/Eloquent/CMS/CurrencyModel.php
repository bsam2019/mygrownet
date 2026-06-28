<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;

class CurrencyModel extends Model
{
    protected $table = 'cms_currencies';

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'decimal_places',
        'format',
        'is_active',
    ];

    protected $casts = [
        'decimal_places' => 'integer',
        'is_active' => 'boolean',
    ];

    public function formatAmount(float $amount): string
    {
        $formattedAmount = number_format($amount, $this->decimal_places);
        
        return str_replace(
            ['{symbol}', '{amount}'],
            [$this->symbol, $formattedAmount],
            $this->format
        );
    }
}
