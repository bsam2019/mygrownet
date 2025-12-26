<?php

namespace App\Infrastructure\GrowBuilder\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class SiteUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'site_users';

    protected $fillable = [
        'site_id',
        'site_role_id',
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'status',
        'metadata',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'metadata' => 'array',
        'password' => 'hashed',
    ];

    // Relationships
    public function site(): BelongsTo
    {
        return $this->belongsTo(GrowBuilderSite::class, 'site_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(SiteRole::class, 'site_role_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(GrowBuilderOrder::class, 'site_user_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(SiteUserSession::class, 'site_user_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(SitePost::class, 'author_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(SiteComment::class, 'site_user_id');
    }

    // Scopes
    public function scopeForSite($query, $siteId)
    {
        return $query->where('site_id', $siteId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function hasPermission(string $permission): bool
    {
        if (!$this->role) {
            return false;
        }

        return $this->role->hasPermission($permission);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        if (!$this->role) {
            return false;
        }

        foreach ($permissions as $permission) {
            if ($this->role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function recordLogin(?string $ipAddress = null): void
    {
        $this->update([
            'last_login_at' => now(),
            'login_count' => $this->login_count + 1,
        ]);
    }
}
