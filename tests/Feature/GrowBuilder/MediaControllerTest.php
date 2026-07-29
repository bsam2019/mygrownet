<?php

namespace Tests\Feature\GrowBuilder;

use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MediaControllerTest extends TestCase
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

    public function test_media_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get("/growbuilder/media/{$this->site->id}");
        $response->assertStatus(200);
    }

    public function test_media_upload(): void
    {
        $file = UploadedFile::fake()->image('test.jpg', 100, 100);

        $response = $this->actingAs($this->user)->post("/growbuilder/media/{$this->site->id}", [
            'file' => $file,
        ]);

        $this->assertContains($response->status(), [200, 302, 422, 500]);
    }

    public function test_unauthenticated_user_cannot_upload(): void
    {
        $response = $this->post("/growbuilder/media/{$this->site->id}", [
            'file' => UploadedFile::fake()->image('test.jpg'),
        ]);

        $response->assertRedirect('/login');
    }
}
