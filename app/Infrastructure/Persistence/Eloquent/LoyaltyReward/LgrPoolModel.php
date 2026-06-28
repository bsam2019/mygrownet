<?php

namespace App\Infrastructure\Persistence\Eloquent\LoyaltyReward;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LgrPoolModel extends Model
{
    protected $table = 'lgr_pools';

    protected $fillable = [
        'pool_date',
        'opening_balance',
        'contributions',
        'allocations',
        'closing_balance',
        'reserve_amount',
        'available_for_distribution',
        'contribution_sources',
    ];

    protected $casts = [
        'pool_date' => 'date',
        'opening_balance' => 'decimal:2',
        'contributions' => 'decimal:2',
        'allocations' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'reserve_amount' => 'decimal:2',
        'available_for_distribution' => 'decimal:2',
        'contribution_sources' => 'array',
    ];

    public function contributions(): HasMany
    {
        return $this->hasMany(LgrPoolContributionModel::class, 'lgr_pool_id');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(LgrPayoutModel::class, 'lgr_pool_id');
    }
}
