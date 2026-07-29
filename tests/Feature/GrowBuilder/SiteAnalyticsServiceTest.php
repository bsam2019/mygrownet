<?php

namespace Tests\Feature\GrowBuilder;

use App\Domain\GrowBuilder\Services\SiteAnalyticsService;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteAnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    private SiteAnalyticsService $service;
    private int $siteId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SiteAnalyticsService::class);
        $user = User::factory()->create();
        $site = \App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::create([
            'user_id' => $user->id,
            'name' => 'Test Site',
            'subdomain' => 'test-' . uniqid(),
            'status' => 'published',
        ]);
        $this->siteId = $site->id;

        GrowBuilderPageView::insert([
            ['site_id' => $this->siteId, 'viewed_date' => now()->subDays(1)->format('Y-m-d'), 'ip_address' => '1.2.3.4', 'path' => '/', 'device_type' => 'mobile', 'country' => 'ZM'],
            ['site_id' => $this->siteId, 'viewed_date' => now()->subDays(1)->format('Y-m-d'), 'ip_address' => '1.2.3.5', 'path' => '/about', 'device_type' => 'desktop', 'country' => 'ZM'],
            ['site_id' => $this->siteId, 'viewed_date' => now()->subDays(2)->format('Y-m-d'), 'ip_address' => '1.2.3.4', 'path' => '/', 'device_type' => 'mobile', 'country' => 'US'],
            ['site_id' => $this->siteId, 'viewed_date' => now()->subDays(10)->format('Y-m-d'), 'ip_address' => '1.2.3.6', 'path' => '/contact', 'device_type' => 'mobile', 'country' => 'ZM'],
            ['site_id' => 99999, 'viewed_date' => now()->subDays(1)->format('Y-m-d'), 'ip_address' => '9.9.9.9', 'path' => '/other', 'device_type' => 'desktop', 'country' => 'ZA'],
        ]);
    }

    public function test_get_total_views_within_days(): void
    {
        $views = $this->service->getTotalViews($this->siteId, 7);
        $this->assertEquals(3, $views);
    }

    public function test_get_total_views_outside_range(): void
    {
        $views = $this->service->getTotalViews($this->siteId, 3);
        $this->assertEquals(3, $views);
    }

    public function test_get_total_views_all_time(): void
    {
        $views = $this->service->getTotalViews($this->siteId, 30);
        $this->assertEquals(4, $views);
    }

    public function test_get_total_visitors_within_days(): void
    {
        $visitors = $this->service->getTotalVisitors($this->siteId, 7);
        $this->assertEquals(2, $visitors);
    }

    public function test_get_total_visitors_no_duplicates(): void
    {
        $visitors = $this->service->getTotalVisitors($this->siteId, 30);
        $this->assertEquals(3, $visitors);
    }

    public function test_get_previous_period_views(): void
    {
        GrowBuilderPageView::insert([
            ['site_id' => $this->siteId, 'viewed_date' => now()->subDays(8)->format('Y-m-d'), 'ip_address' => '1.2.3.7', 'path' => '/', 'device_type' => 'mobile', 'country' => 'ZM'],
        ]);

        $prev = $this->service->getPreviousPeriodViews($this->siteId, 7);
        $this->assertEquals(2, $prev);
    }

    public function test_get_daily_stats_returns_filled_series(): void
    {
        $stats = $this->service->getDailyStats($this->siteId, 7);
        $this->assertCount(7, $stats);
        $this->assertArrayHasKey('date', $stats->first());
        $this->assertArrayHasKey('views', $stats->first());
        $this->assertArrayHasKey('visitors', $stats->first());
    }

    public function test_get_device_stats(): void
    {
        $stats = $this->service->getDeviceStats($this->siteId, 7, 4);
        $this->assertGreaterThan(0, $stats->count());
        $deviceTypes = $stats->pluck('device')->toArray();
        $this->assertContains('mobile', $deviceTypes);
        $this->assertContains('desktop', $deviceTypes);
    }

    public function test_get_top_pages(): void
    {
        $pages = $this->service->getTopPages($this->siteId, 7, 5);
        $this->assertGreaterThan(0, $pages->count());
        $paths = $pages->pluck('path')->toArray();
        $this->assertContains('/', $paths);
    }

    public function test_get_top_pages_respects_limit(): void
    {
        $pages = $this->service->getTopPages($this->siteId, 7, 1);
        $this->assertCount(1, $pages);
    }

    public function test_get_traffic_sources(): void
    {
        $sources = $this->service->getTrafficSources($this->siteId, 7, 2);
        $this->assertGreaterThan(0, $sources->count());
    }

    public function test_get_geographic_data(): void
    {
        $geo = $this->service->getGeographicData($this->siteId, 7, 2);
        $this->assertGreaterThan(0, $geo->count());
        $codes = $geo->pluck('countryCode')->toArray();
        $this->assertContains('ZM', $codes);
    }
}
