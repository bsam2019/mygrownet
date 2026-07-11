<?php

namespace App\Models\StockAudit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $table = 'sa_sale_items';

    protected $fillable = [
        'sa_sale_id', 'sa_item_id', 'item_name',
        'quantity', 'unit_price', 'discount', 'total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sa_sale_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'sa_item_id');
    }
}
