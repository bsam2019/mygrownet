<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LifePointSetting extends Model
{
    protected $fillable = [
        'activity_type',
        'name',
        'description',
        'lp_value',
        'is_active',
        'conditions',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'conditions' => 'array',
        'lp_value' => 'integer',
    ];

    /**
     * Get LP value for a specific activity type
     */
    public static function getLPValue(string $activityType): int
    {
        $setting = static::where('activity_type', $activityType)
            ->where('is_active', true)
            ->first();

        return $setting?->lp_value ?? 0;
    }

    /**
     * Get all active LP settings
     */
    public static function getActiveSettings(): array
    {
        return static::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->activity_type => $setting->lp_value];
            })
            ->toArray();
    }
}
