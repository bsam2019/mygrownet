<?php

namespace App\Infrastructure\Persistence\Eloquent\GrowFinance;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowFinanceTeamMemberModel extends Model
{
    protected $table = 'growfinance_team_members';

    protected $fillable = [
        'business_id',
        'user_id',
        'role',
        'permissions',
        'status',
        'invitation_token',
        'invited_at',
        'accepted_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'invited_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public const ROLE_OWNER = 'owner';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_ACCOUNTANT = 'accountant';
    public const ROLE_VIEWER = 'viewer';

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';

    public const ROLE_PERMISSIONS = [
        self::ROLE_OWNER => ['*'],
        self::ROLE_ADMIN => [
            'invoices.*', 'expenses.*', 'customers.*', 'vendors.*',
            'reports.*', 'banking.*', 'budgets.*', 'recurring.*',
            'team.view', 'settings.view',
        ],
        self::ROLE_ACCOUNTANT => [
            'invoices.*', 'expenses.*', 'customers.view', 'vendors.view',
            'reports.*', 'banking.*', 'budgets.*', 'recurring.*',
        ],
        self::ROLE_VIEWER => [
            'invoices.view', 'expenses.view', 'customers.view', 'vendors.view',
            'reports.view', 'banking.view', 'budgets.view',
        ],
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(User::class, 'business_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? self::ROLE_PERMISSIONS[$this->role] ?? [];

        if (in_array('*', $permissions)) {
            return true;
        }

        if (in_array($permission, $permissions)) {
            return true;
        }

        $parts = explode('.', $permission);
        if (count($parts) === 2) {
            $wildcard = $parts[0] . '.*';
            if (in_array($wildcard, $permissions)) {
                return true;
            }
        }

        return false;
    }

    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_OWNER, self::ROLE_ADMIN]);
    }
}
