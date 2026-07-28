<?php

namespace App\Infrastructure\Persistence\Eloquent\GrowFinance;

use Illuminate\Database\Eloquent\Model;

class GrowFinanceReportScheduleModel extends Model
{
    protected $table = 'growfinance_report_schedules';

    protected $fillable = [
        'business_id',
        'name',
        'report_type',
        'frequency',
        'recipients',
        'format',
        'is_active',
        'last_run_at',
        'next_run_at',
    ];

    protected $casts = [
        'recipients' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDue($query)
    {
        return $query->where('next_run_at', '<=', now())->where('is_active', true);
    }
}
