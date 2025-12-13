<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\GrowBiz\ValueObjects\AppointmentStatus;
use App\Domain\GrowBiz\ValueObjects\BookingSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrowBizAppointmentModel extends Model
{
    use SoftDeletes;

    protected $table = 'growbiz_appointments';

    protected $fillable = [
        'user_id',
        'service_id',
        'provider_id',
        'customer_id',
        'booking_reference',
        'appointment_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'status',
        'price',
        'currency',
        'payment_status',
        'amount_paid',
        'customer_notes',
        'internal_notes',
        'booking_source',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by',
        'confirmed_at',
        'completed_at',
        'invoice_id',
        'reminder_sent',
        'follow_up_sent',
        'metadata',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'status' => AppointmentStatus::class,
        'booking_source' => BookingSource::class,
        'price' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'reminder_sent' => 'boolean',
        'follow_up_sent' => 'boolean',
        'cancelled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->booking_reference)) {
                $model->booking_reference = self::generateBookingReference();
            }
        });
    }

    public static function generateBookingReference(): string
    {
        $prefix = 'APT';
        $timestamp = now()->format('ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return "{$prefix}{$timestamp}{$random}";
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(GrowBizServiceModel::class, 'service_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(GrowBizServiceProviderModel::class, 'provider_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(GrowBizBookingCustomerModel::class, 'customer_id');
    }

    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'cancelled_by');
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(GrowBizAppointmentReminderModel::class, 'appointment_id');
    }

    public function getFormattedPriceAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->price, 2);
    }

    public function getFormattedTimeAttribute(): string
    {
        return date('g:i A', strtotime($this->start_time)) . ' - ' . date('g:i A', strtotime($this->end_time));
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->appointment_date->format('D, M j, Y');
    }

    public function getIsUpcomingAttribute(): bool
    {
        $appointmentDateTime = $this->appointment_date->format('Y-m-d') . ' ' . $this->start_time;
        return now()->lt($appointmentDateTime) && $this->status->isActive();
    }

    public function getIsTodayAttribute(): bool
    {
        return $this->appointment_date->isToday();
    }

    public function getBalanceDueAttribute(): float
    {
        return max(0, $this->price - $this->amount_paid);
    }
}
