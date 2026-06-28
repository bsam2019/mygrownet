<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderModel extends Model
{
    protected $table = 'cms_purchase_orders';

    protected $fillable = [
        'company_id', 'po_number', 'vendor_id', 'po_date', 'expected_delivery_date', 'actual_delivery_date',
        'status', 'subtotal', 'tax_amount', 'total_amount', 'currency_code', 'exchange_rate',
        'delivery_address', 'notes', 'terms_conditions', 'approved_by', 'approved_at', 'created_by',
    ];

    protected $casts = [
        'po_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'approved_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(VendorModel::class, 'vendor_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItemModel::class, 'purchase_order_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
