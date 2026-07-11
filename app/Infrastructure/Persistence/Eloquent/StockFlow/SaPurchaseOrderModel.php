<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaPurchaseOrderModel extends Model
{
    protected $table = 'sa_purchase_orders';
    protected $fillable = [
        'sa_company_id', 'sa_supplier_id', 'order_number', 'order_date',
        'status', 'subtotal', 'tax', 'total', 'notes',
    ];
    protected $casts = [
        'order_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function supplier(): BelongsTo { return $this->belongsTo(SaSupplierModel::class, 'sa_supplier_id'); }
    public function items(): HasMany { return $this->hasMany(SaPurchaseOrderItemModel::class, 'sa_purchase_order_id'); }
}
