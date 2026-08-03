<?php

namespace App\Domain\Core\Contracts;

/**
 * Canonical source of the company/business identity for an organization.
 *
 * This is the decoupled mechanism behind "enter your company details once":
 * applications read company details here instead of asking the user to retype
 * them. An app should still own its own per-app profile record where it stores
 * app-specific configuration, but the company's legal/address/contact details
 * are sourced from here and seeded into the app profile at install/setup.
 *
 * The returned array keys mirror the `organizations` table columns.
 */
interface CompanyDetailsProvider extends ProviderContract
{
    /**
     * Canonical company details for an organization.
     *
     * @return array{
     *     name: string,
     *     slug: string,
     *     logo_path: ?string,
     *     address: ?string,
     *     phone: ?string,
     *     email: ?string,
     *     website: ?string,
     *     country: ?string,
     *     currency: ?string,
     *     timezone: ?string,
     *     language: ?string,
     *     tax_number: ?string,
     *     registration_number: ?string,
     * }
     */
    public function getCompanyDetails(int $organizationId): array;

    /**
     * True when the organization has a company-profile record suitable for
     * being the canonical source (i.e. it has been created at the platform).
     */
    public function hasCompanyDetails(int $organizationId): bool;
}