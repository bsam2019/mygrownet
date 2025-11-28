<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DividendDeclaration extends Model
{
    protected $fillable = [
        'declaration_name',
        'total_amount',
        'per_share_amount',
        'declaration_date',
        'record_date',
        'payment_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'per_share_amount' => 'decimal:4',
        'declaration_date' => 'date',
        'record_date' => 'date',
        'payment_date' => 'date',
    ];

    public function dividends(): HasMany
    {
        return $this->hasMany(InvestorDividend::class);
    }

    /**
     * Check if declaration is upcoming
     */
    public function isUpcoming(): bool
    {
        return $this->payment_date->isFuture() && $this->status === 'approved';
    }

    /**
     * Check if declaration is paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
