<?php

namespace App\Extensions\Restaurant\Models;

use App\Infrastructure\Persistence\Eloquent\StockFlow\SaCompanyModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaItemModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaWastageRecordModel extends Model
{
    protected $table = 'sa_wastage_records';
    protected $fillable = ['sa_company_id', 'sa_item_id', 'quantity', 'unit_cost', 'reason', 'reference_type', 'reference_id', 'notes', 'occurred_at'];
    protected $casts = ['quantity' => 'decimal:2', 'unit_cost' => 'decimal:2', 'occurred_at' => 'date'];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function item(): BelongsTo { return $this->belongsTo(SaItemModel::class, 'sa_item_id'); }
}
