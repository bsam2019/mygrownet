<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BizBoostSaleModel extends Model
{
    use HasFactory;

    protected $table = 'bizboost_sales';

    protected $fillable = [
        'business_id',
        'customer_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_price',
        'total_amount',
        'currency',
        'sale_date',
        'payment_method',
        'source',
        'linked_post_id',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'sale_date' => 'date',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BizBoostBusinessModel::class, 'business_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(BizBoostCustomerModel::class, 'customer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(BizBoostProductModel::class, 'product_id');
    }

    public function linkedPost(): BelongsTo
    {
        return $this->belongsTo(BizBoostPostModel::class, 'linked_post_id');
    }

    protected static function booted(): void
    {
        static::created(function ($sale) {
            if ($sale->customer_id) {
                $sale->customer->updatePurchaseStats();
            }
        });
    }
}
