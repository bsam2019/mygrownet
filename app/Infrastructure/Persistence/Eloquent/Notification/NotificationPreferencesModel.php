<?php

namespace App\Infrastructure\Persistence\Eloquent\Notification;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class NotificationPreferencesModel extends Model
{
    protected $table = 'notification_preferences';

    protected $fillable = [
        'user_id',
        'email_enabled',
        'sms_enabled',
        'push_enabled',
        'in_app_enabled',
        'notify_wallet',
        'notify_commissions',
        'notify_withdrawals',
        'notify_subscriptions',
        'notify_referrals',
        'notify_workshops',
        'notify_ventures',
        'notify_bgf',
        'notify_points',
        'notify_security',
        'notify_marketing',
        'digest_frequency',
        'quiet_hours_start',
        'quiet_hours_end',
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'sms_enabled' => 'boolean',
        'push_enabled' => 'boolean',
        'in_app_enabled' => 'boolean',
        'notify_wallet' => 'boolean',
        'notify_commissions' => 'boolean',
        'notify_withdrawals' => 'boolean',
        'notify_subscriptions' => 'boolean',
        'notify_referrals' => 'boolean',
        'notify_workshops' => 'boolean',
        'notify_ventures' => 'boolean',
        'notify_bgf' => 'boolean',
        'notify_points' => 'boolean',
        'notify_security' => 'boolean',
        'notify_marketing' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
