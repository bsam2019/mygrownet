<?php

namespace App\Infrastructure\PrimeEdge\Persistence;

use Illuminate\Database\Eloquent\Model;

class InvoiceLineItemModel extends Model
{
    protected $table = 'prime_edge_invoice_line_items';

    protected $fillable = [
        'id',
        'invoice_id',
        'description',
        'unit_price_amount',
        'unit_price_currency',
        'quantity',
        'total_amount',
        'total_currency',
    ];

    protected $casts = [
        'unit_price_amount' => 'float',
        'quantity' => 'integer',
        'total_amount' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
