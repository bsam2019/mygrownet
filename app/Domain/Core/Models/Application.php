<?php

namespace App\Domain\Core\Models;

use App\Domain\Core\Services\ApplicationRegistry;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Application extends Model
{
    protected static function booted(): void
    {
        static::saved(fn() => app(ApplicationRegistry::class)->clearCache());
        static::deleted(fn() => app(ApplicationRegistry::class)->clearCache());
    }
    protected $fillable = [
        'name', 'slug', 'type', 'url', 'config', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_active' => 'boolean',
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
}
