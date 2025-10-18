<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceFingerprint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fingerprint_hash',
        'user_agent',
        'ip_address',
        'device_info',
        'browser_info',
        'is_trusted',
        'first_seen_at',
        'last_seen_at',
    ];

    protected $casts = [
        'device_info' => 'array',
        'browser_info' => 'array',
        'is_trusted' => 'boolean',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a device fingerprint hash from device information
     */
    public static function generateFingerprint(array $deviceInfo, array $browserInfo, string $userAgent): string
    {
        $data = [
            'screen' => $deviceInfo['screen'] ?? '',
            'timezone' => $deviceInfo['timezone'] ?? '',
            'language' => $deviceInfo['language'] ?? '',
            'platform' => $deviceInfo['platform'] ?? '',
            'browser' => $browserInfo['name'] ?? '',
            'version' => $browserInfo['version'] ?? '',
            'user_agent' => $userAgent,
        ];

        return hash('sha256', json_encode($data));
    }

    /**
     * Check if this device has been seen recently
     */
    public function isRecentlyActive(): bool
    {
        return $this->last_seen_at->diffInHours(now()) < 24;
    }

    /**
     * Update the last seen timestamp
     */
    public function updateLastSeen(): void
    {
        $this->update(['last_seen_at' => now()]);
    }
}