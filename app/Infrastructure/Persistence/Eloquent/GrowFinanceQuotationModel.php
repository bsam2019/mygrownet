<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\GrowFinance\ValueObjects\QuotationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrowFinanceQuotationModel extends Model
{
    protected $table = 'growfinance_quotations';

    protected $fillable = [
        'business_id',
        'customer_id',
        'template_id',
        'quotation_number',
        'quotation_date',
        'valid_until',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'notes',
        'terms',
        'subject',
        'converted_invoice_id',
        'sent_at',
        'accepted_at',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    // Relationships
    public function business(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'business_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(GrowFinanceCustomerModel::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GrowFinanceQuotationItemModel::class, 'quotation_id');
    }

    public function convertedInvoice(): BelongsTo
    {
        return $this->belongsTo(GrowFinanceInvoiceModel::class, 'converted_invoice_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(GrowFinanceInvoiceTemplateModel::class, 'template_id');
    }

    // Scopes
    public function scopeForBusiness(Builder $query, int $businessId): Builder
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', [
            QuotationStatus::DRAFT->value,
            QuotationStatus::SENT->value,
        ]);
    }

    // Accessors
    public function getStatusEnumAttribute(): QuotationStatus
    {
        return QuotationStatus::from($this->status);
    }

    public function getIsExpiredAttribute(): bool
    {
        if (!$this->valid_until) {
            return false;
        }
        return $this->valid_until->isPast() && 
            !in_array($this->status, [QuotationStatus::CONVERTED->value, QuotationStatus::EXPIRED->value]);
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->valid_until) {
            return null;
        }
        return now()->diffInDays($this->valid_until, false);
    }
}
