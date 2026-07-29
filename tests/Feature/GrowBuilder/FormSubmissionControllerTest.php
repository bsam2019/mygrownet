<?php

namespace Tests\Feature\GrowBuilder;

use App\Infrastructure\GrowBuilder\Models\GrowBuilderFormSubmission;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormSubmissionControllerTest extends TestCase
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

    public function test_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get("/growbuilder/sites/{$this->site->id}/form-submissions");
        $response->assertStatus(200);
    }

    public function test_toggle_read(): void
    {
        $submission = GrowBuilderFormSubmission::create([
            'site_id' => $this->site->id,
            'form_id' => 'contact',
            'data' => ['name' => 'John'],
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->user)->post("/growbuilder/sites/{$this->site->id}/form-submissions/{$submission->id}/toggle-read");
        $response->assertStatus(302);
    }

    public function test_mark_spam(): void
    {
        $submission = GrowBuilderFormSubmission::create([
            'site_id' => $this->site->id,
            'form_id' => 'contact',
            'data' => ['name' => 'John'],
        ]);

        $response = $this->actingAs($this->user)->post("/growbuilder/sites/{$this->site->id}/form-submissions/{$submission->id}/spam");
        $response->assertStatus(302);
    }

    public function test_export(): void
    {
        $response = $this->actingAs($this->user)->get("/growbuilder/sites/{$this->site->id}/form-submissions/export");
        $response->assertStatus(200);
    }
}
