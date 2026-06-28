<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class POSSaleModel extends Model
{
    use SoftDeletes;

    protected $table = 'pos_sales';

    protected $fillable = [
        'user_id',
        'shift_id',
        'module_context',
        'sale_number',
        'customer_type',
        'customer_id',
        'customer_name',
        'customer_phone',
        'item_count',
        'subtotal',
        'discount_amount',
        'discount_percentage',
        'tax_amount',
        'total_amount',
        'payment_method',
        'payment_reference',
        'amount_paid',
        'change_given',
        'status',
        'notes',
        'currency',
        'served_by_type',
        'served_by_id',
        'completed_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_given' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(POSShiftModel::class, 'shift_id');
    }

    public function customer(): MorphTo
    {
        return $this->morphTo();
    }

    public function servedBy(): MorphTo
    {
        return $this->morphTo('served_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(POSSaleItemModel::class, 'sale_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForModule($query, string $module)
    {
        return $query->where('module_context', $module);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isVoided(): bool
    {
        return $this->status === 'voided';
    }

    public function getProfitAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return ($item->unit_price - $item->cost_price) * $item->quantity;
        });
    }
}
