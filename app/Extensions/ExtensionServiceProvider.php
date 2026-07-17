<?php

namespace App\Extensions;

use Illuminate\Support\ServiceProvider;

abstract class ExtensionServiceProvider extends ServiceProvider
{
    /**
     * Unique extension code (e.g., 'pharmacy', 'supermarket').
     */
    abstract public function getCode(): string;

    /**
     * Human-readable extension name.
     */
    abstract public function getName(): string;

    /**
     * Extension description.
     */
    abstract public function getDescription(): ?string;

    /**
     * Extension version.
     */
    abstract public function getVersion(): ?string;

    /**
     * Feature keys this extension provides (e.g., ['controlled-medicines', 'prescriptions']).
     * These are used with the stockflow.feature middleware.
     */
    public function getFeatures(): array
    {
        return [];
    }

    /**
     * Default extension settings.
     */
    public function getDefaultSettings(): array
    {
        return [];
    }

    /**
     * Get the base path for this extension.
     */
    protected function getBasePath(): string
    {
        $reflector = new \ReflectionClass(static::class);
        return dirname($reflector->getFileName());
    }

    /**
     * Boot extension migrations.
     */
    protected function loadExtensionMigrations(): void
    {
        $path = $this->getBasePath() . '/database/migrations';
        if (is_dir($path)) {
            $this->loadMigrationsFrom($path);
        }
    }

    /**
     * Boot extension routes.
     */
    protected function loadExtensionRoutes(): void
    {
        $path = $this->getBasePath() . '/routes';
        if (is_dir($path)) {
            foreach (glob($path . '/*.php') as $routeFile) {
                $this->loadRoutesFrom($routeFile);
            }
        }
    }

    /**
     * Register extension's repository bindings.
     */
    protected function registerBindings(array $bindings): void
    {
        foreach ($bindings as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

    /**
     * Register extension's singleton services.
     */
    protected function registerServices(array $services): void
    {
        foreach ($services as $service) {
            $this->app->singleton($service);
        }
    }
}
