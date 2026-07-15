<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaReceiptModel extends Model
{
    protected $table = 'sa_receipts';
    protected $fillable = [
        'sa_company_id', 'receipt_number', 'sa_sale_id', 'sa_invoice_id',
        'customer_name', 'customer_phone', 'customer_email',
        'receipt_date', 'payment_method', 'subtotal', 'total',
        'amount_received', 'change_due', 'reference_number', 'notes', 'created_by',
    ];
    protected $casts = [
        'receipt_date' => 'date',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_received' => 'decimal:2',
        'change_due' => 'decimal:2',
    ];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function items(): HasMany { return $this->hasMany(SaReceiptItemModel::class, 'sa_receipt_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(\App\Models\User::class, 'created_by'); }
    public function sale(): BelongsTo { return $this->belongsTo(SaSaleModel::class, 'sa_sale_id'); }
    public function invoice(): BelongsTo { return $this->belongsTo(SaInvoiceModel::class, 'sa_invoice_id'); }
}
