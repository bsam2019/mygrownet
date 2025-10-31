<?php

namespace App\Infrastructure\Persistence\Eloquent\LoyaltyReward;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LgrPoolContributionModel extends Model
{
    protected $table = 'lgr_pool_contributions';

    protected $fillable = [
        'lgr_pool_id',
        'source_type',
        'source_reference',
        'amount',
        'percentage',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
    ];

    public function pool(): BelongsTo
    {
        return $this->belongsTo(LgrPoolModel::class, 'lgr_pool_id');
    }
}
