<?php

namespace App\Models\GrowMart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowMartCartItem extends Model
{
    protected $table = 'growmart_cart_items';

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(GrowMartCart::class, 'cart_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(GrowMartProduct::class, 'product_id');
    }
}
