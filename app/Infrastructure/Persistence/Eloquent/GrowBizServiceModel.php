<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GrowBizServiceModel extends Model
{
    use SoftDeletes;

    protected $table = 'growbiz_services';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'duration_minutes',
        'buffer_minutes',
        'price',
        'currency',
        'category',
        'color',
        'is_active',
        'allow_online_booking',
        'max_bookings_per_slot',
        'required_resources',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'allow_online_booking' => 'boolean',
        'required_resources' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(GrowBizAppointmentModel::class, 'service_id');
    }

    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(
            GrowBizServiceProviderModel::class,
            'growbiz_service_provider_services',
            'service_id',
            'provider_id'
        )->withPivot(['custom_price', 'custom_duration'])->withTimestamps();
    }

    public function getFormattedPriceAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->price, 2);
    }

    public function getFormattedDurationAttribute(): string
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        }
        return "{$minutes}m";
    }

    public function getTotalDurationAttribute(): int
    {
        return $this->duration_minutes + $this->buffer_minutes;
    }
}
