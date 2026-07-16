<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaSupplierReturnModel extends Model
{
    protected $table = 'sa_supplier_returns';
    protected $fillable = ['sa_company_id', 'sa_supplier_id', 'sa_purchase_order_id', 'return_number', 'return_date', 'reason', 'total_refund', 'notes', 'created_by'];
    protected $casts = ['return_date' => 'date', 'total_refund' => 'decimal:2'];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function supplier(): BelongsTo { return $this->belongsTo(SaSupplierModel::class, 'sa_supplier_id'); }
    public function purchaseOrder(): BelongsTo { return $this->belongsTo(SaPurchaseOrderModel::class, 'sa_purchase_order_id'); }
    public function items(): HasMany { return $this->hasMany(SaSupplierReturnItemModel::class, 'sa_supplier_return_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(SaUserModel::class, 'created_by'); }
}
