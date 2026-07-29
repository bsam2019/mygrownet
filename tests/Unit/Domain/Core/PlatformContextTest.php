<?php

namespace Tests\Unit\Domain\Core;

use App\Domain\Core\ValueObjects\PlatformContext;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PlatformContextTest extends TestCase
{
    #[Test]
    public function constructor_sets_all_properties()
    {
        $context = new PlatformContext(
            traceId: 'trace-1',
            requestId: 'req-1',
            userId: 'user-1',
            organizationId: 'org-1',
            applicationId: 'app-1',
            installationId: 'inst-1',
            workspaceId: 'ws-1',
            locale: 'fr',
            timezone: 'Africa/Lusaka',
        );

        $this->assertEquals('trace-1', $context->traceId);
        $this->assertEquals('req-1', $context->requestId);
        $this->assertEquals('user-1', $context->userId);
        $this->assertEquals('org-1', $context->organizationId);
        $this->assertEquals('app-1', $context->applicationId);
        $this->assertEquals('inst-1', $context->installationId);
        $this->assertEquals('ws-1', $context->workspaceId);
        $this->assertEquals('fr', $context->locale);
        $this->assertEquals('Africa/Lusaka', $context->timezone);
    }

    #[Test]
    public function constructor_applies_defaults()
    {
        $context = new PlatformContext(
            traceId: 't',
            requestId: 'r',
            userId: 'u',
            organizationId: 'o',
            applicationId: 'a',
        );

        $this->assertNull($context->installationId);
        $this->assertEquals('', $context->workspaceId);
        $this->assertEquals('en', $context->locale);
        $this->assertEquals('UTC', $context->timezone);
    }

    #[Test]
    public function make_creates_context_with_auto_generated_ids()
    {
        $context = PlatformContext::make(
            userId: 'user-1',
            organizationId: 'org-1',
            applicationId: 'app-1',
        );

        $this->assertEquals('user-1', $context->userId);
        $this->assertEquals('org-1', $context->organizationId);
        $this->assertEquals('app-1', $context->applicationId);
        $this->assertNotNull($context->traceId);
        $this->assertNotNull($context->requestId);
        $this->assertNull($context->installationId);
        $this->assertEquals('', $context->workspaceId);
    }

    #[Test]
    public function make_accepts_optional_parameters()
    {
        $context = PlatformContext::make(
            userId: 'u',
            organizationId: 'o',
            applicationId: 'a',
            installationId: 'inst-1',
            traceId: 'provided-trace',
            requestId: 'provided-req',
            workspaceId: 'ws-1',
            locale: 'es',
            timezone: 'America/New_York',
        );

        $this->assertEquals('provided-trace', $context->traceId);
        $this->assertEquals('provided-req', $context->requestId);
        $this->assertEquals('inst-1', $context->installationId);
        $this->assertEquals('ws-1', $context->workspaceId);
        $this->assertEquals('es', $context->locale);
        $this->assertEquals('America/New_York', $context->timezone);
    }

    #[Test]
    public function withInstallation_returns_new_instance()
    {
        $context = PlatformContext::make(
            userId: 'u',
            organizationId: 'o',
            applicationId: 'a',
        );

        $updated = $context->withInstallation('new-inst');

        $this->assertNotSame($context, $updated);
        $this->assertNull($context->installationId);
        $this->assertEquals('new-inst', $updated->installationId);
        $this->assertEquals($context->userId, $updated->userId);
        $this->assertEquals($context->organizationId, $updated->organizationId);
        $this->assertEquals($context->applicationId, $updated->applicationId);
    }

    #[Test]
    public function toArray_returns_correct_keys()
    {
        $context = PlatformContext::make(
            userId: 'u',
            organizationId: 'o',
            applicationId: 'a',
            installationId: 'inst',
            workspaceId: 'ws',
            locale: 'en',
            timezone: 'UTC',
        );

        $data = $context->toArray();

        $this->assertArrayHasKey('trace_id', $data);
        $this->assertArrayHasKey('request_id', $data);
        $this->assertEquals('u', $data['user_id']);
        $this->assertEquals('o', $data['organization_id']);
        $this->assertEquals('a', $data['application_id']);
        $this->assertEquals('inst', $data['installation_id']);
        $this->assertEquals('ws', $data['workspace_id']);
        $this->assertEquals('en', $data['locale']);
        $this->assertEquals('UTC', $data['timezone']);
    }

    #[Test]
    public function fromArray_restores_context()
    {
        $original = PlatformContext::make(
            userId: 'u',
            organizationId: 'o',
            applicationId: 'a',
            installationId: 'inst',
            workspaceId: 'ws',
            locale: 'fr',
            timezone: 'Africa/Lusaka',
        );

        $restored = PlatformContext::fromArray($original->toArray());

        $this->assertEquals($original->traceId, $restored->traceId);
        $this->assertEquals($original->requestId, $restored->requestId);
        $this->assertEquals($original->userId, $restored->userId);
        $this->assertEquals($original->organizationId, $restored->organizationId);
        $this->assertEquals($original->applicationId, $restored->applicationId);
        $this->assertEquals($original->installationId, $restored->installationId);
        $this->assertEquals($original->workspaceId, $restored->workspaceId);
        $this->assertEquals($original->locale, $restored->locale);
        $this->assertEquals($original->timezone, $restored->timezone);
    }

    #[Test]
    public function fromArray_handles_missing_keys()
    {
        $restored = PlatformContext::fromArray([]);

        $this->assertNotEmpty($restored->traceId);
        $this->assertNotEmpty($restored->requestId);
        $this->assertEquals('', $restored->userId);
        $this->assertEquals('', $restored->organizationId);
        $this->assertEquals('', $restored->applicationId);
        $this->assertNull($restored->installationId);
        $this->assertEquals('', $restored->workspaceId);
        $this->assertEquals('en', $restored->locale);
        $this->assertEquals('UTC', $restored->timezone);
    }
}
