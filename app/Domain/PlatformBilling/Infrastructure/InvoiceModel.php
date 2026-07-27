<?php

namespace App\Domain\PlatformBilling\Infrastructure;

use Illuminate\Database\Eloquent\Model;

class InvoiceModel extends Model
{
    protected $table = 'billing_invoices';

    protected $fillable = [
        'subscription_id', 'organization_id', 'invoice_number',
        'amount', 'currency', 'status', 'issued_at',
        'due_date', 'paid_at', 'description', 'line_items',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'issued_at' => 'datetime',
        'due_date' => 'datetime',
        'paid_at' => 'datetime',
        'line_items' => 'array',
    ];
}
