<?php

namespace App\Models\QuickInvoice;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminSetting extends Model
{
    protected $table = 'quick_invoice_admin_settings';
    
    public $timestamps = false;

    protected $fillable = [
        'setting_key',
        'setting_value',
        'updated_by',
        'updated_at',
    ];

    protected $casts = [
        'setting_value' => 'array',
        'updated_at' => 'datetime',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get a setting value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = self::where('setting_key', $key)->first();
        return $setting ? $setting->setting_value : $default;
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, mixed $value, ?int $updatedBy = null): void
    {
        self::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $value,
                'updated_by' => $updatedBy ?? auth()->id(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Check if usage limits are enabled
     */
    public static function isUsageLimitsEnabled(): bool
    {
        return self::get('usage_limits_enabled', false);
    }

    /**
     * Enable/disable usage limits
     */
    public static function setUsageLimitsEnabled(bool $enabled, ?int $updatedBy = null): void
    {
        self::set('usage_limits_enabled', $enabled, $updatedBy);
    }

    /**
     * Get monetization settings
     */
    public static function getMonetizationSettings(): array
    {
        return self::get('monetization_settings', [
            'usage_limits_enabled' => false,
            'free_tier_limit' => 5,
            'require_subscription' => false,
            'grace_period_days' => 7,
        ]);
    }

    /**
     * Update monetization settings
     */
    public static function updateMonetizationSettings(array $settings, ?int $updatedBy = null): void
    {
        $current = self::getMonetizationSettings();
        $updated = array_merge($current, $settings);
        self::set('monetization_settings', $updated, $updatedBy);
    }
}