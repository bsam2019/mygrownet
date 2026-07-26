<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Exceptions\ProvisioningException;
use App\Domain\Core\Models\Application;
use App\Domain\Core\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

enum LifecycleState: string
{
    case Active = 'active';
    case Maintenance = 'maintenance';
    case Updating = 'updating';
    case Suspended = 'suspended';
    case Archived = 'archived';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Active => $target === self::Maintenance || $target === self::Suspended || $target === self::Archived,
            self::Maintenance => $target === self::Updating || $target === self::Active,
            self::Updating => $target === self::Active,
            self::Suspended => $target === self::Active,
            self::Archived => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Maintenance => 'Maintenance',
            self::Updating => 'Updating',
            self::Suspended => 'Suspended',
            self::Archived => 'Archived',
        };
    }
}

class ApplicationLifecycleService
{
    private array $stateStore = [];

    public function __construct(
        private ApplicationService $applicationService,
        private EventDispatcher $eventDispatcher,
    ) {}

    public function enterMaintenance(Application $app, Organization $org): void
    {
        $this->transitionWithEvent(
            $app, $org,
            LifecycleState::Maintenance,
            fn() => $this->eventDispatcher->dispatch('platform.application.maintenance.v1', ['organization_id' => $org->id, 'application_id' => (string) $app->id, 'application_name' => $app->name, 'entering_maintenance' => true])
        );
    }

    public function exitMaintenance(Application $app, Organization $org): void
    {
        $this->transitionWithEvent(
            $app, $org,
            LifecycleState::Active,
            fn() => $this->eventDispatcher->dispatch('platform.application.maintenance.v1', ['organization_id' => $org->id, 'application_id' => (string) $app->id, 'application_name' => $app->name, 'entering_maintenance' => false])
        );
    }

    public function startUpgrade(Application $app, Organization $org): void
    {
        $this->transition($app, $org, LifecycleState::Updating);
    }

    public function finishUpgrade(Application $app, Organization $org): void
    {
        $this->transitionWithEvent(
            $app, $org,
            LifecycleState::Active,
            fn() => Log::info("Application '{$app->name}' upgrade complete for organization '{$org->name}'")
        );
    }

    public function suspend(Application $app, Organization $org): void
    {
        $this->transitionWithEvent(
            $app, $org,
            LifecycleState::Suspended,
            fn() => $this->eventDispatcher->dispatch('platform.application.disabled.v1', ['organization_id' => $org->id, 'application_id' => (string) $app->id, 'application_name' => $app->name])
        );
    }

    public function reactivate(Application $app, Organization $org): void
    {
        $this->transitionWithEvent(
            $app, $org,
            LifecycleState::Active,
            fn() => $this->eventDispatcher->dispatch('platform.application.enabled.v1', ['organization_id' => $org->id, 'application_id' => (string) $app->id, 'application_name' => $app->name])
        );
    }

    public function archive(Application $app, Organization $org): void
    {
        $this->transitionWithEvent(
            $app, $org,
            LifecycleState::Archived,
            fn() => $this->eventDispatcher->dispatch('platform.application.archived.v1', ['organization_id' => $org->id, 'application_id' => (string) $app->id, 'application_name' => $app->name])
        );
    }

    public function currentState(string $appId, int $orgId): LifecycleState
    {
        return $this->stateStore["{$appId}:{$orgId}"] ?? LifecycleState::Active;
    }

    public function isValidTransition(LifecycleState $from, LifecycleState $to): bool
    {
        return $from->canTransitionTo($to);
    }

    private function transition(Application $app, Organization $org, LifecycleState $target): void
    {
        $key = "{$app->id}:{$org->id}";
        $current = $this->stateStore[$key] ?? LifecycleState::Active;

        if (!$current->canTransitionTo($target)) {
            throw new ProvisioningException(
                "Invalid lifecycle transition: '{$current->label()}' → '{$target->label()}'"
            );
        }

        $this->stateStore[$key] = $target;
    }

    private function transitionWithEvent(Application $app, Organization $org, LifecycleState $target, callable $fireEvent): void
    {
        DB::transaction(function () use ($app, $org, $target, $fireEvent) {
            $this->transition($app, $org, $target);
            $fireEvent();
        });

        Log::info("Application '{$app->name}' transitioned to '{$target->label()}' for organization '{$org->name}'", [
            'app_id' => $app->id,
            'org_id' => $org->id,
        ]);
    }
}
