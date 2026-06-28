<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class LgrSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("lgr_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value): void
    {
        $setting = self::where('key', $key)->first();
        
        if ($setting) {
            $setting->update(['value' => $value]);
        } else {
            self::create([
                'key' => $key,
                'value' => $value,
                'type' => 'string',
                'group' => 'general',
                'label' => $key,
            ]);
        }
        
        Cache::forget("lgr_setting_{$key}");
    }

    /**
     * Get all settings grouped by category
     */
    public static function getAllGrouped(): array
    {
        return self::all()->groupBy('group')->map(function ($settings) {
            return $settings->map(function ($setting) {
                return [
                    'key' => $setting->key,
                    'value' => self::castValue($setting->value, $setting->type),
                    'type' => $setting->type,
                    'label' => $setting->label,
                    'description' => $setting->description,
                ];
            });
        })->toArray();
    }

    /**
     * Cast value to appropriate type
     */
    private static function castValue($value, string $type)
    {
        return match ($type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'decimal' => (float) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        self::all()->each(function ($setting) {
            Cache::forget("lgr_setting_{$setting->key}");
        });
    }
}
