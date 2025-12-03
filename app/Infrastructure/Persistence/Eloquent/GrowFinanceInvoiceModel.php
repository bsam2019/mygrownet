<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\GrowFinance\ValueObjects\InvoiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class GrowFinanceInvoiceModel extends Model
{
    protected $table = 'growfinance_invoices';

    protected $fillable = [
        'business_id',
        'customer_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'amount_paid',
        'notes',
        'terms',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'status' => InvoiceStatus::class,
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

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
        return $this->hasMany(GrowFinanceInvoiceItemModel::class, 'invoice_id');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(GrowFinancePaymentModel::class, 'payable');
    }

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeStatus($query, InvoiceStatus $status)
    {
        return $query->where('status', $status->value);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', [InvoiceStatus::PAID->value, InvoiceStatus::CANCELLED->value]);
    }

    public function getBalanceDueAttribute(): float
    {
        return $this->total_amount - $this->amount_paid;
    }

    public function isPaid(): bool
    {
        return $this->status === InvoiceStatus::PAID;
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !$this->isPaid();
    }
}
