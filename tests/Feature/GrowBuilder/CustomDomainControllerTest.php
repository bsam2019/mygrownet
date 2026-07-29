<?php

namespace Tests\Feature\GrowBuilder;

use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomDomainControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private GrowBuilderSite $site;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->site = GrowBuilderSite::create([
            'user_id' => $this->user->id,
            'name' => 'Site',
            'subdomain' => 'site-' . uniqid(),
            'plan' => 'free',
            'status' => 'draft',
        ]);
    }

    public function test_domain_status(): void
    {
        $response = $this->actingAs($this->user)->get("/growbuilder/sites/{$this->site->id}/domain/status");
        $response->assertStatus(200);
    }

    public function test_verify_dns(): void
    {
        $response = $this->actingAs($this->user)->post("/growbuilder/sites/{$this->site->id}/domain/verify", [
            'domain' => 'example.com',
        ]);

        $this->assertContains($response->status(), [200, 422]);
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $response = $this->get("/growbuilder/sites/{$this->site->id}/domain/status");
        $response->assertRedirect('/login');
    }
}
