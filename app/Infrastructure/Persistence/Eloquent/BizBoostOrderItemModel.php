<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BizBoostOrderItemModel extends Model
{
    use HasFactory;

    protected $table = 'bizboost_order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'unit_price',
        'quantity',
        'subtotal',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(BizBoostOrderModel::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(BizBoostProductModel::class, 'product_id');
    }
}
