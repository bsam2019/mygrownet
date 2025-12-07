<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Module\Entities\Module;
use App\Domain\Module\Repositories\ModuleRepositoryInterface;
use App\Domain\Module\ValueObjects\ModuleId;
use Illuminate\Support\Facades\Config;

/**
 * Config-based Module Repository
 * 
 * Loads module definitions from config/modules/*.php files.
 * Suitable for applications where modules are defined in configuration.
 */
class ConfigBasedModuleRepository implements ModuleRepositoryInterface
{
    private array $modules = [];
    private bool $loaded = false;

    public function findById(string|ModuleId $id): ?Module
    {
        $this->loadModules();
        $idValue = $id instanceof ModuleId ? $id->value() : $id;
        return $this->modules[$idValue] ?? null;
    }

    public function findBySlug(string $slug): ?Module
    {
        $this->loadModules();
        foreach ($this->modules as $module) {
            if ($module->getSlug() === $slug) {
                return $module;
            }
        }
        return null;
    }

    public function findByCategory(string $category): array
    {
        $this->loadModules();
        return array_filter($this->modules, fn(Module $m) => $m->getCategory() === $category);
    }

    public function findByAccountType(string $accountType): array
    {
        $this->loadModules();
        return array_filter($this->modules, fn(Module $m) => in_array($accountType, $m->getAccountTypes()));
    }

    public function findAvailableForUser(int $userId, string $accountType): array
    {
        return $this->findByAccountType($accountType);
    }

    public function findAllActive(): array
    {
        $this->loadModules();
        return array_filter($this->modules, fn(Module $m) => $m->isActive());
    }

    public function findAll(): array
    {
        $this->loadModules();
        return array_values($this->modules);
    }

    public function save(Module $module): void
    {
        // Config-based repository doesn't support saving
        throw new \RuntimeException('Config-based repository does not support saving modules.');
    }

    public function delete(string $id): void
    {
        // Config-based repository doesn't support deletion
        throw new \RuntimeException('Config-based repository does not support deleting modules.');
    }

    private function loadModules(): void
    {
        if ($this->loaded) {
            return;
        }

        $modulesConfig = Config::get('modules', []);

        foreach ($modulesConfig as $moduleId => $config) {
            if (!is_array($config) || !isset($config['name'])) {
                continue;
            }

            $this->modules[$moduleId] = Module::fromConfig($moduleId, $config);
        }

        $this->loaded = true;
    }
}
