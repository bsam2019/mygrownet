<?php

namespace App\Infrastructure\BizDocs\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentItemModel extends Model
{
    protected $table = 'bizdocs_document_items';

    protected $fillable = [
        'document_id',
        'description',
        'dimensions',
        'dimensions_value',
        'quantity',
        'unit_price',
        'tax_rate',
        'discount_amount',
        'line_total',
        'sort_order',
    ];

    protected $casts = [
        'dimensions_value' => 'decimal:4',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(DocumentModel::class, 'document_id');
    }
}
