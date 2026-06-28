<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class POSShiftModel extends Model
{
    protected $table = 'pos_shifts';

    protected $fillable = [
        'user_id',
        'module_context',
        'shift_number',
        'opening_cash',
        'closing_cash',
        'expected_cash',
        'cash_difference',
        'total_sales',
        'total_cash_sales',
        'total_mobile_sales',
        'total_card_sales',
        'transaction_count',
        'started_at',
        'ended_at',
        'opening_notes',
        'closing_notes',
        'status',
        'operator_type',
        'operator_id',
    ];

    protected $casts = [
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'total_cash_sales' => 'decimal:2',
        'total_mobile_sales' => 'decimal:2',
        'total_card_sales' => 'decimal:2',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function operator(): MorphTo
    {
        return $this->morphTo();
    }

    public function sales(): HasMany
    {
        return $this->hasMany(POSSaleModel::class, 'shift_id');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeForModule($query, string $module)
    {
        return $query->where('module_context', $module);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function getDurationAttribute(): ?string
    {
        if (!$this->started_at) return null;
        
        $end = $this->ended_at ?? now();
        return $this->started_at->diffForHumans($end, true);
    }
}
