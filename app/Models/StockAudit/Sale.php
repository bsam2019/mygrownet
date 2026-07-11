<?php

namespace App\Models\StockAudit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $table = 'sa_sales';

    protected $fillable = [
        'sa_company_id', 'receipt_number', 'sale_date', 'sale_time',
        'payment_method', 'subtotal', 'discount', 'tax', 'total',
        'amount_tendered', 'change_due', 'sold_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'sale_time' => 'datetime:H:i',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_tendered' => 'decimal:2',
            'change_due' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'sa_company_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'sa_sale_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'sold_by');
    }
}
