<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BOQCategoryModel extends Model
{
    protected $table = 'cms_boq_categories';

    protected $fillable = [
        'company_id', 'name', 'code', 'description', 'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BOQItemModel::class, 'category_id');
    }
}
