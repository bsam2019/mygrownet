<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowBizAvailabilityScheduleModel extends Model
{
    protected $table = 'growbiz_availability_schedules';

    protected $fillable = [
        'user_id',
        'provider_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(GrowBizServiceProviderModel::class, 'provider_id');
    }

    public function getDayNameAttribute(): string
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return $days[$this->day_of_week] ?? '';
    }

    public function getFormattedHoursAttribute(): string
    {
        if (!$this->is_available) {
            return 'Closed';
        }
        return date('g:i A', strtotime($this->start_time)) . ' - ' . date('g:i A', strtotime($this->end_time));
    }
}
