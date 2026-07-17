<?php

namespace App\Extensions\Manufacturing\Models;

use App\Infrastructure\Persistence\Eloquent\StockFlow\SaItemModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaBomMaterialModel extends Model
{
    protected $table = 'sa_bom_materials';
    protected $fillable = ['sa_bom_id', 'sa_item_id', 'quantity', 'uom', 'waste_factor', 'sort_order'];
    protected $casts = ['quantity' => 'decimal:2', 'waste_factor' => 'decimal:2'];

    public function bom(): BelongsTo { return $this->belongsTo(SaBillOfMaterialsModel::class, 'sa_bom_id'); }
    public function item(): BelongsTo { return $this->belongsTo(SaItemModel::class, 'sa_item_id'); }
}
