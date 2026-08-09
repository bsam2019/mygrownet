<?php

namespace Tests\Feature\GrowBuilder;

use App\Infrastructure\GrowBuilder\Models\GrowBuilderPage;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomDomainSeoTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private GrowBuilderSite $site;
    private GrowBuilderPage $homepage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->site = GrowBuilderSite::create([
            'user_id' => $this->user->id,
            'name' => 'Acme Auto Repairs',
            'subdomain' => 'acme-repairs',
            'custom_domain' => 'acmerepairs.com',
            'description' => 'Best auto repair service in town',
            'status' => 'published',
            'plan' => 'business',
            'seo_settings' => [
                'metaTitle' => 'Acme Auto Repairs | Top Rated Mechanics',
                'metaDescription' => 'Professional car service and repair in Central City.',
                'ogImage' => 'https://acmerepairs.com/images/og-banner.jpg',
            ],
        ]);

        $this->homepage = GrowBuilderPage::create([
            'site_id' => $this->site->id,
            'title' => 'Home',
            'slug' => '',
            'content_json' => ['sections' => []],
            'meta_title' => 'Acme Auto Repairs - Quality Car Service',
            'meta_description' => 'Fast and reliable auto repair shop.',
            'og_image' => 'https://acmerepairs.com/images/home-og.jpg',
            'is_homepage' => true,
            'is_published' => true,
            'show_in_nav' => true,
            'nav_order' => 1,
        ]);
    }

    public function test_custom_domain_renders_site_seo_title_and_description(): void
    {
        $response = $this->get('http://acmerepairs.com/');

        $response->assertStatus(200);

        $content = $response->getContent();

        // Must render client site's SEO title and NOT platform 'MyGrowNet' title
        $this->assertStringContainsString('<title inertia>Acme Auto Repairs - Quality Car Service</title>', $content);
        $this->assertStringContainsString('name="description" content="Fast and reliable auto repair shop."', $content);
        $this->assertStringContainsString('property="og:site_name" content="Acme Auto Repairs"', $content);
        $this->assertStringContainsString('property="og:title" content="Acme Auto Repairs - Quality Car Service"', $content);
        $this->assertStringContainsString('property="og:description" content="Fast and reliable auto repair shop."', $content);
        $this->assertStringContainsString('<script type="application/ld+json">', $content);
        $this->assertStringContainsString('"name":"Acme Auto Repairs"', $content);
    }

    public function test_editor_updates_page_seo_metadata(): void
    {
        $response = $this->actingAs($this->user)
            ->putJson("/growbuilder/editor/{$this->site->id}/pages/{$this->homepage->id}", [
                'title' => 'Updated Home Title',
                'slug' => '',
                'show_in_nav' => true,
                'meta_title' => 'Custom Meta Title for SEO',
                'meta_description' => 'Custom Meta Description for SEO results',
                'og_image' => 'https://acmerepairs.com/images/new-og.jpg',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('growbuilder_pages', [
            'id' => $this->homepage->id,
            'title' => 'Updated Home Title',
            'meta_title' => 'Custom Meta Title for SEO',
            'meta_description' => 'Custom Meta Description for SEO results',
            'og_image' => 'https://acmerepairs.com/images/new-og.jpg',
        ]);
    }
}
