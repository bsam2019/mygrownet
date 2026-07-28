<?php

namespace App\Infrastructure\Persistence\Eloquent\GrowFinance;

use Illuminate\Database\Eloquent\Model;

class GrowFinanceTaxReturnModel extends Model
{
    protected $table = 'growfinance_tax_returns';
    protected $guarded = ['id'];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'due_date' => 'date',
        'output_vat' => 'decimal:2',
        'input_vat' => 'decimal:2',
        'net_vat_payable' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'total_purchases' => 'decimal:2',
        'withholding_collected' => 'decimal:2',
        'withholding_paid' => 'decimal:2',
        'filed_at' => 'datetime',
    ];

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeByType($query, string $returnType)
    {
        return $query->where('return_type', $returnType);
    }
}
