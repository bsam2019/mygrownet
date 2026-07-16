<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaBranchModel extends Model
{
    protected $table = 'sa_branches';
    protected $fillable = ['sa_company_id', 'name', 'code', 'phone', 'email', 'address', 'city', 'country', 'is_head_office', 'is_active'];
    protected $casts = ['is_head_office' => 'boolean', 'is_active' => 'boolean'];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function warehouses(): HasMany { return $this->hasMany(SaWarehouseModel::class, 'sa_branch_id'); }
}
