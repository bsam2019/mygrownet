<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LifePlusChilimbaMemberModel extends Model
{
    protected $table = 'lifeplus_chilimba_members';

    protected $fillable = [
        'group_id',
        'user_id',
        'name',
        'phone',
        'position_in_queue',
        'has_received_payout',
        'payout_date',
        'is_active',
    ];

    protected $casts = [
        'payout_date' => 'date',
        'has_received_payout' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(LifePlusChilimbaGroupModel::class, 'group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(LifePlusChilimbaContributionModel::class, 'member_id');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(LifePlusChilimbaPayoutModel::class, 'member_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(LifePlusChilimbaLoanModel::class, 'member_id');
    }
}
