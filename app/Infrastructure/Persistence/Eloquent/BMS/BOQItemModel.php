<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BOQItemModel extends Model
{
    protected $table = 'cms_boq_items';

    protected $fillable = [
        'boq_id', 'category_id', 'item_code', 'description', 'unit',
        'quantity', 'unit_rate', 'amount', 'actual_quantity', 'actual_amount',
        'sort_order', 'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_rate' => 'decimal:2',
        'amount' => 'decimal:2',
        'actual_quantity' => 'decimal:3',
        'actual_amount' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($item) {
            $item->amount = $item->quantity * $item->unit_rate;
            if ($item->actual_quantity) {
                $item->actual_amount = $item->actual_quantity * $item->unit_rate;
            }
        });
    }

    public function boq(): BelongsTo
    {
        return $this->belongsTo(BOQModel::class, 'boq_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BOQCategoryModel::class, 'category_id');
    }

    public function getVarianceAttribute(): ?float
    {
        if (!$this->actual_amount) {
            return null;
        }
        return $this->actual_amount - $this->amount;
    }
}
