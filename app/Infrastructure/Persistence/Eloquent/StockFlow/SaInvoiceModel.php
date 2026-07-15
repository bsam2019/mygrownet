<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaInvoiceModel extends Model
{
    protected $table = 'sa_invoices';
    protected $fillable = [
        'sa_company_id', 'invoice_number', 'customer_name', 'customer_phone', 'customer_email',
        'invoice_date', 'due_date', 'status', 'subtotal', 'discount', 'tax', 'total',
        'amount_paid', 'balance_due', 'payment_terms', 'notes', 'created_by',
        'sa_quotation_id', 'sa_sale_id',
    ];
    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
    ];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function items(): HasMany { return $this->hasMany(SaInvoiceItemModel::class, 'sa_invoice_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(\App\Models\User::class, 'created_by'); }
    public function quotation(): BelongsTo { return $this->belongsTo(SaQuotationModel::class, 'sa_quotation_id'); }
    public function sale(): BelongsTo { return $this->belongsTo(SaSaleModel::class, 'sa_sale_id'); }
}
