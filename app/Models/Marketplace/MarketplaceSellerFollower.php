<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceSellerFollower extends Model
{
    protected $fillable = ['seller_id', 'user_id'];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(MarketplaceSeller::class, 'seller_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
