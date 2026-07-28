<?php

namespace Tests\Feature\GrowBuilder;

use App\Domain\GrowBuilder\Repositories\PageRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\PageContent;
use App\Domain\GrowBuilder\ValueObjects\PageId;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Domain\GrowBuilder\ValueObjects\Subdomain;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PageRepositoryInterface $repository;
    private GrowBuilderSite $site;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(PageRepositoryInterface::class);
        $user = User::factory()->create();
        $this->site = GrowBuilderSite::create([
            'user_id' => $user->id,
            'name' => 'Test Site',
            'subdomain' => 'pagetest',
            'status' => 'draft',
            'plan' => 'free',
        ]);
    }

    public function test_save_and_find_by_id(): void
    {
        $page = \App\Domain\GrowBuilder\Entities\Page::create(
            siteId: $this->site->id,
            title: 'About',
            slug: 'about',
            content: PageContent::fromArray([['type' => 'text']]),
        );

        $saved = $this->repository->save($page);
        $this->assertNotNull($saved->getId());

        $found = $this->repository->findById($saved->getId());
        $this->assertNotNull($found);
        $this->assertEquals('About', $found->getTitle());
        $this->assertEquals('about', $found->getSlug());
    }

    public function test_find_by_site_id(): void
    {
        $this->repository->save(\App\Domain\GrowBuilder\Entities\Page::create($this->site->id, 'A', 'a', PageContent::empty()));
        $this->repository->save(\App\Domain\GrowBuilder\Entities\Page::create($this->site->id, 'B', 'b', PageContent::empty()));

        $pages = $this->repository->findBySiteId(SiteId::fromInt($this->site->id));
        $this->assertCount(2, $pages);
    }

    public function test_find_by_site_id_and_slug(): void
    {
        $this->repository->save(\App\Domain\GrowBuilder\Entities\Page::create($this->site->id, 'Home', 'home', PageContent::empty()));

        $found = $this->repository->findBySiteIdAndSlug(SiteId::fromInt($this->site->id), 'home');
        $this->assertNotNull($found);
        $this->assertEquals('Home', $found->getTitle());

        $notFound = $this->repository->findBySiteIdAndSlug(SiteId::fromInt($this->site->id), 'nonexistent');
        $this->assertNull($notFound);
    }

    public function test_find_homepage(): void
    {
        $this->repository->save(\App\Domain\GrowBuilder\Entities\Page::create($this->site->id, 'Home', 'home', PageContent::empty(), isHomepage: true));
        $this->repository->save(\App\Domain\GrowBuilder\Entities\Page::create($this->site->id, 'About', 'about', PageContent::empty()));

        $homepage = $this->repository->findHomepage(SiteId::fromInt($this->site->id));
        $this->assertNotNull($homepage);
        $this->assertEquals('Home', $homepage->getTitle());
        $this->assertTrue($homepage->isHomepage());
    }

    public function test_find_homepage_returns_null_if_none(): void
    {
        $this->assertNull($this->repository->findHomepage(SiteId::fromInt($this->site->id)));
    }

    public function test_find_published_by_site_id(): void
    {
        $home = \App\Domain\GrowBuilder\Entities\Page::create($this->site->id, 'Home', 'home', PageContent::empty());
        $home->publish();
        $this->repository->save($home);

        $this->repository->save(\App\Domain\GrowBuilder\Entities\Page::create($this->site->id, 'Draft', 'draft', PageContent::empty()));

        $published = $this->repository->findPublishedBySiteId(SiteId::fromInt($this->site->id));
        $this->assertCount(1, $published);
        $this->assertEquals('Home', $published[0]->getTitle());
    }

    public function test_clear_homepage(): void
    {
        $page = \App\Domain\GrowBuilder\Entities\Page::create($this->site->id, 'Home', 'home', PageContent::empty(), isHomepage: true);
        $saved = $this->repository->save($page);
        $this->assertTrue($saved->isHomepage());

        $this->repository->clearHomepage(SiteId::fromInt($this->site->id));

        $found = $this->repository->findById($saved->getId());
        $this->assertFalse($found->isHomepage());
    }

    public function test_count_by_site_id(): void
    {
        $this->repository->save(\App\Domain\GrowBuilder\Entities\Page::create($this->site->id, 'A', 'a', PageContent::empty()));
        $this->repository->save(\App\Domain\GrowBuilder\Entities\Page::create($this->site->id, 'B', 'b', PageContent::empty()));

        $count = $this->repository->countBySiteId(SiteId::fromInt($this->site->id));
        $this->assertEquals(2, $count);
    }

    public function test_delete(): void
    {
        $page = \App\Domain\GrowBuilder\Entities\Page::create($this->site->id, 'Delete', 'delete', PageContent::empty());
        $saved = $this->repository->save($page);

        $this->repository->delete($saved->getId());

        $this->assertNull($this->repository->findById($saved->getId()));
    }

    public function test_save_updates_existing(): void
    {
        $page = \App\Domain\GrowBuilder\Entities\Page::create($this->site->id, 'Original', 'original', PageContent::empty());
        $saved = $this->repository->save($page);

        $saved->updateTitle('Updated');
        $this->repository->save($saved);

        $found = $this->repository->findById($saved->getId());
        $this->assertEquals('Updated', $found->getTitle());
    }

    public function test_save_preserves_publish_state(): void
    {
        $page = \App\Domain\GrowBuilder\Entities\Page::create($this->site->id, 'Pub', 'pub', PageContent::empty());
        $page->publish();
        $saved = $this->repository->save($page);

        $found = $this->repository->findById($saved->getId());
        $this->assertTrue($found->isPublished());
    }

    public function test_save_preserves_homepage(): void
    {
        $page = \App\Domain\GrowBuilder\Entities\Page::create($this->site->id, 'Home', 'home', PageContent::empty(), isHomepage: true);
        $saved = $this->repository->save($page);

        $found = $this->repository->findById($saved->getId());
        $this->assertTrue($found->isHomepage());
    }

    public function test_save_preserves_nav_visibility(): void
    {
        $page = \App\Domain\GrowBuilder\Entities\Page::create($this->site->id, 'Hidden', 'hidden', PageContent::empty(), showInNav: false);
        $saved = $this->repository->save($page);

        $found = $this->repository->findById($saved->getId());
        $this->assertFalse($found->showInNav());
    }
}
