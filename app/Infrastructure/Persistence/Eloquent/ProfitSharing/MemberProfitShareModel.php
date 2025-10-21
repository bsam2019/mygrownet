<?php

namespace App\Infrastructure\Persistence\Eloquent\ProfitSharing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class MemberProfitShareModel extends Model
{
    protected $table = 'member_profit_shares';

    protected $fillable = [
        'quarterly_profit_share_id',
        'user_id',
        'professional_level',
        'level_multiplier',
        'member_bp',
        'share_amount',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'level_multiplier' => 'decimal:2',
        'member_bp' => 'decimal:2',
        'share_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function quarterlyProfitShare(): BelongsTo
    {
        return $this->belongsTo(QuarterlyProfitShareModel::class, 'quarterly_profit_share_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
