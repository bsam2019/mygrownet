<?php

namespace Tests\Feature\Platform;

use App\Domain\Core\Contracts\CompanyDetailsProvider;
use App\Domain\Core\Models\Organization;
use App\Domain\Core\Services\OrganizationEntryResolver;
use App\Domain\Core\Services\OrganizationService;
use App\Domain\Workspace\Services\ContextResolverService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyDetailsRoundTripTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrg(): Organization
    {
        $user = User::factory()->create();

        return app(OrganizationService::class)->create([
            'name' => 'Rockshield Investments',
            'slug' => 'rockshield',
            'type' => 'company',
            'status' => 'active',
            'owner_id' => $user->id,
            'country' => 'ZM',
            'currency' => 'ZMW',
            'timezone' => 'Africa/Lusaka',
            'language' => 'en',
        ]);
    }

    public function test_provider_returns_canonical_company_details()
    {
        $org = $this->makeOrg();
        $org->update([
            'logo_path' => 'logos/rock.png',
            'address' => '123 Main St, Lusaka',
            'phone' => '+260977000000',
            'email' => 'info@rocksmith.example',
            'website' => 'https://rocksmith.example',
            'tax_number' => 'TPIN-0001',
            'registration_number' => 'REG-001',
        ]);

        $provider = app(CompanyDetailsProvider::class);

        $this->assertTrue($provider->hasCompanyDetails($org->id));
        $this->assertEquals([
            'name' => 'Rockshield Investments',
            'slug' => 'rockshield',
            'logo_path' => 'logos/rock.png',
            'address' => '123 Main St, Lusaka',
            'phone' => '+260977000000',
            'email' => 'info@rocksmith.example',
            'website' => 'https://rocksmith.example',
            'country' => 'ZM',
            'currency' => 'ZMW',
            'timezone' => 'Africa/Lusaka',
            'language' => 'en',
            'tax_number' => 'TPIN-0001',
            'registration_number' => 'REG-001',
        ], $provider->getCompanyDetails($org->id));
    }

    public function test_entry_resolver_returns_empty_company_details_without_context()
    {
        $user = User::factory()->create();
        $resolver = app(OrganizationEntryResolver::class);

        $this->assertEquals([], $resolver->companyDetails($user));
    }

    public function test_entry_resolver_reads_company_details_from_active_org_context()
    {
        $org = $this->makeOrg();
        $user = User::factory()->create();

        // Make the user an active member of the org, then switch the workspace
        // context (as the workspace pages do) so the resolver can read the org.
        $org->members()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        app(ContextResolverService::class)->switchContext($user, 'organization', $org->id);

        $resolver = app(OrganizationEntryResolver::class);

        $this->assertEquals($org->id, $resolver->activeOrganizationId($user));
        $this->assertEquals('Rockshield Investments', $resolver->companyDetails($user)['name']);
    }
}