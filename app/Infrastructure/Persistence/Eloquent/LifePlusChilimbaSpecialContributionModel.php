<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifePlusChilimbaSpecialContributionModel extends Model
{
    protected $table = 'lifeplus_chilimba_special_contributions';

    protected $fillable = [
        'group_id',
        'type_id',
        'member_id',
        'recorded_by',
        'beneficiary_id',
        'beneficiary_name',
        'contribution_date',
        'amount',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'contribution_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(LifePlusChilimbaGroupModel::class, 'group_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(LifePlusChilimbaContributionTypeModel::class, 'type_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(LifePlusChilimbaMemberModel::class, 'member_id');
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(LifePlusChilimbaMemberModel::class, 'beneficiary_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
