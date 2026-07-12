<?php

namespace App\Infrastructure\Persistence\Eloquent\Wedding;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class WeddingEventModel extends Model
{
    use HasFactory;

    protected $table = 'wedding_events';

    protected $fillable = [
        'user_id',
        'slug',
        'partner_name',
        'partner_email',
        'partner_phone',
        'wedding_date',
        'venue_name',
        'venue_location',
        'budget',
        'guest_count',
        'status',
        'notes',
        'preferences',
        'access_code',
    ];

    protected $casts = [
        'wedding_date' => 'date',
        'budget' => 'decimal:2',
        'guest_count' => 'integer',
        'preferences' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(WeddingBookingModel::class);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['planning', 'confirmed']);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('wedding_date', '>', now())
                    ->whereIn('status', ['planning', 'confirmed']);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}