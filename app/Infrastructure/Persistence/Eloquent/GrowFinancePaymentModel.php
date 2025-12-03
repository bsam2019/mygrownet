<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\GrowFinance\ValueObjects\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GrowFinancePaymentModel extends Model
{
    protected $table = 'growfinance_payments';

    protected $fillable = [
        'business_id',
        'payable_type',
        'payable_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'payment_method' => PaymentMethod::class,
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'business_id');
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }
}
