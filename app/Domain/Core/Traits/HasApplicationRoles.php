<?php

namespace App\Domain\Core\Traits;

use App\Domain\Core\Models\Application;

trait HasApplicationRoles
{
    public function hasApplicationRole(string $appSlug, string $roleName): bool
    {
        $app = Application::where('slug', $appSlug)->first();
        if (!$app) {
            return false;
        }

        return $this->roles()
            ->where('application_id', $app->id)
            ->where('name', $roleName)
            ->exists();
    }

    public function hasApplicationPermission(string $appSlug, string $permission): bool
    {
        $app = Application::where('slug', $appSlug)->first();
        if (!$app) {
            return false;
        }

        return $this->roles()
            ->where('application_id', $app->id)
            ->whereHas('permissions', fn($q) => $q->where('name', $permission))
            ->exists();
    }

    public function getApplicationRoles(string $appSlug): array
    {
        $app = Application::where('slug', $appSlug)->first();
        if (!$app) {
            return [];
        }

        return $this->roles()
            ->where('application_id', $app->id)
            ->pluck('name')
            ->toArray();
    }

    public function getAllApplicationRoles(): array
    {
        $apps = Application::pluck('slug', 'id');

        return $this->roles()
            ->whereNotNull('application_id')
            ->get()
            ->groupBy(fn($role) => $apps->get($role->application_id, 'unknown'))
            ->map(fn($roles) => $roles->pluck('name')->toArray())
            ->toArray();
    }
}
