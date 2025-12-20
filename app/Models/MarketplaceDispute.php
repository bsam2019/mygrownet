<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceDispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'buyer_id',
        'seller_id',
        'type',
        'description',
        'evidence',
        'status',
        'resolution',
        'resolution_type',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'evidence' => 'array',
        'resolved_at' => 'datetime',
    ];

    protected $appends = ['status_label', 'type_label'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(MarketplaceOrder::class, 'order_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(MarketplaceSeller::class, 'seller_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'open' => 'Open',
            'investigating' => 'Under Investigation',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
            default => ucfirst($this->status),
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'not_received' => 'Item Not Received',
            'not_as_described' => 'Not As Described',
            'damaged' => 'Damaged Item',
            'wrong_item' => 'Wrong Item',
            'other' => 'Other',
            default => ucfirst($this->type),
        };
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInvestigating($query)
    {
        return $query->where('status', 'investigating');
    }
}
