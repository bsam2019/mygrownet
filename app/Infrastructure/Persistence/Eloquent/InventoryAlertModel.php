<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAlertModel extends Model
{
    protected $table = 'inventory_alerts';

    protected $fillable = [
        'item_id',
        'user_id',
        'type',
        'threshold_value',
        'current_value',
        'is_acknowledged',
        'acknowledged_at',
    ];

    protected $casts = [
        'is_acknowledged' => 'boolean',
        'acknowledged_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItemModel::class, 'item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function scopeUnacknowledged($query)
    {
        return $query->where('is_acknowledged', false);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
