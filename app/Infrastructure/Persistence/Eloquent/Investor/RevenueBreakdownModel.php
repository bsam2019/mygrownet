<?php

namespace App\Infrastructure\Persistence\Eloquent\Investor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevenueBreakdownModel extends Model
{
    protected $table = 'revenue_breakdown';

    protected $fillable = [
        'financial_report_id',
        'revenue_source',
        'amount',
        'percentage',
        'growth_rate',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'growth_rate' => 'decimal:2',
    ];

    public function financialReport(): BelongsTo
    {
        return $this->belongsTo(FinancialReportModel::class, 'financial_report_id');
    }

    public function scopeBySource($query, string $source)
    {
        return $query->where('revenue_source', $source);
    }
}