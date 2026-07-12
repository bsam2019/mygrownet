<?php

namespace App\Infrastructure\PrimeEdge\Persistence;

use Illuminate\Database\Eloquent\Model;

class InvoiceModel extends Model
{
    protected $table = 'prime_edge_invoices';

    protected $fillable = [
        'id',
        'client_id',
        'number',
        'status',
        'total_amount',
        'total_currency',
        'engagement_id',
        'notes',
        'sent_at',
        'paid_at',
    ];

    protected $casts = [
        'total_amount' => 'float',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function lineItems()
    {
        return $this->hasMany(InvoiceLineItemModel::class, 'invoice_id');
    }
}
