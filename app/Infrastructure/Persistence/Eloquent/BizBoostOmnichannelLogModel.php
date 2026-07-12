<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class BizBoostOmnichannelLogModel extends Model
{
    protected $table = 'bizboost_omnichannel_logs';

    protected $fillable = [
        'user_id',
        'business_id',
        'channel_type',
        'recipient_phone',
        'message_content',
        'client_amount_charged',
        'vendor_actual_cost',
        'net_platform_profit',
        'delivery_status',
        'error_message',
        'reference',
        'meta',
    ];

    protected $casts = [
        'client_amount_charged' => 'decimal:4',
        'vendor_actual_cost' => 'decimal:4',
        'net_platform_profit' => 'decimal:4',
        'meta' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
