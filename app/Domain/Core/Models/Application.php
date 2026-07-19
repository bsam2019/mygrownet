<?php

namespace App\Domain\Core\Models;

use App\Domain\Core\Services\ApplicationRegistry;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    protected static function booted(): void
    {
        static::saved(fn() => app(ApplicationRegistry::class)->clearCache());
        static::deleted(fn() => app(ApplicationRegistry::class)->clearCache());
    }
    protected $fillable = [
        'name', 'slug', 'type', 'url', 'config', 'is_active',
        'category', 'access_model', 'context_support',
        'requires_organization_context', 'subscription_required',
        'lifecycle', 'operational_status', 'replacement_app_id',
        'migration_deadline', 'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_active' => 'boolean',
            'requires_organization_context' => 'boolean',
            'subscription_required' => 'boolean',
            'is_visible' => 'boolean',
            'migration_deadline' => 'date',
        ];
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_applications');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_applications');
    }

    public function installations(): HasMany
    {
        return $this->hasMany(ApplicationInstallation::class);
    }

    public function userSubscriptions(): HasMany
    {
        return $this->hasMany(UserApplicationSubscription::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(ApplicationRole::class);
    }

    public function replacement(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replacement_app_id');
    }
}
