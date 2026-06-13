<?php

namespace App\Models\GrowMart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowMartOrder extends Model
{
    protected $table = 'growmart_orders';

    protected $fillable = [
        'order_number',
        'user_id',
        'coupon_id',
        'status',
        'payment_status',
        'delivery_method',
        'delivery_zone',
        'delivery_address',
        'payment_method',
        'payment_reference',
        'payment_phone',
        'payment_notes',
        'payment_submitted_at',
        'payment_details',
        'contact_phone',
        'special_instructions',
        'tracking_number',
        'tracking_url',
        'estimated_delivery_at',
        'tracking_updates',
        'subtotal',
        'delivery_fee',
        'discount',
        'total',
        'paid_at',
        'delivered_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'delivery_fee' => 'integer',
            'discount' => 'integer',
            'total' => 'integer',
            'paid_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'estimated_delivery_at' => 'datetime',
            'tracking_updates' => 'array',
            'payment_submitted_at' => 'datetime',
            'payment_details' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(GrowMartCoupon::class, 'coupon_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GrowMartOrderItem::class, 'order_id');
    }
}
