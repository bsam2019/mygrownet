<?php

namespace App\Infrastructure\GrowBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GrowBuilderOrder extends Model
{
    protected $table = 'growbuilder_orders';

    protected $fillable = [
        'site_id',
        'site_user_id',
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'customer_city',
        'items',
        'subtotal',
        'shipping_cost',
        'discount_amount',
        'discount_code',
        'total',
        'status',
        'payment_method',
        'payment_reference',
        'notes',
        'admin_notes',
        'paid_at',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'items' => 'array',
        'subtotal' => 'integer',
        'shipping_cost' => 'integer',
        'discount_amount' => 'integer',
        'total' => 'integer',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(GrowBuilderSite::class, 'site_id');
    }

    public function siteUser(): BelongsTo
    {
        return $this->belongsTo(SiteUser::class, 'site_user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(GrowBuilderPayment::class, 'order_id');
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(GrowBuilderPayment::class, 'order_id')->latestOfMany();
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(GrowBuilderInvoice::class, 'order_id');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'payment_pending']);
    }

    public function scopePaid($query)
    {
        return $query->whereNotNull('paid_at');
    }

    public function getTotalInKwachaAttribute(): float
    {
        return $this->total / 100;
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'K' . number_format($this->total_in_kwacha, 2);
    }

    public function getItemCountAttribute(): int
    {
        return array_sum(array_column($this->items ?? [], 'quantity'));
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'payment_pending', 'paid']);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'payment_pending' => 'orange',
            'paid' => 'blue',
            'processing' => 'indigo',
            'shipped' => 'purple',
            'delivered', 'completed' => 'green',
            'cancelled' => 'red',
            'refunded' => 'gray',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'payment_pending' => 'Awaiting Payment',
            'paid' => 'Paid',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
            default => ucfirst($this->status),
        };
    }
}
