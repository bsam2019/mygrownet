<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Models\Application;
use App\Domain\Core\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ApplicationRegistry
{
    private const CACHE_KEY_ALL = 'platform:applications:all';
    private const CACHE_TTL = 3600;

    public function all(): Collection
    {
        return Cache::remember(self::CACHE_KEY_ALL, self::CACHE_TTL, function () {
            return Application::where('is_active', true)->get();
        });
    }

    public function findBySlug(string $slug): ?Application
    {
        return $this->all()->firstWhere('slug', $slug);
    }

    public function getByType(string $type): Collection
    {
        return $this->all()->where('type', $type);
    }

    public function getForOrganization(Organization $org): Collection
    {
        return $org->applications()->where('is_active', true)->get();
    }

    public function getForUser(User $user): Collection
    {
        return Application::whereHas('users', fn($q) => $q->where('user_id', $user->id))
            ->where('is_active', true)
            ->get();
    }

    public function isActive(string $slug): bool
    {
        return $this->findBySlug($slug) !== null;
    }

    public function isAvailableForOrganization(Application $app, Organization $org): bool
    {
        return $org->applications()
            ->where('application_id', $app->id)
            ->wherePivot('status', 'active')
            ->exists();
    }

    public function getRoutingConfig(string $slug): ?array
    {
        return config("platform.applications.{$slug}");
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_ALL);
    }
}
