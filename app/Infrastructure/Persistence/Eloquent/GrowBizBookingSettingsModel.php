<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowBizBookingSettingsModel extends Model
{
    protected $table = 'growbiz_booking_settings';

    protected $fillable = [
        'user_id',
        'business_name',
        'booking_page_description',
        'booking_page_slug',
        'timezone',
        'slot_duration_minutes',
        'min_booking_notice_hours',
        'max_booking_advance_days',
        'require_approval',
        'allow_cancellation',
        'cancellation_notice_hours',
        'send_confirmation_email',
        'send_reminder_sms',
        'send_reminder_whatsapp',
        'reminder_timings',
        'collect_payment_online',
        'deposit_percentage',
        'cancellation_policy',
        'custom_fields',
        'logo',
        'primary_color',
    ];

    protected $casts = [
        'require_approval' => 'boolean',
        'allow_cancellation' => 'boolean',
        'send_confirmation_email' => 'boolean',
        'send_reminder_sms' => 'boolean',
        'send_reminder_whatsapp' => 'boolean',
        'collect_payment_online' => 'boolean',
        'deposit_percentage' => 'decimal:2',
        'reminder_timings' => 'array',
        'custom_fields' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function getBookingUrlAttribute(): string
    {
        if ($this->booking_page_slug) {
            return url("/book/{$this->booking_page_slug}");
        }
        return url("/book/{$this->user_id}");
    }
}
