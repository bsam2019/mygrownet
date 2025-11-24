<?php

namespace App\Infrastructure\Persistence\Eloquent\Investor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorAnnouncementReadModel extends Model
{
    protected $table = 'investor_announcement_reads';

    protected $fillable = [
        'investor_announcement_id',
        'investor_account_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(InvestorAnnouncementModel::class, 'investor_announcement_id');
    }

    public function investorAccount(): BelongsTo
    {
        return $this->belongsTo(InvestorAccountModel::class, 'investor_account_id');
    }
}