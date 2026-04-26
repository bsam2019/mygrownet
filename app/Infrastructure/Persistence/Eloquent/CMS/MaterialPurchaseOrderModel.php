<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class MaterialPurchaseOrderModel extends Model
{
    protected $table = 'cms_material_purchase_orders';

    protected $fillable = [
        'company_id',
        'job_id',
        'po_number',
        'supplier_name',
        'supplier_contact',
        'supplier_address',
        'subtotal',
        'tax_amount',
        'total_amount',
        'status',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'notes',
        'terms',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobModel::class, 'job_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItemModel::class, 'purchase_order_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'approved_by');
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('total_price');
        $this->total_amount = $this->subtotal + $this->tax_amount;
    }

    public function approve(int $approvedBy): void
    {
        $this->update([
            'status' => 'sent',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);
    }

    public function markAsConfirmed(): void
    {
        $this->update(['status' => 'confirmed']);
    }

    public function markAsReceived(?string $actualDeliveryDate = null): void
    {
        $this->update([
            'status' => 'received',
            'actual_delivery_date' => $actualDeliveryDate ?? now(),
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isReceived(): bool
    {
        return $this->status === 'received';
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeForJob(Builder $query, int $jobId): Builder
    {
        return $query->where('job_id', $jobId);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', ['sent', 'confirmed']);
    }
}
