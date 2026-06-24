<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class BizBoostBillingLedgerModel extends Model
{
    protected $table = 'bizboost_billing_ledger';

    protected $fillable = [
        'user_id',
        'service_type',
        'campaign_id',
        'recipient_identifier',
        'gross_amount_charged',
        'net_vendor_cost',
        'pure_platform_profit',
        'currency',
        'vendor',
        'delivery_status',
        'reference',
        'meta',
    ];

    protected $casts = [
        'gross_amount_charged' => 'decimal:4',
        'net_vendor_cost' => 'decimal:4',
        'pure_platform_profit' => 'decimal:4',
        'meta' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
