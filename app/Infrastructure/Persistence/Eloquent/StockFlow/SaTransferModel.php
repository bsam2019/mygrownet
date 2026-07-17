<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaTransferModel extends Model
{
    protected $table = 'sa_transfers';
    protected $fillable = [
        'sa_company_id', 'transfer_number', 'from_warehouse_id', 'to_warehouse_id',
        'status', 'transferred_by', 'received_by', 'notes',
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function fromWarehouse(): BelongsTo { return $this->belongsTo(SaWarehouseModel::class, 'from_warehouse_id'); }
    public function toWarehouse(): BelongsTo { return $this->belongsTo(SaWarehouseModel::class, 'to_warehouse_id'); }
    public function transferrer(): BelongsTo { return $this->belongsTo(SaUserModel::class, 'transferred_by'); }
    public function receiver(): BelongsTo { return $this->belongsTo(SaUserModel::class, 'received_by'); }
    public function items(): HasMany { return $this->hasMany(SaTransferItemModel::class, 'sa_transfer_id'); }
}
