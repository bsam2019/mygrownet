<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\GrowFinance\ValueObjects\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class GrowFinanceExpenseModel extends Model
{
    protected $table = 'growfinance_expenses';

    protected $fillable = [
        'business_id',
        'vendor_id',
        'account_id',
        'expense_date',
        'category',
        'description',
        'amount',
        'tax_amount',
        'payment_method',
        'reference',
        'receipt_path',
        'receipt_size',
        'receipt_original_name',
        'receipt_mime_type',
        'is_recurring',
        'notes',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'payment_method' => PaymentMethod::class,
        'is_recurring' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'business_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(GrowFinanceVendorModel::class, 'vendor_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(GrowFinanceAccountModel::class, 'account_id');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(GrowFinancePaymentModel::class, 'payable');
    }

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function getTotalAmountAttribute(): float
    {
        return $this->amount + $this->tax_amount;
    }
}
