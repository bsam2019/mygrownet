<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaTaxRateModel extends Model
{
    protected $table = 'sa_tax_rates';
    protected $fillable = ['sa_company_id', 'name', 'rate', 'type', 'is_default'];
    protected $casts = ['rate' => 'decimal:2', 'is_default' => 'boolean'];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
}
