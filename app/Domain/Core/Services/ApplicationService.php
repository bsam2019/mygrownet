<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Models\Application;
use App\Domain\Core\Models\Organization;

class ApplicationService
{
    public function __construct(
        private ApplicationRegistry $registry,
    ) {}

    public function create(array $data): Application
    {
        return Application::create($data);
    }

    public function update(Application $app, array $data): Application
    {
        $app->update($data);
        return $app->fresh();
    }

    public function enable(Application $app): void
    {
        $app->update(['is_active' => true]);
        $this->registry->clearCache();
    }

    public function disable(Application $app): void
    {
        $app->update(['is_active' => false]);
        $this->registry->clearCache();
    }

    public function installForOrganization(Organization $org, Application $app): void
    {
        $org->applications()->attach($app->id, ['status' => 'active']);
    }

    public function uninstallForOrganization(Organization $org, Application $app): void
    {
        $org->applications()->updateExistingPivot($app->id, ['status' => 'inactive']);
    }

    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->registry->all();
    }

    public function findBySlug(string $slug): ?Application
    {
        return $this->registry->findBySlug($slug);
    }
}
