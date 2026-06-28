<?php

namespace App\Infrastructure\Persistence\Eloquent\Investor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialReportModel extends Model
{
    protected $table = 'investor_financial_reports';

    protected $fillable = [
        'title',
        'report_type',
        'report_period',
        'report_date',
        'total_revenue',
        'total_expenses',
        'net_profit',
        'gross_margin',
        'operating_margin',
        'net_margin',
        'cash_flow',
        'total_members',
        'active_members',
        'monthly_recurring_revenue',
        'customer_acquisition_cost',
        'lifetime_value',
        'churn_rate',
        'growth_rate',
        'notes',
        'published_at',
    ];

    protected $casts = [
        'report_date' => 'date',
        'total_revenue' => 'decimal:2',
        'total_expenses' => 'decimal:2',
        'net_profit' => 'decimal:2',
        'gross_margin' => 'decimal:2',
        'operating_margin' => 'decimal:2',
        'net_margin' => 'decimal:2',
        'cash_flow' => 'decimal:2',
        'monthly_recurring_revenue' => 'decimal:2',
        'customer_acquisition_cost' => 'decimal:2',
        'lifetime_value' => 'decimal:2',
        'churn_rate' => 'decimal:2',
        'growth_rate' => 'decimal:2',
        'published_at' => 'datetime',
    ];

    public function revenueBreakdown(): HasMany
    {
        return $this->hasMany(RevenueBreakdownModel::class, 'financial_report_id');
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('report_type', $type);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('report_date', 'desc');
    }
}