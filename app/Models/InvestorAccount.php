<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestorAccount extends Model
{
    use HasFactory;

    protected $table = 'investor_accounts';

    protected $fillable = [
        'user_id',
        'investment_round_id',
        'name',
        'email',
        'phone',
        'investment_amount',
        'investment_date',
        'equity_percentage',
        'status',
        'notes',
    ];

    protected $casts = [
        'investment_amount' => 'decimal:2',
        'equity_percentage' => 'decimal:4',
        'investment_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function investmentRound(): BelongsTo
    {
        return $this->belongsTo(\App\Models\InvestmentRound::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(\App\Models\InvestorMessage::class);
    }

    public function dividends(): HasMany
    {
        return $this->hasMany(InvestorDividend::class);
    }

    public function shareCertificates(): HasMany
    {
        return $this->hasMany(InvestorShareCertificate::class);
    }
}
