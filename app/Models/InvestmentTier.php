<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'minimum_investment',
        'maximum_investment',
        'fixed_profit_rate',
        'direct_referral_rate',
        'level2_referral_rate',
        'level3_referral_rate',
        'benefits',
        'description',
        'sort_order',
        'order',
        'is_active'
    ];

    protected $casts = [
        'minimum_investment' => 'decimal:2',
        'maximum_investment' => 'decimal:2',
        'benefits' => 'array',
        'is_active' => 'boolean'
    ];

    public function tierSettings()
    {
        return $this->hasOne(TierSetting::class, 'investment_tier_id');
    }

    public function tierQualifications()
    {
        return $this->hasMany(TierQualification::class, 'tier_id');
    }
}
