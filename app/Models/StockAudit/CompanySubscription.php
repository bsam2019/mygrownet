<?php

namespace App\Models\StockAudit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanySubscription extends Model
{
    protected $table = 'sa_company_subscriptions';

    protected $fillable = [
        'sa_company_id', 'sa_subscription_plan_id', 'status',
        'trial_ends_at', 'starts_at', 'ends_at', 'next_billing_at',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'next_billing_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'sa_company_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'sa_subscription_plan_id');
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['trial', 'active']);
    }
}
