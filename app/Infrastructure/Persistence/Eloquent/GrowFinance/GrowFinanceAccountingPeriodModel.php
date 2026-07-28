<?php

namespace App\Infrastructure\Persistence\Eloquent\GrowFinance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowFinanceAccountingPeriodModel extends Model
{
    protected $table = 'growfinance_accounting_periods';

    protected $fillable = [
        'business_id',
        'fiscal_year_id',
        'label',
        'start_date',
        'end_date',
        'status',
        'closed_by',
        'closed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'closed_at' => 'datetime',
    ];

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(GrowFinanceFiscalYearModel::class, 'fiscal_year_id');
    }

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeCurrent($query)
    {
        $now = now()->format('Y-m-d');
        return $query->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now);
    }
}
