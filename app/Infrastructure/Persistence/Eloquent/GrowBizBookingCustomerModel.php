<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrowBizBookingCustomerModel extends Model
{
    use SoftDeletes;

    protected $table = 'growbiz_booking_customers';

    protected $fillable = [
        'user_id',
        'registered_user_id',
        'name',
        'email',
        'phone',
        'notes',
        'preferences',
        'total_bookings',
        'no_shows',
        'cancellations',
        'last_visit_at',
    ];

    protected $casts = [
        'preferences' => 'array',
        'last_visit_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function registeredUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'registered_user_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(GrowBizAppointmentModel::class, 'customer_id');
    }

    public function getInitialsAttribute(): string
    {
        $parts = explode(' ', $this->name);
        if (count($parts) >= 2) {
            return strtoupper($parts[0][0] . $parts[1][0]);
        }
        return strtoupper(substr($this->name, 0, 2));
    }

    public function getReliabilityScoreAttribute(): int
    {
        if ($this->total_bookings === 0) return 100;
        $issues = $this->no_shows + $this->cancellations;
        return max(0, 100 - (int)(($issues / $this->total_bookings) * 100));
    }

    public function incrementBookings(): void
    {
        $this->increment('total_bookings');
        $this->update(['last_visit_at' => now()]);
    }

    public function incrementNoShows(): void
    {
        $this->increment('no_shows');
    }

    public function incrementCancellations(): void
    {
        $this->increment('cancellations');
    }
}
