<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialPriceHistoryModel extends Model
{
    protected $table = 'cms_material_price_history';

    protected $fillable = [
        'material_id',
        'old_price',
        'new_price',
        'change_percentage',
        'reason',
        'changed_by',
        'effective_date',
    ];

    protected $casts = [
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
        'change_percentage' => 'decimal:2',
        'effective_date' => 'datetime',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(MaterialModel::class, 'material_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'changed_by');
    }

    public function isIncrease(): bool
    {
        return $this->new_price > $this->old_price;
    }

    public function isDecrease(): bool
    {
        return $this->new_price < $this->old_price;
    }
}
