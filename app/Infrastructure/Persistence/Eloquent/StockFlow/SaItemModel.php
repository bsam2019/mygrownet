<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaItemModel extends Model
{
    protected $table = 'sa_items';
    protected $fillable = [
        'sa_company_id', 'sa_department_id', 'sa_bin_id',
        'name', 'sku', 'description', 'unit_price', 'unit',
        'system_quantity', 'reorder_level', 'category',
        'is_expirable', 'expiry_date', 'notes',
    ];
    protected $casts = [
        'unit_price' => 'decimal:2',
        'system_quantity' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'is_expirable' => 'boolean',
        'expiry_date' => 'date',
    ];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function department(): BelongsTo { return $this->belongsTo(SaDepartmentModel::class, 'sa_department_id'); }
    public function bin(): BelongsTo { return $this->belongsTo(SaBinModel::class, 'sa_bin_id'); }
    public function stockMovements(): HasMany { return $this->hasMany(SaStockMovementModel::class, 'sa_item_id'); }
}
