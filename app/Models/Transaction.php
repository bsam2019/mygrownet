<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'investment_id',
        'amount',
        'transaction_type',
        'status',
        'payment_method',
        'reference_number',
        'description',
        'processed_at',
        'processed_by',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime'
    ];

    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }

    public function scopeEarnings($query)
    {
        return $query->where('transaction_type', 'return');
    }
}
