<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GrowBizServiceProviderModel extends Model
{
    use SoftDeletes;

    protected $table = 'growbiz_service_providers';

    protected $fillable = [
        'user_id',
        'employee_id',
        'name',
        'email',
        'phone',
        'bio',
        'avatar',
        'color',
        'is_active',
        'accept_online_bookings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'accept_online_bookings' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(GrowBizEmployeeModel::class, 'employee_id');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(
            GrowBizServiceModel::class,
            'growbiz_service_provider_services',
            'provider_id',
            'service_id'
        )->withPivot(['custom_price', 'custom_duration'])->withTimestamps();
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(GrowBizAppointmentModel::class, 'provider_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(GrowBizAvailabilityScheduleModel::class, 'provider_id');
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(GrowBizAvailabilityExceptionModel::class, 'provider_id');
    }

    public function getInitialsAttribute(): string
    {
        $parts = explode(' ', $this->name);
        if (count($parts) >= 2) {
            return strtoupper($parts[0][0] . $parts[1][0]);
        }
        return strtoupper(substr($this->name, 0, 2));
    }
}
