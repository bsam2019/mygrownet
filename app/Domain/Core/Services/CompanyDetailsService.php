<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Contracts\CompanyDetailsProvider;
use App\Domain\Core\Models\Organization;

/**
 * Reference implementation of CompanyDetailsProvider backed by the platform
 * `organizations` table. This is the single place a user records their
 * company identity; apps seed their own per-app profile from the returned
 * array rather than asking the user to re-enter the same details.
 */
class CompanyDetailsService implements CompanyDetailsProvider
{
    public function capability(): string
    {
        return 'company.details';
    }

    public function getCompanyDetails(int $organizationId): array
    {
        $org = Organization::find($organizationId);

        return [
            'name' => $org?->name ?? '',
            'slug' => $org?->slug ?? '',
            'logo_path' => $org?->logo_path,
            'address' => $org?->address,
            'phone' => $org?->phone,
            'email' => $org?->email,
            'website' => $org?->website,
            'country' => $org?->country,
            'currency' => $org?->currency,
            'timezone' => $org?->timezone,
            'language' => $org?->language,
            'tax_number' => $org?->tax_number,
            'registration_number' => $org?->registration_number,
        ];
    }

    public function hasCompanyDetails(int $organizationId): bool
    {
        return Organization::whereKey($organizationId)->exists();
    }
}