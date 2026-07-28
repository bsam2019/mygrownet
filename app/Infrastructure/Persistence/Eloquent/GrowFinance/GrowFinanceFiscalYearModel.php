<?php

namespace App\Infrastructure\Persistence\Eloquent\GrowFinance;

use Illuminate\Database\Eloquent\Model;

class GrowFinanceFiscalYearModel extends Model
{
    protected $table = 'growfinance_fiscal_years';

    protected $fillable = [
        'business_id',
        'label',
        'start_date',
        'end_date',
        'is_closed',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_closed' => 'boolean',
    ];

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeCurrent($query)
    {
        $now = now()->format('Y-m-d');
        return $query->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now);
    }
}
