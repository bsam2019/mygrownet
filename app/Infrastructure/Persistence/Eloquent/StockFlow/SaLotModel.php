<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaLotModel extends Model
{
    protected $table = 'sa_lots';
    protected $fillable = ['sa_company_id', 'sa_item_id', 'lot_number', 'manufacturing_date', 'expiry_date', 'received_date', 'initial_quantity', 'current_quantity', 'status'];
    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'received_date' => 'date',
        'initial_quantity' => 'decimal:2',
        'current_quantity' => 'decimal:2',
    ];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function item(): BelongsTo { return $this->belongsTo(SaItemModel::class, 'sa_item_id'); }
}
