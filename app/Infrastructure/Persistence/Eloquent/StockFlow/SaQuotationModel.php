<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaQuotationModel extends Model
{
    protected $table = 'sa_quotations';
    protected $fillable = [
        'sa_company_id', 'quotation_number', 'customer_name', 'customer_phone', 'customer_email',
        'quotation_date', 'expiry_date', 'status', 'subtotal', 'discount', 'tax', 'total',
        'notes', 'terms_conditions', 'created_by', 'converted_to_sale_id',
    ];
    protected $casts = [
        'quotation_date' => 'date',
        'expiry_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function items(): HasMany { return $this->hasMany(SaQuotationItemModel::class, 'sa_quotation_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(\App\Models\User::class, 'created_by'); }
}
