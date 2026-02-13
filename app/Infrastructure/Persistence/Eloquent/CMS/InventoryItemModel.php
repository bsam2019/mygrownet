<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItemModel extends Model
{
    protected $table = 'cms_inventory_items';

    protected $fillable = [
        'company_id',
        'item_code',
        'name',
        'description',
        'category',
        'unit',
        'unit_cost',
        'selling_price',
        'current_stock',
        'minimum_stock',
        'reorder_quantity',
        'supplier',
        'location',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'current_stock' => 'integer',
        'minimum_stock' => 'integer',
        'reorder_quantity' => 'integer',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovementModel::class, 'inventory_item_id');
    }

    public function jobUsages(): HasMany
    {
        return $this->hasMany(JobInventoryModel::class, 'inventory_item_id');
    }

    public function lowStockAlerts(): HasMany
    {
        return $this->hasMany(LowStockAlertModel::class, 'inventory_item_id');
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->minimum_stock;
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('current_stock', '<=', 'minimum_stock');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
