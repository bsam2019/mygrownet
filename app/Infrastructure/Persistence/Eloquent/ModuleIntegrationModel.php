<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tracks which modules have sub-modules (POS, Inventory) enabled
 */
class ModuleIntegrationModel extends Model
{
    protected $table = 'module_integrations';

    protected $fillable = [
        'user_id',
        'parent_module',
        'integrated_module',
        'is_enabled',
        'settings',
        'enabled_at',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'settings' => 'array',
        'enabled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeForParent($query, string $parentModule)
    {
        return $query->where('parent_module', $parentModule);
    }

    public function scopeForIntegration($query, string $integratedModule)
    {
        return $query->where('integrated_module', $integratedModule);
    }

    /**
     * Check if a user has a specific integration enabled
     */
    public static function isEnabled(int $userId, string $parentModule, string $integratedModule): bool
    {
        return static::where('user_id', $userId)
            ->where('parent_module', $parentModule)
            ->where('integrated_module', $integratedModule)
            ->where('is_enabled', true)
            ->exists();
    }

    /**
     * Enable an integration for a user
     */
    public static function enable(int $userId, string $parentModule, string $integratedModule, array $settings = []): self
    {
        return static::updateOrCreate(
            [
                'user_id' => $userId,
                'parent_module' => $parentModule,
                'integrated_module' => $integratedModule,
            ],
            [
                'is_enabled' => true,
                'settings' => $settings,
                'enabled_at' => now(),
            ]
        );
    }

    /**
     * Disable an integration for a user
     */
    public static function disable(int $userId, string $parentModule, string $integratedModule): bool
    {
        return static::where('user_id', $userId)
            ->where('parent_module', $parentModule)
            ->where('integrated_module', $integratedModule)
            ->update(['is_enabled' => false]) > 0;
    }
}
