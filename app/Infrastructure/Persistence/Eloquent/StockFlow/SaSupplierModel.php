<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaSupplierModel extends Model
{
    protected $table = 'sa_suppliers';
    protected $fillable = ['sa_company_id', 'name', 'contact_person', 'phone', 'email', 'address', 'payment_terms'];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function purchaseOrders(): HasMany { return $this->hasMany(SaPurchaseOrderModel::class, 'sa_supplier_id'); }
}
