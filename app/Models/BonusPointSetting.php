<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonusPointSetting extends Model
{
    protected $fillable = [
        'activity_type',
        'name',
        'description',
        'bp_value',
        'is_active',
        'conditions',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'conditions' => 'array',
        'bp_value' => 'integer',
    ];

    /**
     * Get BP value for a specific activity type
     */
    public static function getBPValue(string $activityType): int
    {
        $setting = static::where('activity_type', $activityType)
            ->where('is_active', true)
            ->first();

        return $setting?->bp_value ?? 0;
    }

    /**
     * Get all active BP settings
     */
    public static function getActiveSettings(): array
    {
        return static::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->activity_type => $setting->bp_value];
            })
            ->toArray();
    }
}
