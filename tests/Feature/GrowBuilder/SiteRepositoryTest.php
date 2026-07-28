<?php

namespace Tests\Feature\GrowBuilder;

use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Domain\GrowBuilder\ValueObjects\SitePlan;
use App\Domain\GrowBuilder\ValueObjects\SiteStatus;
use App\Domain\GrowBuilder\ValueObjects\Subdomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

class SiteRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SiteRepositoryInterface $repository;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(SiteRepositoryInterface::class);
        $this->user = User::factory()->create();
    }

    public function test_can_save_and_find_by_id(): void
    {
        $site = \App\Domain\GrowBuilder\Entities\Site::create(
            userId: $this->user->id,
            name: 'My Shop',
            subdomain: Subdomain::fromString('myshop'),
        );

        $saved = $this->repository->save($site);
        $this->assertNotNull($saved->getId());
        $this->assertGreaterThan(0, $saved->getId()->value());

        $found = $this->repository->findById($saved->getId());
        $this->assertNotNull($found);
        $this->assertEquals('My Shop', $found->getName());
        $this->assertEquals('myshop', $found->getSubdomain()->value());
        $this->assertEquals($this->user->id, $found->getUserId());
    }

    public function test_create_returns_null_id(): void
    {
        $site = \App\Domain\GrowBuilder\Entities\Site::create(
            userId: $this->user->id,
            name: 'New',
            subdomain: Subdomain::fromString('brandnew'),
        );
        $this->assertNull($site->getId());
    }

    public function test_cannot_find_non_existent(): void
    {
        $found = $this->repository->findById(SiteId::fromInt(999));
        $this->assertNull($found);
    }

    public function test_find_by_subdomain(): void
    {
        $site = \App\Domain\GrowBuilder\Entities\Site::create(
            userId: $this->user->id,
            name: 'Test',
            subdomain: Subdomain::fromString('uniquetest'),
        );
        $this->repository->save($site);

        $found = $this->repository->findBySubdomain(Subdomain::fromString('uniquetest'));
        $this->assertNotNull($found);
        $this->assertEquals('Test', $found->getName());

        $notFound = $this->repository->findBySubdomain(Subdomain::fromString('nonexistent'));
        $this->assertNull($notFound);
    }

    public function test_subdomain_exists(): void
    {
        $site = \App\Domain\GrowBuilder\Entities\Site::create(
            userId: $this->user->id,
            name: 'Test',
            subdomain: Subdomain::fromString('existcheck'),
        );
        $this->repository->save($site);

        $this->assertTrue($this->repository->subdomainExists(Subdomain::fromString('existcheck')));
        $this->assertFalse($this->repository->subdomainExists(Subdomain::fromString('notexist')));
    }

    public function test_find_by_user_id(): void
    {
        $site1 = \App\Domain\GrowBuilder\Entities\Site::create(1, 'A', Subdomain::fromString('site-a'));
        $site2 = \App\Domain\GrowBuilder\Entities\Site::create(1, 'B', Subdomain::fromString('site-b'));
        $this->repository->save($site1);
        $this->repository->save($site2);

        $user2 = User::factory()->create();
        $site3 = \App\Domain\GrowBuilder\Entities\Site::create($user2->id, 'C', Subdomain::fromString('site-c'));
        $this->repository->save($site3);

        $userSites = $this->repository->findByUserId(1);
        $this->assertCount(2, $userSites);

        $user2Sites = $this->repository->findByUserId($user2->id);
        $this->assertCount(1, $user2Sites);
    }

    public function test_find_by_id_for_user(): void
    {
        $site = \App\Domain\GrowBuilder\Entities\Site::create(
            userId: $this->user->id,
            name: 'Mine',
            subdomain: Subdomain::fromString('mine'),
        );
        $saved = $this->repository->save($site);

        $found = $this->repository->findByIdForUser($saved->getId(), $this->user->id);
        $this->assertNotNull($found);

        $otherUser = User::factory()->create();
        $notFound = $this->repository->findByIdForUser($saved->getId(), $otherUser->id);
        $this->assertNull($notFound);
    }

    public function test_custom_domain_exists(): void
    {
        $site = \App\Domain\GrowBuilder\Entities\Site::create(
            userId: $this->user->id,
            name: 'Custom',
            subdomain: Subdomain::fromString('customd'),
        );
        $saved = $this->repository->save($site);
        $saved->setCustomDomain('example.com');
        $this->repository->save($saved);

        $this->assertTrue($this->repository->customDomainExists('example.com'));
        $this->assertFalse($this->repository->customDomainExists('nonexistent.com'));
    }

    public function test_count_by_user_id(): void
    {
        $this->repository->save(\App\Domain\GrowBuilder\Entities\Site::create($this->user->id, 'A', Subdomain::fromString('count-a')));
        $this->repository->save(\App\Domain\GrowBuilder\Entities\Site::create($this->user->id, 'B', Subdomain::fromString('count-b')));
        $this->repository->save(\App\Domain\GrowBuilder\Entities\Site::create($this->user->id, 'C', Subdomain::fromString('count-c')));

        $this->assertEquals(3, $this->repository->countByUserId($this->user->id));
    }

    public function test_save_updates_existing(): void
    {
        $site = \App\Domain\GrowBuilder\Entities\Site::create(
            userId: $this->user->id,
            name: 'Original',
            subdomain: Subdomain::fromString('original'),
        );
        $saved = $this->repository->save($site);
        $id = $saved->getId();

        $saved->updateName('Updated');
        $this->repository->save($saved);

        $found = $this->repository->findById($id);
        $this->assertEquals('Updated', $found->getName());
    }

    public function test_delete(): void
    {
        $site = \App\Domain\GrowBuilder\Entities\Site::create(
            userId: $this->user->id,
            name: 'Delete Me',
            subdomain: Subdomain::fromString('deleteme'),
        );
        $saved = $this->repository->save($site);

        $this->repository->delete($saved->getId());

        $found = $this->repository->findById($saved->getId());
        $this->assertNull($found);
    }

    public function test_save_preserves_plan_and_status(): void
    {
        $site = \App\Domain\GrowBuilder\Entities\Site::create(
            userId: $this->user->id,
            name: 'Preserve',
            subdomain: Subdomain::fromString('preserve'),
        );
        $site->publish();

        $expiresAt = new \DateTimeImmutable('+1 year');
        $site->upgradePlan(SitePlan::business(), $expiresAt);

        $saved = $this->repository->save($site);
        $found = $this->repository->findById($saved->getId());

        $this->assertTrue($found->isPublished());
        $this->assertTrue($found->getPlan()->isBusiness());
        $this->assertNotNull($found->getPublishedAt());
        $this->assertNotNull($found->getPlanExpiresAt());
    }
}
