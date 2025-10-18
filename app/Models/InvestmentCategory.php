<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestmentCategory extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'min_investment',
        'max_investment',
        'interest_rate',
        'expected_roi',
        'lock_in_period',
        'is_active'
    ];

    protected $casts = [
        'min_investment' => 'decimal:2',
        'max_investment' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'expected_roi' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class);
    }

    // Community project relationships
    public function communityProjects(): HasMany
    {
        return $this->hasMany(CommunityProject::class, 'category');
    }

    // Business Logic Methods
    public function isEligibleForCommunityProjects(): bool
    {
        return in_array($this->slug, [
            'real-estate',
            'agriculture', 
            'small-business',
            'technology',
            'infrastructure'
        ]);
    }

    public static function getCommunityProjectCategories(): array
    {
        return [
            'real-estate' => 'Real Estate Development',
            'agriculture' => 'Agricultural Projects',
            'small-business' => 'Small & Medium Enterprises',
            'technology' => 'Technology & Digital Projects',
            'infrastructure' => 'Community Infrastructure'
        ];
    }
}
