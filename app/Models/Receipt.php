<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    protected $fillable = [
        'receipt_number',
        'user_id',
        'type',
        'amount',
        'payment_method',
        'transaction_reference',
        'description',
        'pdf_path',
        'emailed',
        'emailed_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'emailed' => 'boolean',
        'emailed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function getDownloadUrlAttribute(): string
    {
        return route('receipts.download', $this->id);
    }
}
