<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class GrowFinanceApiTokenModel extends Model
{
    protected $table = 'growfinance_api_tokens';

    protected $fillable = [
        'business_id',
        'name',
        'token',
        'abilities',
        'last_used_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'abilities' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'token',
    ];

    // Available abilities
    public const ABILITY_READ = 'read';
    public const ABILITY_WRITE = 'write';
    public const ABILITY_DELETE = 'delete';

    public const ABILITIES = [
        self::ABILITY_READ => 'Read data (invoices, expenses, customers, reports)',
        self::ABILITY_WRITE => 'Create and update records',
        self::ABILITY_DELETE => 'Delete records',
    ];

    // Resource-specific abilities
    public const RESOURCE_ABILITIES = [
        'invoices:read', 'invoices:write', 'invoices:delete',
        'expenses:read', 'expenses:write', 'expenses:delete',
        'customers:read', 'customers:write', 'customers:delete',
        'vendors:read', 'vendors:write', 'vendors:delete',
        'reports:read',
        'accounts:read', 'accounts:write',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($token) {
            if (empty($token->token)) {
                $token->token = Str::random(64);
            }
        });
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(User::class, 'business_id');
    }

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function hasAbility(string $ability): bool
    {
        $abilities = $this->abilities ?? [];

        // Check for wildcard
        if (in_array('*', $abilities)) {
            return true;
        }

        // Check for general ability (read, write, delete)
        if (in_array($ability, $abilities)) {
            return true;
        }

        // Check for resource-specific ability
        $parts = explode(':', $ability);
        if (count($parts) === 2) {
            // Check if general ability covers this
            if (in_array($parts[1], $abilities)) {
                return true;
            }
        }

        return false;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    public function getMaskedToken(): string
    {
        return substr($this->token, 0, 8) . '...' . substr($this->token, -4);
    }
}
