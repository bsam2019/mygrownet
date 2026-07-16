<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaCategoryModel extends Model
{
    protected $table = 'sa_categories';
    protected $fillable = ['sa_company_id', 'name', 'slug', 'description', 'parent_id', 'sort_order'];
    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id'); }
    public function items(): HasMany { return $this->hasMany(SaItemModel::class, 'sa_category_id'); }
}
