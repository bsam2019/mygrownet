<?php

namespace App\Domain\BMS\Listeners;

use App\Domain\Core\Events\OrganizationArchived;
use App\Domain\Core\Events\OrganizationCreated;
use App\Infrastructure\Persistence\Eloquent\BMS\CompanyModel;

class SyncOrganizationToBmsCompany
{
    public function handle(OrganizationCreated|OrganizationArchived $event): void
    {
        $org = $event->organization;

        if ($event instanceof OrganizationCreated) {
            CompanyModel::where('organization_id', $org->id)
                ->orWhere('email', $org->owner?->email)
                ->firstOrCreate(
                    ['organization_id' => $org->id],
                    [
                        'name' => $org->name,
                        'status' => 'active',
                        'email' => $org->owner?->email,
                    ]
                );
        }

        if ($event instanceof OrganizationArchived) {
            CompanyModel::where('organization_id', $org->id)
                ->each(fn(CompanyModel $c) => $c->update(['status' => 'suspended']));
        }
    }
}
