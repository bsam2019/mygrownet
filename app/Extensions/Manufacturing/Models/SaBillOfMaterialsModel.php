<?php

namespace App\Extensions\Manufacturing\Models;

use App\Infrastructure\Persistence\Eloquent\StockFlow\SaCompanyModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaItemModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaBillOfMaterialsModel extends Model
{
    protected $table = 'sa_bill_of_materials';
    protected $fillable = ['sa_company_id', 'sa_item_id', 'name', 'quantity', 'uom', 'status', 'version', 'notes'];
    protected $casts = ['quantity' => 'decimal:2'];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function item(): BelongsTo { return $this->belongsTo(SaItemModel::class, 'sa_item_id'); }
    public function materials(): HasMany { return $this->hasMany(SaBomMaterialModel::class, 'sa_bom_id'); }
}
