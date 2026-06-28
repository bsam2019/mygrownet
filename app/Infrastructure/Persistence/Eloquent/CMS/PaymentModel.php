<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentModel extends Model
{
    protected $table = 'cms_payments';

    protected $fillable = [
        'company_id',
        'customer_id',
        'invoice_id',
        'payment_number',
        'payment_date',
        'amount',
        'unallocated_amount',
        'is_overpayment',
        'payment_method',
        'reference_number',
        'notes',
        'received_by',
        'is_voided',
        'void_reason',
        'voided_at',
        'voided_by',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'unallocated_amount' => 'decimal:2',
        'is_overpayment' => 'boolean',
        'is_voided' => 'boolean',
        'voided_at' => 'datetime',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(InvoiceModel::class, 'invoice_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'received_by');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocationModel::class, 'payment_id');
    }

    // Scopes
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }
}
