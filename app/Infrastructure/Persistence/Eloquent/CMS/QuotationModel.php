<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuotationModel extends Model
{
    protected $table = 'cms_quotations';

    protected $fillable = [
        'company_id',
        'customer_id',
        'quotation_number',
        'quotation_date',
        'expiry_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'status',
        'notes',
        'terms',
        'converted_to_job_id',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'expiry_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItemModel::class, 'quotation_id');
    }

    public function convertedToJob(): BelongsTo
    {
        return $this->belongsTo(JobModel::class, 'converted_to_job_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'approved_by');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isConverted(): bool
    {
        return $this->converted_to_job_id !== null;
    }
}
