<?php

namespace App\Domain\StockFlow\Listeners;

use App\Domain\Core\Events\OrganizationArchived;
use App\Domain\Core\Events\OrganizationCreated;
use App\Models\StockAudit\Company;

class SyncOrganizationToCompany
{
    public function handle(OrganizationCreated|OrganizationArchived $event): void
    {
        $org = $event->organization;

        if ($event instanceof OrganizationCreated) {
            Company::where('organization_id', $org->id)
                ->orWhere('email', $org->owner?->email)
                ->firstOrCreate(
                    ['organization_id' => $org->id],
                    [
                        'name' => $org->name,
                        'subdomain' => $org->slug,
                        'status' => 'active',
                        'email' => $org->owner?->email,
                    ]
                );
        }

        if ($event instanceof OrganizationArchived) {
            Company::where('organization_id', $org->id)
                ->each(fn(Company $c) => $c->update(['status' => 'suspended']));
        }
    }
}
