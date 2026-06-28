<?php

namespace App\Infrastructure\Persistence\Eloquent\ProfitSharing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class QuarterlyProfitShareModel extends Model
{
    protected $table = 'quarterly_profit_shares';

    protected $fillable = [
        'year',
        'quarter',
        'total_project_profit',
        'member_share_amount',
        'company_retained',
        'total_active_members',
        'total_bp_pool',
        'distribution_method',
        'status',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
        'distributed_at',
    ];

    protected $casts = [
        'total_project_profit' => 'decimal:2',
        'member_share_amount' => 'decimal:2',
        'company_retained' => 'decimal:2',
        'total_bp_pool' => 'decimal:2',
        'approved_at' => 'datetime',
        'distributed_at' => 'datetime',
    ];

    public function memberShares(): HasMany
    {
        return $this->hasMany(MemberProfitShareModel::class, 'quarterly_profit_share_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
