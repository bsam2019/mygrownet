<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorDividend extends Model
{
    protected $fillable = [
        'investor_account_id',
        'dividend_declaration_id',
        'gross_amount',
        'tax_withheld',
        'net_amount',
        'payment_date',
        'status',
        'payment_method',
        'payment_reference',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'tax_withheld' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'payment_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function investor(): BelongsTo
    {
        return $this->belongsTo(InvestorAccount::class, 'investor_account_id');
    }

    public function declaration(): BelongsTo
    {
        return $this->belongsTo(DividendDeclaration::class, 'dividend_declaration_id');
    }

    /**
     * Calculate tax withheld (15% standard rate in Zambia)
     */
    public static function calculateTaxWithheld(float $grossAmount, float $taxRate = 15.0): float
    {
        return round($grossAmount * ($taxRate / 100), 2);
    }

    /**
     * Calculate net amount after tax
     */
    public static function calculateNetAmount(float $grossAmount, float $taxWithheld): float
    {
        return round($grossAmount - $taxWithheld, 2);
    }
}
