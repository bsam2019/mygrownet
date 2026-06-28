<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Shareholder Directory Profile (Opt-in)
 * 
 * Shareholders can choose to be listed in the directory
 * to network with other shareholders.
 */
class ShareholderDirectoryProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'investor_account_id',
        'is_listed',
        'display_name',
        'industry',
        'location',
        'bio',
        'show_investment_date',
        'allow_contact',
    ];

    protected $casts = [
        'is_listed' => 'boolean',
        'show_investment_date' => 'boolean',
        'allow_contact' => 'boolean',
    ];

    public function investorAccount(): BelongsTo
    {
        return $this->belongsTo(InvestorAccount::class);
    }

    public function scopeListed($query)
    {
        return $query->where('is_listed', true);
    }

    public function scopeContactable($query)
    {
        return $query->where('allow_contact', true);
    }

    public function isVisible(): bool
    {
        return $this->is_listed;
    }

    public function canBeContacted(): bool
    {
        return $this->is_listed && $this->allow_contact;
    }
}
