<?php

namespace App\Infrastructure\Persistence\Eloquent\GrowFinance;

use Illuminate\Database\Eloquent\Model;

class GrowFinanceTaxRateModel extends Model
{
    protected $table = 'growfinance_tax_rates';
    protected $guarded = ['id'];

    protected $casts = [
        'rate' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeByType($query, string $taxType)
    {
        return $query->where('tax_type', $taxType);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
