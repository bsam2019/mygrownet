<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaWarehouseModel extends Model
{
    protected $table = 'sa_warehouses';
    protected $fillable = ['sa_company_id', 'sa_branch_id', 'name', 'code', 'address', 'city', 'country', 'contact_person', 'phone', 'is_default'];
    protected $casts = ['is_default' => 'boolean'];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function departments(): HasMany { return $this->hasMany(SaDepartmentModel::class, 'sa_warehouse_id'); }
    public function items(): HasMany { return $this->hasMany(SaItemModel::class, 'sa_warehouse_id'); }
}
