<?php

namespace App\Models\GrowMart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowMartOrderItem extends Model
{
    protected $table = 'growmart_order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(GrowMartOrder::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(GrowMartProduct::class, 'product_id');
    }
}
