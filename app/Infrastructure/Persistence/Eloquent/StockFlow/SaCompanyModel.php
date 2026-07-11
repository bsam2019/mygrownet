<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaCompanyModel extends Model
{
    protected $table = 'sa_companies';
    protected $fillable = ['name', 'subdomain', 'email', 'phone', 'address', 'city', 'country', 'contact_person', 'currency', 'status'];
    protected $casts = ['status' => 'string'];

    public function departments(): HasMany { return $this->hasMany(SaDepartmentModel::class, 'sa_company_id'); }
    public function bins(): HasMany { return $this->hasMany(SaBinModel::class, 'sa_company_id'); }
    public function items(): HasMany { return $this->hasMany(SaItemModel::class, 'sa_company_id'); }
    public function audits(): HasMany { return $this->hasMany(SaAuditModel::class, 'sa_company_id'); }
    public function physicalCounts(): HasMany { return $this->hasMany(SaPhysicalCountModel::class, 'sa_company_id'); }
    public function sales(): HasMany { return $this->hasMany(SaSaleModel::class, 'sa_company_id'); }
    public function purchaseOrders(): HasMany { return $this->hasMany(SaPurchaseOrderModel::class, 'sa_company_id'); }
    public function cashRegisters(): HasMany { return $this->hasMany(SaCashRegisterModel::class, 'sa_company_id'); }
    public function stockMovements(): HasMany { return $this->hasMany(SaStockMovementModel::class, 'sa_company_id'); }
    public function suppliers(): HasMany { return $this->hasMany(SaSupplierModel::class, 'sa_company_id'); }
}
