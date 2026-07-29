<?php

namespace Tests\Feature\GrowBuilder;

use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Infrastructure\GrowBuilder\Models\SiteContactMessage;
use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $admin;
    private GrowBuilderSite $site;
    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->admin = User::factory()->create();

        $this->agency = Agency::create([
            'owner_user_id' => $this->user->id,
            'agency_name' => 'Test Agency',
            'slug' => 'test-agency-' . uniqid(),
            'business_email' => 'agency@example.com',
            'status' => 'active',
        ]);

        $this->site = GrowBuilderSite::create([
            'user_id' => $this->user->id,
            'name' => 'Test Site',
            'subdomain' => 'test-' . uniqid(),
            'plan' => 'free',
            'status' => 'draft',
        ]);
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $protectedRoutes = [
            'GET' => ['/growbuilder/dashboard', '/growbuilder/sites/create', '/growbuilder/agency/dashboard', '/growbuilder/clients'],
            'POST' => ['/growbuilder/sites', '/growbuilder/logout'],
        ];

        foreach ($protectedRoutes as $method => $routes) {
            foreach ($routes as $route) {
                $response = $this->call($method, $route);
                $response->assertRedirect('/login');
            }
        }
    }

    public function test_store_site_creates_and_redirects(): void
    {
        $response = $this->actingAs($this->user)->post('/growbuilder/sites', [
            'name' => 'New Site',
            'subdomain' => 'new-' . uniqid(),
        ]);

        $response->assertStatus(302);
    }

    public function test_store_site_without_agency_redirects(): void
    {
        $userNoAgency = User::factory()->create();
        $response = $this->actingAs($userNoAgency)->post('/growbuilder/sites', [
            'name' => 'Orphan Site',
            'subdomain' => 'orphan-' . uniqid(),
        ]);
        $response->assertRedirect(route('growbuilder.agency.dashboard'));
    }

    public function test_publish_site(): void
    {
        $response = $this->actingAs($this->user)->post("/growbuilder/sites/{$this->site->id}/publish");

        $response->assertJson(['message' => 'Site published successfully.']);
        $this->assertDatabaseHas('growbuilder_sites', [
            'id' => $this->site->id,
            'status' => 'published',
        ]);
    }

    public function test_unpublish_site(): void
    {
        $this->site->update(['status' => 'published', 'published_at' => now()]);

        $response = $this->actingAs($this->user)->post("/growbuilder/sites/{$this->site->id}/unpublish");

        $response->assertJson(['message' => 'Site unpublished.']);
        $this->assertDatabaseHas('growbuilder_sites', [
            'id' => $this->site->id,
            'status' => 'draft',
        ]);
    }

    public function test_non_owner_cannot_publish(): void
    {
        $response = $this->actingAs($this->admin)->post("/growbuilder/sites/{$this->site->id}/publish");
        $response->assertStatus(404);
    }

    public function test_site_messages_list(): void
    {
        SiteContactMessage::create([
            'site_id' => $this->site->id,
            'name' => 'Visitor',
            'email' => 'v@example.com',
            'message' => 'Hello',
            'status' => 'unread',
        ]);

        $response = $this->actingAs($this->user)->get("/growbuilder/sites/{$this->site->id}/messages");
        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'total']);
    }

    public function test_reply_to_message(): void
    {
        $msg = SiteContactMessage::create([
            'site_id' => $this->site->id,
            'name' => 'Visitor',
            'email' => 'v@example.com',
            'message' => 'Hello',
            'status' => 'unread',
        ]);

        $response = $this->actingAs($this->user)->post("/growbuilder/sites/{$this->site->id}/messages/{$msg->id}/reply", [
            'reply' => 'Thanks for reaching out!',
        ]);

        $response->assertJson(['message' => 'Reply sent successfully.']);
        $this->assertDatabaseHas('site_contact_messages', [
            'id' => $msg->id,
            'status' => 'replied',
        ]);
    }

    public function test_update_message_status(): void
    {
        $msg = SiteContactMessage::create([
            'site_id' => $this->site->id,
            'name' => 'Visitor',
            'email' => 'v@example.com',
            'message' => 'Hello',
            'status' => 'unread',
        ]);

        $response = $this->actingAs($this->user)->put("/growbuilder/sites/{$this->site->id}/messages/{$msg->id}/status", [
            'status' => 'read',
        ]);

        $response->assertJson(['message' => 'Message status updated.']);
    }

    public function test_delete_message(): void
    {
        $msg = SiteContactMessage::create([
            'site_id' => $this->site->id,
            'name' => 'Visitor',
            'email' => 'v@example.com',
            'message' => 'Hello',
            'status' => 'unread',
        ]);

        $response = $this->actingAs($this->user)->delete("/growbuilder/sites/{$this->site->id}/messages/{$msg->id}");
        $response->assertJson(['message' => 'Message deleted.']);
    }

    public function test_export_messages(): void
    {
        $response = $this->actingAs($this->user)->get("/growbuilder/sites/{$this->site->id}/messages-export");
        $response->assertStatus(200);
    }

    public function test_non_owner_cannot_access_site(): void
    {
        $response = $this->actingAs($this->admin)->get("/growbuilder/sites/{$this->site->id}");
        $response->assertStatus(404);
    }

    public function test_show_product_stub(): void
    {
        $this->site->update(['status' => 'published', 'published_at' => now()]);
        $response = $this->get("/sites/{$this->site->subdomain}/product/test-product");
        $response->assertStatus(200);
    }

    public function test_checkout_stub(): void
    {
        $this->site->update(['status' => 'published', 'published_at' => now()]);
        $response = $this->get("/sites/{$this->site->subdomain}/checkout");
        $response->assertStatus(200);
    }
}
