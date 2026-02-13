<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItemModel extends Model
{
    protected $table = 'cms_invoice_items';

    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(InvoiceModel::class, 'invoice_id');
    }
}
