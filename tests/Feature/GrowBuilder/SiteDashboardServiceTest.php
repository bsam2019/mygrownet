<?php

namespace Tests\Feature\GrowBuilder;

use App\Domain\GrowBuilder\Services\SiteDashboardService;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Infrastructure\GrowBuilder\Models\SiteContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteDashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    private SiteDashboardService $service;
    private User $user;
    private GrowBuilderSite $site;
    private GrowBuilderSite $publishedSite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SiteDashboardService::class);
        $this->user = User::factory()->create();

        $this->site = GrowBuilderSite::create([
            'user_id' => $this->user->id,
            'name' => 'Draft Site',
            'subdomain' => 'draft-' . uniqid(),
            'status' => 'draft',
        ]);

        $this->publishedSite = GrowBuilderSite::create([
            'user_id' => $this->user->id,
            'name' => 'Published Site',
            'subdomain' => 'pub-' . uniqid(),
            'status' => 'published',
        ]);
    }

    public function test_get_user_sites_data(): void
    {
        $sites = $this->service->getUserSitesData($this->user->id);
        $this->assertCount(2, $sites);
    }

    public function test_get_user_sites_data_scoped_to_user(): void
    {
        $otherUser = User::factory()->create();
        $sites = $this->service->getUserSitesData($otherUser->id);
        $this->assertCount(0, $sites);
    }

    public function test_get_dashboard_stats(): void
    {
        $sites = $this->service->getUserSitesData($this->user->id);
        $stats = $this->service->getDashboardStats($sites);

        $this->assertEquals(2, $stats['totalSites']);
        $this->assertEquals(1, $stats['publishedSites']);
        $this->assertEquals(0, $stats['totalPageViews']);
        $this->assertEquals(0, $stats['totalOrders']);
    }

    public function test_get_dashboard_stats_counts_views(): void
    {
        GrowBuilderPageView::insert([
            ['site_id' => $this->publishedSite->id, 'path' => '/', 'viewed_date' => now()->format('Y-m-d'), 'ip_address' => '1.2.3.4'],
            ['site_id' => $this->publishedSite->id, 'path' => '/', 'viewed_date' => now()->format('Y-m-d'), 'ip_address' => '1.2.3.5'],
        ]);

        $sites = $this->service->getUserSitesData($this->user->id);
        $stats = $this->service->getDashboardStats($sites);

        $this->assertEquals(2, $stats['totalPageViews']);
    }

    public function test_get_dashboard_stats_counts_messages(): void
    {
        SiteContactMessage::insert([
            ['site_id' => $this->publishedSite->id, 'name' => 'A', 'email' => 'a@b.com', 'message' => 'Hi', 'status' => 'unread', 'created_at' => now(), 'updated_at' => now()],
            ['site_id' => $this->publishedSite->id, 'name' => 'B', 'email' => 'b@b.com', 'message' => 'Hello', 'status' => 'read', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $sites = $this->service->getUserSitesData($this->user->id);
        $stats = $this->service->getDashboardStats($sites);

        $this->assertEquals(2, $stats['totalMessages']);
        $this->assertEquals(1, $stats['unreadMessages']);
    }

    public function test_get_page_views_per_site(): void
    {
        GrowBuilderPageView::insert([
            ['site_id' => $this->publishedSite->id, 'path' => '/', 'viewed_date' => now()->format('Y-m-d'), 'ip_address' => '1.2.3.4'],
            ['site_id' => $this->publishedSite->id, 'path' => '/', 'viewed_date' => now()->format('Y-m-d'), 'ip_address' => '1.2.3.5'],
            ['site_id' => $this->site->id, 'path' => '/', 'viewed_date' => now()->format('Y-m-d'), 'ip_address' => '1.2.3.6'],
        ]);

        $sites = $this->service->getUserSitesData($this->user->id);
        $views = $this->service->getPageViewsPerSite($sites);

        $this->assertEquals(2, $views[$this->publishedSite->id]);
        $this->assertEquals(1, $views[$this->site->id]);
    }

    public function test_get_message_counts(): void
    {
        SiteContactMessage::insert([
            ['site_id' => $this->publishedSite->id, 'name' => 'A', 'email' => 'a@b.com', 'message' => 'Hi', 'status' => 'unread', 'created_at' => now(), 'updated_at' => now()],
            ['site_id' => $this->site->id, 'name' => 'B', 'email' => 'b@b.com', 'message' => 'Hello', 'status' => 'read', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $sites = $this->service->getUserSitesData($this->user->id);
        $counts = $this->service->getMessageCounts($sites);

        $this->assertEquals(1, $counts[$this->publishedSite->id]->total);
        $this->assertEquals(1, $counts[$this->publishedSite->id]->unread);
        $this->assertEquals(1, $counts[$this->site->id]->total);
    }

    public function test_get_daily_page_views(): void
    {
        GrowBuilderPageView::insert([
            ['site_id' => $this->publishedSite->id, 'path' => '/', 'viewed_date' => now()->subDays(0)->format('Y-m-d'), 'ip_address' => '1.2.3.4'],
            ['site_id' => $this->publishedSite->id, 'path' => '/', 'viewed_date' => now()->subDays(1)->format('Y-m-d'), 'ip_address' => '1.2.3.5'],
            ['site_id' => $this->publishedSite->id, 'path' => '/', 'viewed_date' => now()->subDays(1)->format('Y-m-d'), 'ip_address' => '1.2.3.6'],
        ]);

        $sites = $this->service->getUserSitesData($this->user->id);
        $daily = $this->service->getDailyPageViews($sites, 7);

        $this->assertArrayHasKey($this->publishedSite->id, $daily->toArray());
    }

    public function test_get_recent_messages(): void
    {
        SiteContactMessage::insert([
            ['site_id' => $this->publishedSite->id, 'name' => 'Recent', 'email' => 'r@b.com', 'message' => 'New', 'status' => 'unread', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $sites = $this->service->getUserSitesData($this->user->id);
        $messages = $this->service->getRecentMessages($sites, 5);

        $this->assertCount(1, $messages);
        $this->assertEquals('Recent', $messages->first()['name']);
    }

    public function test_format_site_data(): void
    {
        $this->publishedSite->load(['pages', 'media', 'client']);

        $sites = $this->service->getUserSitesData($this->user->id);
        $pageViews = $this->service->getPageViewsPerSite($sites);
        $messageCounts = $this->service->getMessageCounts($sites);
        $dailyViews = $this->service->getDailyPageViews($sites);

        $formatted = $this->service->formatSiteData($this->publishedSite, $pageViews, $messageCounts, $dailyViews);

        $this->assertEquals($this->publishedSite->id, $formatted['id']);
        $this->assertEquals('Published Site', $formatted['name']);
        $this->assertTrue($formatted['isPublished']);
        $this->assertArrayHasKey('sparkline', $formatted);
    }

    public function test_get_site_with_access_check_returns_site(): void
    {
        $site = $this->service->getSiteWithAccessCheck($this->site->id, $this->user->id);
        $this->assertNotNull($site);
        $this->assertEquals($this->site->id, $site->id);
    }

    public function test_get_site_with_access_check_throws_for_wrong_user(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->service->getSiteWithAccessCheck($this->site->id, 99999);
    }

    public function test_find_site_for_user_found(): void
    {
        $site = $this->service->findSiteForUser($this->site->id, $this->user->id);
        $this->assertNotNull($site);
    }

    public function test_find_site_for_user_not_found(): void
    {
        $site = $this->service->findSiteForUser($this->site->id, 99999);
        $this->assertNull($site);
    }
}
