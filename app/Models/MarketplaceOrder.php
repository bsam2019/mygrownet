<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MarketplaceOrder extends Model
{
    protected $table = 'marketplace_orders';

    protected $fillable = [
        'order_number',
        'buyer_id',
        'seller_id',
        'status',
        'subtotal',
        'delivery_fee',
        'total',
        'delivery_method',
        'delivery_address',
        'delivery_notes',
        'payment_reference',
        'tracking_info',
        'delivery_proof',
        'cancellation_reason',
        'cancelled_by',
        'dispute_reason',
        'dispute_resolution',
        'paid_at',
        'shipped_at',
        'delivered_at',
        'confirmed_at',
        'cancelled_at',
        'disputed_at',
    ];

    protected $casts = [
        'delivery_address' => 'array',
        'subtotal' => 'integer',
        'delivery_fee' => 'integer',
        'total' => 'integer',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'disputed_at' => 'datetime',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(MarketplaceSeller::class, 'seller_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MarketplaceOrderItem::class, 'order_id');
    }

    public function escrow(): HasOne
    {
        return $this->hasOne(MarketplaceEscrow::class, 'order_id');
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'K' . number_format($this->total / 100, 2);
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'K' . number_format($this->subtotal / 100, 2);
    }

    public function getFormattedDeliveryFeeAttribute(): string
    {
        return 'K' . number_format($this->delivery_fee / 100, 2);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending Payment',
            'paid' => 'Paid',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'disputed' => 'Disputed',
            'refunded' => 'Refunded',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'paid' => 'blue',
            'processing' => 'indigo',
            'shipped' => 'purple',
            'delivered' => 'teal',
            'completed' => 'green',
            'cancelled' => 'gray',
            'disputed' => 'red',
            'refunded' => 'orange',
            default => 'gray',
        };
    }

    public function canBeShipped(): bool
    {
        return in_array($this->status, ['paid', 'processing']);
    }

    public function canBeDelivered(): bool
    {
        return $this->status === 'shipped';
    }

    public function canBeConfirmed(): bool
    {
        return $this->status === 'delivered';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'paid']);
    }
}
