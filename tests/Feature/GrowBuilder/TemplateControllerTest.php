<?php

namespace Tests\Feature\GrowBuilder;

use App\Models\GrowBuilder\SiteTemplate;
use App\Models\GrowBuilder\SiteTemplateIndustry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_template_index_returns_json(): void
    {
        SiteTemplate::create([
            'name' => 'Business Pro',
            'slug' => 'business-pro',
            'industry' => 'business',
            'description' => 'A business template',
            'thumbnail' => '/thumb.jpg',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get('/growbuilder/templates');
        $response->assertStatus(200);
        $response->assertJsonStructure(['templates']);
    }

    public function test_template_industries_returns_json(): void
    {
        SiteTemplateIndustry::create([
            'name' => 'Technology',
            'slug' => 'technology',
            'description' => 'Tech templates',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->user)->get('/growbuilder/templates/industries');
        $response->assertStatus(200);
    }

    public function test_template_show_returns_json(): void
    {
        $template = SiteTemplate::create([
            'name' => 'Business Pro',
            'slug' => 'business-pro',
            'industry' => 'business',
            'description' => 'A business template',
            'thumbnail' => '/thumb.jpg',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get("/growbuilder/templates/{$template->id}");
        $response->assertStatus(200);
    }

    public function test_template_live_preview(): void
    {
        $template = SiteTemplate::create([
            'name' => 'Business Pro',
            'slug' => 'business-pro',
            'industry' => 'business',
            'description' => 'A business template',
            'thumbnail' => '/thumb.jpg',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get("/growbuilder/templates/{$template->id}/live");
        $response->assertStatus(200);
    }
}
