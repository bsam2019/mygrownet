<?php

namespace App\Models\GrowMart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowMartWishlistItem extends Model
{
    protected $table = 'growmart_wishlist_items';

    protected $fillable = [
        'user_id',
        'product_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(GrowMartProduct::class, 'product_id');
    }
}
