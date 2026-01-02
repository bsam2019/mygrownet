<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuickInvoiceItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'document_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'amount',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(QuickInvoiceDocument::class, 'document_id');
    }
}
