<?php

namespace App\Infrastructure\Persistence\Eloquent\GrowFinance;

use Illuminate\Database\Eloquent\Model;

class GrowFinanceReportSnapshotModel extends Model
{
    protected $table = 'growfinance_report_snapshots';

    protected $fillable = [
        'business_id',
        'report_type',
        'as_of_date',
        'report_data',
        'integrity_hash',
        'locked_at',
    ];

    protected $casts = [
        'as_of_date' => 'date',
        'report_data' => 'array',
        'locked_at' => 'datetime',
    ];

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('report_type', $type);
    }
}
