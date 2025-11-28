<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorExitOpportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'investment_round_id',
        'opportunity_type',
        'status',
        'estimated_valuation',
        'estimated_share_price',
        'target_date',
        'description',
        'terms',
        'is_public',
    ];

    protected $casts = [
        'estimated_valuation' => 'decimal:2',
        'estimated_share_price' => 'decimal:2',
        'target_date' => 'date',
        'terms' => 'array',
        'is_public' => 'boolean',
    ];

    public function investmentRound(): BelongsTo
    {
        return $this->belongsTo(InvestmentRound::class);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getOpportunityTypeLabelAttribute(): string
    {
        return match ($this->opportunity_type) {
            'ipo' => 'Initial Public Offering (IPO)',
            'acquisition' => 'Acquisition',
            'secondary_sale' => 'Secondary Sale',
            'buyback' => 'Share Buyback',
            default => ucfirst(str_replace('_', ' ', $this->opportunity_type)),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'potential' => 'Potential',
            'active' => 'Active',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }
}
