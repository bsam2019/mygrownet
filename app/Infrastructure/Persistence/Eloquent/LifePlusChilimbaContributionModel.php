<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LifePlusChilimbaContributionModel extends Model
{
    use SoftDeletes;

    protected $table = 'lifeplus_chilimba_contributions';

    protected $fillable = [
        'group_id',
        'member_id',
        'recorded_by',
        'contribution_date',
        'amount',
        'payment_method',
        'receipt_number',
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

    public function member(): BelongsTo
    {
        return $this->belongsTo(LifePlusChilimbaMemberModel::class, 'member_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
