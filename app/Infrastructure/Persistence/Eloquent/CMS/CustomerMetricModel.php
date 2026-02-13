<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerMetricModel extends Model
{
    protected $table = 'cms_customer_metrics';

    protected $fillable = [
        'company_id', 'customer_id',
        'lifetime_value', 'average_order_value', 'total_orders', 'total_jobs',
        'first_purchase_date', 'last_purchase_date', 'days_since_last_purchase', 'purchase_frequency_days',
        'total_revenue', 'total_profit', 'profit_margin',
        'customer_tier', 'churn_risk', 'calculated_at',
    ];

    protected $casts = [
        'lifetime_value' => 'decimal:2',
        'average_order_value' => 'decimal:2',
        'first_purchase_date' => 'datetime',
        'last_purchase_date' => 'datetime',
        'total_revenue' => 'decimal:2',
        'total_profit' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'calculated_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
