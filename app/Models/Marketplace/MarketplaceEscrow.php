<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceEscrow extends Model
{
    protected $table = 'marketplace_escrow';

    protected $fillable = [
        'order_id',
        'amount',
        'status',
        'held_at',
        'released_at',
        'release_reason',
    ];

    protected $casts = [
        'amount' => 'integer',
        'held_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(MarketplaceOrder::class, 'order_id');
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'K' . number_format($this->amount / 100, 2);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'held' => 'Funds Held',
            'released' => 'Funds Released',
            'refunded' => 'Refunded',
            'disputed' => 'Under Dispute',
            default => ucfirst($this->status),
        };
    }
}
