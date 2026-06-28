<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'order_id',
        'payout_id',
        'type',
        'amount',
        'balance_after',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected $appends = ['formatted_amount', 'type_label'];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(MarketplaceSeller::class, 'seller_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(MarketplaceOrder::class, 'order_id');
    }

    public function payout(): BelongsTo
    {
        return $this->belongsTo(MarketplacePayout::class, 'payout_id');
    }

    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->amount >= 0 ? '+' : '';
        return $prefix . 'K' . number_format(abs($this->amount) / 100, 2);
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'sale' => 'Sale',
            'commission' => 'Commission',
            'payout' => 'Payout',
            'refund' => 'Refund',
            'adjustment' => 'Adjustment',
            default => ucfirst($this->type),
        };
    }

    public function scopeForSeller($query, int $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
