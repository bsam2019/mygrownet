<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaExchangeRateModel extends Model
{
    protected $table = 'sa_exchange_rates';
    protected $fillable = ['sa_company_id', 'from_currency', 'to_currency', 'rate', 'effective_date'];
    protected $casts = ['rate' => 'decimal:6', 'effective_date' => 'date'];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
}
