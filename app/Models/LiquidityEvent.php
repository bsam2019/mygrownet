<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Company-Initiated Liquidity Events
 * 
 * For private limited companies: buybacks, acquisitions, mergers, etc.
 * These are company-initiated, not shareholder-initiated.
 */
class LiquidityEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'event_type',
        'status',
        'announcement_date',
        'registration_deadline',
        'expected_completion',
        'actual_completion',
        'price_per_share',
        'total_budget',
        'shares_available',
        'eligibility_criteria',
        'documents',
        'terms_conditions',
        'board_resolution_reference',
    ];

    protected $casts = [
        'announcement_date' => 'date',
        'registration_deadline' => 'date',
        'expected_completion' => 'date',
        'actual_completion' => 'date',
        'price_per_share' => 'decimal:2',
        'total_budget' => 'decimal:2',
        'shares_available' => 'decimal:4',
        'eligibility_criteria' => 'array',
        'documents' => 'array',
    ];

    public function participations(): HasMany
    {
        return $this->hasMany(LiquidityEventParticipation::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['announced', 'open']);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isRegistrationOpen(): bool
    {
        return $this->status === 'open' 
            && (!$this->registration_deadline || $this->registration_deadline->isFuture());
    }

    public function getEventTypeLabel(): string
    {
        return match($this->event_type) {
            'buyback' => 'Share Buyback',
            'acquisition' => 'Acquisition',
            'merger' => 'Merger',
            'special_dividend' => 'Special Dividend',
            'rights_issue' => 'Rights Issue',
            'other' => 'Other',
            default => ucfirst($this->event_type),
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'announced' => 'Announced',
            'open' => 'Open for Registration',
            'closed' => 'Registration Closed',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'announced' => 'blue',
            'open' => 'green',
            'closed' => 'yellow',
            'completed' => 'gray',
            'cancelled' => 'red',
            default => 'gray',
        };
    }
}
