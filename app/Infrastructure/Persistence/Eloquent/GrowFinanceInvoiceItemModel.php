<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowFinanceInvoiceItemModel extends Model
{
    protected $table = 'growfinance_invoice_items';

    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
        'discount_rate',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(GrowFinanceInvoiceModel::class, 'invoice_id');
    }

    public function calculateLineTotal(): float
    {
        $subtotal = $this->quantity * $this->unit_price;
        $discount = $subtotal * ($this->discount_rate / 100);
        $afterDiscount = $subtotal - $discount;
        $tax = $afterDiscount * ($this->tax_rate / 100);
        return $afterDiscount + $tax;
    }
}
