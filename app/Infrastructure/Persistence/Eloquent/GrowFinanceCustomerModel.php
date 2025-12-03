<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrowFinanceCustomerModel extends Model
{
    protected $table = 'growfinance_customers';

    protected $fillable = [
        'business_id',
        'name',
        'email',
        'phone',
        'address',
        'tax_number',
        'credit_limit',
        'outstanding_balance',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'business_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(GrowFinanceInvoiceModel::class, 'customer_id');
    }

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithOutstanding($query)
    {
        return $query->where('outstanding_balance', '>', 0);
    }
}
