<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovementModel extends Model
{
    protected $table = 'stock_movements';

    protected $fillable = [
        'item_id',
        'user_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'unit_cost',
        'total_value',
        'reference_type',
        'reference_id',
        'notes',
        'movement_date',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'total_value' => 'decimal:2',
        'movement_date' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItemModel::class, 'item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function isIncoming(): bool
    {
        return $this->quantity > 0;
    }

    public function isOutgoing(): bool
    {
        return $this->quantity < 0;
    }
}
