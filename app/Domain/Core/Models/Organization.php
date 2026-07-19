<?php

namespace App\Domain\Core\Models;

use App\Domain\Core\Events\OrganizationArchived;
use App\Domain\Core\Events\OrganizationCreated;
use App\Models\User;
use App\Domain\Core\Models\ApplicationInstallation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::created(fn(Organization $org) => OrganizationCreated::dispatch($org));
        static::updated(function (Organization $org) {
            if ($org->wasChanged('status') && $org->status === 'archived') {
                OrganizationArchived::dispatch($org);
            }
        });
    }

    protected $fillable = [
        'uuid', 'name', 'slug', 'type', 'status', 'owner_id', 'settings',
        'country', 'currency', 'timezone', 'language',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function branches(): HasMany
    {
        return $this->hasMany(OrganizationBranch::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(OrganizationMember::class);
    }

    public function applications(): BelongsToMany
    {
        return $this->belongsToMany(Application::class, 'organization_applications');
    }

    public function installations(): HasMany
    {
        return $this->hasMany(ApplicationInstallation::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(OrganizationInvitation::class);
    }
}
