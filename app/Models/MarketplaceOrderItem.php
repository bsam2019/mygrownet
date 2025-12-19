<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceOrderItem extends Model
{
    protected $table = 'marketplace_order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'unit_price' => 'integer',
        'total_price' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(MarketplaceOrder::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(MarketplaceProduct::class, 'product_id');
    }

    public function getFormattedUnitPriceAttribute(): string
    {
        return 'K' . number_format($this->unit_price / 100, 2);
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return 'K' . number_format($this->total_price / 100, 2);
    }
}
