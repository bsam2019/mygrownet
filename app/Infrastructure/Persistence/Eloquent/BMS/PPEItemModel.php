<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PPEItemModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_ppe_items';

    protected $fillable = [
        'company_id',
        'item_name',
        'item_type',
        'description',
        'quantity_in_stock',
        'reorder_level',
        'unit_cost',
    ];

    protected $casts = [
        'quantity_in_stock' => 'integer',
        'reorder_level' => 'integer',
        'unit_cost' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(PPEDistributionModel::class, 'ppe_item_id');
    }
}
