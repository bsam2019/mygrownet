<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LowStockAlertModel extends Model
{
    protected $table = 'cms_low_stock_alerts';

    public $timestamps = false;

    protected $fillable = [
        'company_id',
        'inventory_item_id',
        'current_stock',
        'minimum_stock',
        'is_resolved',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'current_stock' => 'integer',
        'minimum_stock' => 'integer',
        'is_resolved' => 'boolean',
        'created_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItemModel::class, 'inventory_item_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'resolved_by');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }
}
