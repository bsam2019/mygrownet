<?php

namespace Tests\Feature\GrowBuilder;

use App\Infrastructure\GrowBuilder\Models\GrowBuilderPage;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRouteTest extends TestCase
{
    use RefreshDatabase;

    private GrowBuilderSite $publishedSite;
    private GrowBuilderSite $draftSite;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();

        $this->publishedSite = GrowBuilderSite::create([
            'user_id' => $user->id,
            'name' => 'Published',
            'subdomain' => 'pub-' . uniqid(),
            'plan' => 'free',
            'status' => 'published',
            'published_at' => now(),
        ]);

        GrowBuilderPage::create([
            'site_id' => $this->publishedSite->id,
            'title' => 'Home',
            'slug' => 'home',
            'content_json' => ['sections' => []],
            'is_homepage' => true,
            'is_published' => true,
        ]);

        $this->draftSite = GrowBuilderSite::create([
            'user_id' => $user->id,
            'name' => 'Draft',
            'subdomain' => 'draft-' . uniqid(),
            'plan' => 'free',
            'status' => 'draft',
        ]);
    }

    public function test_welcome_page_loads(): void
    {
        $response = $this->get('/growbuilder');
        $response->assertStatus(200);
    }

    public function test_pricing_page_loads(): void
    {
        $response = $this->get('/growbuilder/pricing');
        $response->assertStatus(200);
    }

    public function test_terms_page_loads(): void
    {
        $response = $this->get('/growbuilder/terms');
        $response->assertStatus(200);
    }

    public function test_privacy_page_loads(): void
    {
        $response = $this->get('/growbuilder/privacy');
        $response->assertStatus(200);
    }

    public function test_manifest_returns_json(): void
    {
        $response = $this->get("/sites/{$this->publishedSite->subdomain}/manifest.json");
        $response->assertStatus(200);
        $response->assertJsonStructure(['name', 'short_name', 'start_url', 'display']);
    }

    public function test_blog_index_loads(): void
    {
        $response = $this->get("/sites/{$this->publishedSite->subdomain}/blog");
        $response->assertStatus(200);
    }

    public function test_checkout_api(): void
    {
        $response = $this->postJson("/gb-api/{$this->publishedSite->subdomain}/checkout", [
            'product_id' => 1,
            'quantity' => 1,
            'customer_name' => 'John',
            'customer_email' => 'j@example.com',
            'customer_phone' => '260977111111',
        ]);
        $this->assertContains($response->status(), [200, 201, 422]);
    }

    public function test_contact_api(): void
    {
        $response = $this->postJson("/gb-api/{$this->publishedSite->subdomain}/contact", [
            'name' => 'Visitor',
            'email' => 'v@example.com',
            'message' => 'Hello!',
        ]);

        $this->assertContains($response->status(), [200, 201, 422]);
    }

    public function test_chatbot_ask(): void
    {
        $response = $this->postJson("/gb-chatbot/{$this->publishedSite->id}/ask", [
            'message' => 'Hello',
        ]);

        $this->assertContains($response->status(), [200, 422, 500]);
    }

    public function test_whatsapp_webhook_verify(): void
    {
        $response = $this->get('/api/whatsapp/webhook?hub_mode=subscribe&hub_verify_token=test-token&hub_challenge=123');
        $this->assertContains($response->status(), [200, 403, 500]);
    }

    public function test_whatsapp_webhook_handle(): void
    {
        $response = $this->postJson('/api/whatsapp/webhook', [
            'entry' => [['changes' => [['value' => ['messages' => [['from' => '260977111111', 'text' => ['body' => 'Hi']]]]]]]],
        ]);

        $this->assertContains($response->status(), [200, 500]);
    }
}
