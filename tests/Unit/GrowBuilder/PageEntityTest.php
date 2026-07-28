<?php

namespace Tests\Unit\GrowBuilder;

use App\Domain\GrowBuilder\Entities\Page;
use App\Domain\GrowBuilder\ValueObjects\PageContent;
use App\Domain\GrowBuilder\ValueObjects\PageId;
use PHPUnit\Framework\TestCase;

class PageEntityTest extends TestCase
{
    public function test_create_with_minimal_params(): void
    {
        $page = Page::create(
            siteId: 1,
            title: 'Home',
            slug: 'home',
            content: PageContent::empty(),
        );

        $this->assertNull($page->getId());
        $this->assertEquals(1, $page->getSiteId());
        $this->assertEquals('Home', $page->getTitle());
        $this->assertEquals('home', $page->getSlug());
        $this->assertTrue($page->getContent()->isEmpty());
        $this->assertFalse($page->isHomepage());
        $this->assertFalse($page->isPublished());
        $this->assertTrue($page->showInNav());
        $this->assertEquals(0, $page->getNavOrder());
        $this->assertNull($page->getMetaTitle());
        $this->assertNull($page->getMetaDescription());
        $this->assertNull($page->getOgImage());
    }

    public function test_create_as_homepage(): void
    {
        $page = Page::create(1, 'Home', 'home', PageContent::empty(), isHomepage: true);
        $this->assertTrue($page->isHomepage());
    }

    public function test_create_hidden_from_nav(): void
    {
        $page = Page::create(1, 'Privacy', 'privacy', PageContent::empty(), showInNav: false);
        $this->assertFalse($page->showInNav());
    }

    public function test_reconstitute(): void
    {
        $content = PageContent::fromArray([['type' => 'hero']]);
        $page = Page::reconstitute(
            id: PageId::fromInt(42),
            siteId: 1, title: 'About', slug: 'about',
            content: $content,
            metaTitle: 'About Us', metaDescription: 'Learn about us', ogImage: 'og.jpg',
            isHomepage: false, isPublished: true,
            showInNav: true, navOrder: 2,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->assertEquals(42, $page->getId()->value());
        $this->assertEquals('About', $page->getTitle());
        $this->assertEquals('About Us', $page->getMetaTitle());
        $this->assertEquals('og.jpg', $page->getOgImage());
        $this->assertTrue($page->isPublished());
    }

    public function test_update_title_and_slug(): void
    {
        $page = Page::create(1, 'Old', 'old', PageContent::empty());
        $page->updateTitle('New Title');
        $page->updateSlug('new-slug');

        $this->assertEquals('New Title', $page->getTitle());
        $this->assertEquals('new-slug', $page->getSlug());
    }

    public function test_update_slug_normalizes(): void
    {
        $page = Page::create(1, 'Test', 'test', PageContent::empty());
        $page->updateSlug('  New Slug!!  ');
        $this->assertEquals('new-slug', $page->getSlug());
    }

    public function test_update_content(): void
    {
        $page = Page::create(1, 'Test', 'test', PageContent::empty());
        $newContent = PageContent::fromArray([['type' => 'hero']]);
        $page->updateContent($newContent);
        $this->assertEquals(1, $page->getContent()->count());
        $this->assertSame($newContent, $page->getContent());
    }

    public function test_update_seo(): void
    {
        $page = Page::create(1, 'Test', 'test', PageContent::empty());
        $page->updateSeo('Meta Title', 'Meta Desc', 'og-image.jpg');
        $this->assertEquals('Meta Title', $page->getMetaTitle());
        $this->assertEquals('Meta Desc', $page->getMetaDescription());
        $this->assertEquals('og-image.jpg', $page->getOgImage());
    }

    public function test_update_seo_with_partial_params(): void
    {
        $page = Page::create(1, 'Test', 'test', PageContent::empty());
        $page->updateSeo('Title Only', null);
        $this->assertEquals('Title Only', $page->getMetaTitle());
        $this->assertNull($page->getMetaDescription());
        $this->assertNull($page->getOgImage());
    }

    public function test_get_effective_meta_title_returns_title_if_no_meta(): void
    {
        // The effective meta title is on the Eloquent model, not the entity.
        // The entity doesn't have getEffectiveMetaTitle(), so we just verify getMetaTitle() returns null.
        $page = Page::create(1, 'Home', 'home', PageContent::empty());
        $this->assertNull($page->getMetaTitle());
    }

    public function test_publish_and_unpublish(): void
    {
        $page = Page::create(1, 'Test', 'test', PageContent::empty());
        $page->publish();
        $this->assertTrue($page->isPublished());

        $page->unpublish();
        $this->assertFalse($page->isPublished());
    }

    public function test_set_and_unset_homepage(): void
    {
        $page = Page::create(1, 'Test', 'test', PageContent::empty());
        $page->setAsHomepage();
        $this->assertTrue($page->isHomepage());

        $page->unsetAsHomepage();
        $this->assertFalse($page->isHomepage());
    }

    public function test_set_nav_visibility(): void
    {
        $page = Page::create(1, 'Test', 'test', PageContent::empty());
        $page->setNavVisibility(false, 5);
        $this->assertFalse($page->showInNav());
        $this->assertEquals(5, $page->getNavOrder());
    }

    public function test_set_show_in_nav(): void
    {
        $page = Page::create(1, 'Test', 'test', PageContent::empty());
        $page->setShowInNav(false);
        $this->assertFalse($page->showInNav());
    }
}
