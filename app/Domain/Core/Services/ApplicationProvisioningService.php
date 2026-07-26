<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Enums\ProvisioningState;
use App\Domain\Core\Exceptions\ProvisioningException;
use App\Domain\Core\Models\Application;
use App\Domain\Core\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApplicationProvisioningService
{
    private array $stateStore = [];

    public function __construct(
        private ApplicationService $applicationService,
        private EventDispatcher $eventDispatcher,
    ) {}

    public function enable(Application $app, Organization $org): void
    {
        $current = $this->currentState($app->id, $org->id);

        if ($current === ProvisioningState::Active) {
            return;
        }

        if (!$current->canTransitionTo(ProvisioningState::Active)) {
            throw new ProvisioningException(
                "Cannot enable '{$app->name}': current state is '{$current->label()}'"
            );
        }

        DB::transaction(function () use ($app, $org) {
            $this->transitionTo($app->id, $org->id, ProvisioningState::Installing);
            $this->runInstallPipeline($app, $org);
            $this->transitionTo($app->id, $org->id, ProvisioningState::Configuring);
            $this->runConfigurePipeline($app, $org);
            $this->transitionTo($app->id, $org->id, ProvisioningState::Active);
            $this->applicationService->installForOrganization($org, $app);

            $this->eventDispatcher->dispatch('platform.application.enabled.v1', ['organization_id' => $org->id, 'organization_name' => $org->name, 'application_id' => (string) $app->id, 'application_name' => $app->name]);
        });

        Log::info("Application '{$app->name}' enabled for organization '{$org->name}'", [
            'app_id' => $app->id,
            'org_id' => $org->id,
        ]);
    }

    public function disable(Application $app, Organization $org): void
    {
        $current = $this->currentState($app->id, $org->id);

        if ($current === ProvisioningState::Disabled) {
            return;
        }

        if (!$current->canTransitionTo(ProvisioningState::Disabled)) {
            throw new ProvisioningException(
                "Cannot disable '{$app->name}': current state is '{$current->label()}'"
            );
        }

        DB::transaction(function () use ($app, $org) {
            $this->transitionTo($app->id, $org->id, ProvisioningState::Disabled);
            $this->applicationService->uninstallForOrganization($org, $app);
            $this->runTeardownPipeline($app, $org);

            $this->eventDispatcher->dispatch('platform.application.disabled.v1', ['organization_id' => $org->id, 'organization_name' => $org->name, 'application_id' => (string) $app->id, 'application_name' => $app->name]);
        });

        Log::info("Application '{$app->name}' disabled for organization '{$org->name}'", [
            'app_id' => $app->id,
            'org_id' => $org->id,
        ]);
    }

    public function currentState(string $appId, int $orgId): ProvisioningState
    {
        return $this->stateStore["{$appId}:{$orgId}"] ?? ProvisioningState::Disabled;
    }

    public function resetState(string $appId, int $orgId): void
    {
        unset($this->stateStore["{$appId}:{$orgId}"]);
    }

    private function transitionTo(string $appId, int $orgId, ProvisioningState $target): void
    {
        $key = "{$appId}:{$orgId}";
        $current = $this->stateStore[$key] ?? ProvisioningState::Disabled;

        if (!$current->canTransitionTo($target)) {
            throw new ProvisioningException(
                "Invalid state transition: '{$current->label()}' → '{$target->label()}'"
            );
        }

        $this->stateStore[$key] = $target;
    }

    private function runInstallPipeline(Application $app, Organization $org): void
    {
        Log::debug("Installing '{$app->name}' for org '{$org->name}'");
    }

    private function runConfigurePipeline(Application $app, Organization $org): void
    {
        Log::debug("Configuring '{$app->name}' for org '{$org->name}'");
    }

    private function runTeardownPipeline(Application $app, Organization $org): void
    {
        Log::debug("Tearing down '{$app->name}' for org '{$org->name}'");
    }
}
