<?php

namespace Tests\Unit\GrowBuilder;

use App\Domain\GrowBuilder\Entities\Site;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Domain\GrowBuilder\ValueObjects\SitePlan;
use App\Domain\GrowBuilder\ValueObjects\SiteStatus;
use App\Domain\GrowBuilder\ValueObjects\Subdomain;
use App\Domain\GrowBuilder\ValueObjects\Theme;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class SiteEntityTest extends TestCase
{
    public function test_create(): void
    {
        $site = Site::create(
            userId: 1,
            name: 'My Shop',
            subdomain: Subdomain::fromString('myshop'),
            templateId: 5,
            description: 'An online shop',
        );

        $this->assertNull($site->getId());
        $this->assertEquals(1, $site->getUserId());
        $this->assertEquals('My Shop', $site->getName());
        $this->assertTrue($site->getSubdomain()->equals(Subdomain::fromString('myshop')));
        $this->assertEquals(5, $site->getTemplateId());
        $this->assertEquals('An online shop', $site->getDescription());
        $this->assertTrue($site->getStatus()->isDraft());
        $this->assertTrue($site->getPlan()->isStarter());
        $this->assertNull($site->getPublishedAt());
        $this->assertNull($site->getCustomDomain());
        $this->assertNull($site->getLogo());
        $this->assertNull($site->getFavicon());
        $this->assertNull($site->getTheme());
        $this->assertNull($site->getClientId());
        $this->assertEquals([], $site->getSettings());
        $this->assertEquals([], $site->getSocialLinks());
        $this->assertEquals([], $site->getContactInfo());
        $this->assertEquals([], $site->getBusinessHours());
        $this->assertEquals([], $site->getSeoSettings());
        $this->assertNull($site->getPlanExpiresAt());
    }

    public function test_create_with_client(): void
    {
        $site = Site::create(
            userId: 1,
            name: 'Client Site',
            subdomain: Subdomain::fromString('client-site'),
            clientId: 42,
        );
        $this->assertEquals(42, $site->getClientId());
    }

    public function test_reconstitute(): void
    {
        $now = new DateTimeImmutable();
        $site = Site::reconstitute(
            id: SiteId::fromInt(10),
            userId: 1, clientId: 2, templateId: 3,
            name: 'Recon', subdomain: Subdomain::fromString('recon'),
            customDomain: 'recon.com', description: 'Recon site',
            logo: 'logo.png', favicon: 'favicon.ico',
            settings: ['key' => 'val'], theme: Theme::create(primaryColor: '#000'),
            socialLinks: ['fb' => 'fb.com'], contactInfo: ['email' => 'a@b.com'],
            businessHours: ['mon' => '9-5'], seoSettings: ['title' => 'SEO'],
            status: SiteStatus::published(), plan: SitePlan::business(),
            publishedAt: $now, planExpiresAt: $now,
            createdAt: $now, updatedAt: $now,
        );

        $this->assertEquals(10, $site->getId()->value());
        $this->assertTrue($site->isPublished());
        $this->assertTrue($site->getPlan()->isBusiness());
        $this->assertEquals('recon.com', $site->getCustomDomain());
        $this->assertEquals('#000', $site->getTheme()->getPrimaryColor());
        $this->assertEquals($now, $site->getPublishedAt());
    }

    public function test_publish(): void
    {
        $site = Site::create(1, 'Test', Subdomain::fromString('test'));
        $site->publish();

        $this->assertTrue($site->isPublished());
        $this->assertTrue($site->getStatus()->isPublished());
        $this->assertNotNull($site->getPublishedAt());
    }

    public function test_publish_when_already_published_does_not_change(): void
    {
        $site = Site::create(1, 'Test', Subdomain::fromString('test'));
        $site->publish();
        $firstPublishedAt = $site->getPublishedAt();

        $site->publish();
        $this->assertEquals($firstPublishedAt, $site->getPublishedAt());
    }

    public function test_unpublish(): void
    {
        $site = Site::create(1, 'Test', Subdomain::fromString('test'));
        $site->publish();
        $site->unpublish();

        $this->assertFalse($site->isPublished());
        $this->assertTrue($site->getStatus()->isDraft());
    }

    public function test_suspend(): void
    {
        $site = Site::create(1, 'Test', Subdomain::fromString('test'));
        $site->suspend();
        $this->assertTrue($site->getStatus()->isSuspended());
    }

    public function test_can_publish_returns_false_when_suspended(): void
    {
        $site = Site::create(1, 'Test', Subdomain::fromString('test'));
        $site->suspend();
        $this->assertFalse($site->canPublish());
    }

    public function test_can_publish_returns_true_when_draft(): void
    {
        $site = Site::create(1, 'Test', Subdomain::fromString('test'));
        $this->assertTrue($site->canPublish());
    }

    public function test_update_name(): void
    {
        $site = Site::create(1, 'Old', Subdomain::fromString('test'));
        $site->updateName('New');
        $this->assertEquals('New', $site->getName());
    }

    public function test_update_subdomain(): void
    {
        $site = Site::create(1, 'Test', Subdomain::fromString('old'));
        $site->updateSubdomain(Subdomain::fromString('new-name'));
        $this->assertTrue($site->getSubdomain()->equals(Subdomain::fromString('new-name')));
    }

    public function test_update_description(): void
    {
        $site = Site::create(1, 'Test', Subdomain::fromString('test'));
        $site->updateDescription('New desc');
        $this->assertEquals('New desc', $site->getDescription());

        $site->updateDescription(null);
        $this->assertNull($site->getDescription());
    }

    public function test_update_logo_and_favicon(): void
    {
        $site = Site::create(1, 'Test', Subdomain::fromString('test'));
        $site->updateLogo('logo.svg');
        $site->updateFavicon('favicon.ico');
        $this->assertEquals('logo.svg', $site->getLogo());
        $this->assertEquals('favicon.ico', $site->getFavicon());

        $site->setLogo('new.svg');
        $this->assertEquals('new.svg', $site->getLogo());
    }

    public function test_update_theme(): void
    {
        $site = Site::create(1, 'Test', Subdomain::fromString('test'));
        $theme = Theme::create(primaryColor: '#ff0000');
        $site->updateTheme($theme);
        $this->assertSame($theme, $site->getTheme());
    }

    public function test_update_contact_info(): void
    {
        $site = Site::create(1, 'Test', Subdomain::fromString('test'));
        $site->updateContactInfo(['email' => 'test@example.com']);
        $this->assertEquals(['email' => 'test@example.com'], $site->getContactInfo());
    }

    public function test_update_social_links(): void
    {
        $site = Site::create(1, 'Test', Subdomain::fromString('test'));
        $site->updateSocialLinks(['facebook' => 'fb.com/page']);
        $this->assertEquals(['facebook' => 'fb.com/page'], $site->getSocialLinks());
    }

    public function test_update_seo_settings(): void
    {
        $site = Site::create(1, 'Test', Subdomain::fromString('test'));
        $site->updateSeoSettings(['title' => 'SEO Title']);
        $this->assertEquals(['title' => 'SEO Title'], $site->getSeoSettings());
    }

    public function test_set_custom_domain(): void
    {
        $site = Site::create(1, 'Test', Subdomain::fromString('test'));
        $site->setCustomDomain('example.com');
        $this->assertEquals('example.com', $site->getCustomDomain());

        $site->setCustomDomain(null);
        $this->assertNull($site->getCustomDomain());
    }

    public function test_upgrade_plan(): void
    {
        $site = Site::create(1, 'Test', Subdomain::fromString('test'));
        $expiresAt = new DateTimeImmutable('+1 year');
        $site->upgradePlan(SitePlan::pro(), $expiresAt);

        $this->assertTrue($site->getPlan()->isPro());
        $this->assertEquals($expiresAt, $site->getPlanExpiresAt());
    }

    public function test_plan_expired_when_null(): void
    {
        $site = Site::create(1, 'Test', Subdomain::fromString('test'));
        $this->assertFalse($site->isPlanExpired());
    }

    public function test_is_plan_expired(): void
    {
        $expired = new DateTimeImmutable('-1 day');
        $site = Site::create(1, 'Test', Subdomain::fromString('test'));
        $site->upgradePlan(SitePlan::pro(), $expired);
        $this->assertTrue($site->isPlanExpired());
    }

    public function test_set_settings(): void
    {
        $site = Site::create(1, 'Test', Subdomain::fromString('test'));
        $site->setSettings(['theme' => 'dark']);
        $this->assertEquals(['theme' => 'dark'], $site->getSettings());
    }
}
