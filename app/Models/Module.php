<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Module extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_revenue_module',
        'is_active',
        'settings',
        'display_order',
    ];

    protected $casts = [
        'is_revenue_module' => 'boolean',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Get all active modules (cached)
     */
    public static function active(): array
    {
        return Cache::remember('modules.active', 3600, function () {
            return self::where('is_active', true)
                ->orderBy('display_order')
                ->get()
                ->keyBy('code')
                ->toArray();
        });
    }

    /**
     * Get all revenue-generating modules (cached)
     */
    public static function revenueModules(): array
    {
        return Cache::remember('modules.revenue', 3600, function () {
            return self::where('is_active', true)
                ->where('is_revenue_module', true)
                ->orderBy('display_order')
                ->get()
                ->keyBy('code')
                ->toArray();
        });
    }

    /**
     * Check if a module code exists
     */
    public static function exists(string $code): bool
    {
        $modules = self::active();
        return isset($modules[$code]);
    }

    /**
     * Get module by code
     */
    public static function findByCode(string $code): ?self
    {
        return self::where('code', $code)->first();
    }

    /**
     * Clear module cache
     */
    public static function clearCache(): void
    {
        Cache::forget('modules.active');
        Cache::forget('modules.revenue');
    }

    /**
     * Boot method to clear cache on changes
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }
}
