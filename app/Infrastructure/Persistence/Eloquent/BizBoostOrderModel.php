<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BizBoostOrderModel extends Model
{
    use HasFactory;

    protected $table = 'bizboost_orders';

    protected $fillable = [
        'business_id',
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_email',
        'delivery_address',
        'notes',
        'subtotal',
        'delivery_fee',
        'total',
        'currency',
        'payment_method',
        'payment_status',
        'order_status',
        'source',
        'payment_reference',
        'paid_at',
        'delivered_at',
        'meta',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'delivered_at' => 'datetime',
        'meta' => 'array',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BizBoostBusinessModel::class, 'business_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BizBoostOrderItemModel::class, 'order_id');
    }

    protected static function booted(): void
    {
        static::creating(function ($order) {
            $order->order_number = static::generateOrderNumber($order->business_id);
        });
    }

    private static function generateOrderNumber(int $businessId): string
    {
        $prefix = 'BB-' . $businessId . '-';
        $date = now()->format('Ymd');
        $last = static::where('business_id', $businessId)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        return $prefix . $date . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }
}
