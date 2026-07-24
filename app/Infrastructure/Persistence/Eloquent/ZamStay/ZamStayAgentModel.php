<?php

namespace App\Infrastructure\Persistence\Eloquent\ZamStay;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ZamStayAgentModel extends Model
{
    protected $table = 'zamstay_agents';

    protected $fillable = [
        'user_id',
        'business_name',
        'license_number',
        'phone',
        'commission_rate',
        'is_approved',
        'bio',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'is_approved' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(ZamStayBookingModel::class, 'agent_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }
}
