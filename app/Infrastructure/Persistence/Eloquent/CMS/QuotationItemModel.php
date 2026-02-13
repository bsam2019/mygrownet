<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItemModel extends Model
{
    protected $table = 'cms_quotation_items';

    protected $fillable = [
        'quotation_id',
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
        'discount_rate',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(QuotationModel::class, 'quotation_id');
    }
}
