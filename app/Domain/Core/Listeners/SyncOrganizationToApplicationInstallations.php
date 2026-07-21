<?php

namespace App\Domain\Core\Listeners;

use App\Domain\Core\Events\OrganizationArchived;
use App\Domain\Core\Events\OrganizationCreated;
use App\Domain\Core\Models\Application;
use App\Domain\Core\Models\ApplicationInstallation;

class SyncOrganizationToApplicationInstallations
{
    public function handle(OrganizationCreated|OrganizationArchived $event): void
    {
        $org = $event->organization;

        if ($event instanceof OrganizationCreated) {
            $coreApps = Application::where('is_active', true)
                ->whereIn('slug', ['bms', 'stockflow'])
                ->get();

            foreach ($coreApps as $app) {
                ApplicationInstallation::firstOrCreate(
                    ['organization_id' => $org->id, 'application_id' => $app->id],
                    ['status' => 'active']
                );
            }
        }

        if ($event instanceof OrganizationArchived) {
            ApplicationInstallation::where('organization_id', $org->id)
                ->each(fn(ApplicationInstallation $i) => $i->update(['status' => 'suspended']));
        }
    }
}
