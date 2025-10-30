<?php

namespace App\Infrastructure\Persistence\Eloquent\VentureBuilder;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VentureShareholderModel extends Model
{
    use SoftDeletes;

    protected $table = 'venture_shareholders';

    protected $fillable = [
        'venture_id',
        'user_id',
        'investment_id',
        'total_investment',
        'shares_owned',
        'equity_percentage',
        'certificate_number',
        'registration_date',
        'shareholder_agreement_path',
        'agreement_signed',
        'agreement_signed_at',
        'status',
        'total_dividends_received',
        'last_dividend_date',
    ];

    protected $casts = [
        'total_investment' => 'decimal:2',
        'shares_owned' => 'decimal:6',
        'equity_percentage' => 'decimal:4',
        'registration_date' => 'date',
        'agreement_signed' => 'boolean',
        'agreement_signed_at' => 'datetime',
        'total_dividends_received' => 'decimal:2',
        'last_dividend_date' => 'datetime',
    ];

    public function venture(): BelongsTo
    {
        return $this->belongsTo(VentureModel::class, 'venture_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function investment(): BelongsTo
    {
        return $this->belongsTo(VentureInvestmentModel::class, 'investment_id');
    }

    public function dividends(): HasMany
    {
        return $this->hasMany(VentureDividendModel::class, 'shareholder_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helpers
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasSignedAgreement(): bool
    {
        return $this->agreement_signed;
    }
}
