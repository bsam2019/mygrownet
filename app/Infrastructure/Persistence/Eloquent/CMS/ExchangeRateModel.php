<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeRateModel extends Model
{
    protected $table = 'cms_exchange_rates';

    protected $fillable = [
        'company_id',
        'from_currency',
        'to_currency',
        'rate',
        'effective_date',
        'source',
    ];

    protected $casts = [
        'rate' => 'decimal:10',
        'effective_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function convert(float $amount): float
    {
        return $amount * (float)$this->rate;
    }
}
