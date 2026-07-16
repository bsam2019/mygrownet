<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaProductVariantModel extends Model
{
    protected $table = 'sa_product_variants';
    protected $fillable = ['sa_company_id', 'sa_item_id', 'parent_item_id', 'variant_name', 'sku', 'barcode', 'unit_price', 'wholesale_price', 'vip_price', 'sort_order'];
    protected $casts = ['unit_price' => 'decimal:2', 'wholesale_price' => 'decimal:2', 'vip_price' => 'decimal:2'];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function item(): BelongsTo { return $this->belongsTo(SaItemModel::class, 'sa_item_id'); }
    public function parentItem(): BelongsTo { return $this->belongsTo(SaItemModel::class, 'parent_item_id'); }
}
