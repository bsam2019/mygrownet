<?php

namespace App\Models\GrowMart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowMartReview extends Model
{
    protected $table = 'growmart_reviews';

    protected $fillable = [
        'user_id',
        'product_id',
        'rating',
        'review_text',
        'is_approved',
        'is_verified_purchase',
        'helpful_count',
        'seller_response',
        'seller_responded_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_approved' => 'boolean',
            'is_verified_purchase' => 'boolean',
            'helpful_count' => 'integer',
            'seller_responded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(GrowMartProduct::class, 'product_id');
    }
}
