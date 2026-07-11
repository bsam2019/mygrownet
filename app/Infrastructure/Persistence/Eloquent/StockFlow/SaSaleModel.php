<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaSaleModel extends Model
{
    protected $table = 'sa_sales';
    protected $fillable = [
        'sa_company_id', 'receipt_number', 'sale_date', 'sale_time',
        'payment_method', 'subtotal', 'discount', 'tax', 'total',
        'amount_tendered', 'change_due', 'sold_by', 'notes',
    ];
    protected $casts = [
        'sale_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_tendered' => 'decimal:2',
        'change_due' => 'decimal:2',
    ];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function items(): HasMany { return $this->hasMany(SaSaleItemModel::class, 'sa_sale_id'); }
    public function cashier(): BelongsTo { return $this->belongsTo(\App\Models\User::class, 'sold_by'); }
}
