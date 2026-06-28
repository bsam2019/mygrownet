<?php

namespace App\Infrastructure\Persistence\Eloquent\Investor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorNotificationPreferenceModel extends Model
{
    protected $table = 'investor_notification_preferences';

    protected $fillable = [
        'investor_account_id',
        'email_announcements',
        'email_financial_reports',
        'email_dividends',
        'email_meetings',
        'email_messages',
        'email_urgent_only',
        'digest_frequency',
    ];

    protected $casts = [
        'email_announcements' => 'boolean',
        'email_financial_reports' => 'boolean',
        'email_dividends' => 'boolean',
        'email_meetings' => 'boolean',
        'email_messages' => 'boolean',
        'email_urgent_only' => 'boolean',
    ];

    public function investorAccount(): BelongsTo
    {
        return $this->belongsTo(InvestorAccountModel::class, 'investor_account_id');
    }
}
