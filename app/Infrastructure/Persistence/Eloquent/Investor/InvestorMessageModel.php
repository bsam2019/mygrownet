<?php

namespace App\Infrastructure\Persistence\Eloquent\Investor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorMessageModel extends Model
{
    protected $table = 'investor_messages';

    protected $fillable = [
        'investor_account_id',
        'admin_id',
        'subject',
        'content',
        'direction',
        'status',
        'read_at',
        'parent_id',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Check if message is read (status = 'read')
     */
    public function getIsReadAttribute(): bool
    {
        return $this->status === 'read';
    }

    public function investorAccount(): BelongsTo
    {
        return $this->belongsTo(InvestorAccountModel::class, 'investor_account_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'admin_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function scopeForInvestor($query, int $investorAccountId)
    {
        return $query->where('investor_account_id', $investorAccountId);
    }

    public function scopeUnread($query)
    {
        return $query->where('status', '!=', 'read');
    }

    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    public function scopeOutbound($query)
    {
        return $query->where('direction', 'outbound');
    }
}
