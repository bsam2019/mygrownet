<?php

namespace App\Domain\PlatformBilling\Infrastructure;

use Illuminate\Database\Eloquent\Model;

class SubscriptionModel extends Model
{
    protected $table = 'billing_subscriptions';

    protected $fillable = [
        'user_id', 'plan_id', 'amount', 'status',
        'start_date', 'end_date', 'renewal_date',
        'cancelled_at', 'cancellation_reason',
        'auto_renew', 'is_trial', 'trial_days', 'failure_count',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'renewal_date' => 'datetime',
        'cancelled_at' => 'datetime',
        'auto_renew' => 'boolean',
        'is_trial' => 'boolean',
        'failure_count' => 'integer',
    ];
}
