<?php

declare(strict_types=1);

use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->otherUser = User::factory()->create();
    
    $this->site = GrowBuilderSite::create([
        'user_id' => $this->owner->id,
        'name' => 'Test Site',
        'subdomain' => 'testsite',
        'status' => 'draft',
        'plan' => 'free',
    ]);
    
    $this->homepage = GrowBuilderPage::create([
        'site_id' => $this->site->id,
        'title' => 'Home',
        'slug' => 'home',
        'content_json' => ['sections' => []],
        'is_homepage' => true,
        'is_published' => true,
        'show_in_nav' => true,
        'nav_order' => 0,
    ]);
});

describe('Editor Access', function () {
    
    it('allows owner to access editor', function () {
        $response = $this->actingAs($this->owner)
            ->get("/growbuilder/editor/{$this->site->id}");
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('GrowBuilder/Editor/Index')
                ->has('site')
                ->has('pages')
                ->has('currentPage')
        );
    });
    
    it('prevents non-owner from accessing editor', function () {
        $response = $this->actingAs($this->otherUser)
            ->get("/growbuilder/editor/{$this->site->id}");
        
        $response->assertStatus(404);
    });
    
    it('redirects unauthenticated user to login', function () {
        $response = $this->get("/growbuilder/editor/{$this->site->id}");
        
        $response->assertRedirect('/login');
    });
    
    it('loads specific page in editor', function () {
        $aboutPage = GrowBuilderPage::create([
            'site_id' => $this->site->id,
            'title' => 'About',
            'slug' => 'about',
            'content_json' => ['sections' => []],
            'is_homepage' => false,
            'is_published' => true,
            'show_in_nav' => true,
            'nav_order' => 1,
        ]);
        
        $response = $this->actingAs($this->owner)
            ->get("/growbuilder/editor/{$this->site->id}?page={$aboutPage->id}");
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('GrowBuilder/Editor/Index')
                ->where('currentPage.id', $aboutPage->id)
        );
    });
    
});

describe('Page Management', function () {
    
    it('allows owner to create new page', function () {
        $response = $this->actingAs($this->owner)
            ->post("/growbuilder/editor/{$this->site->id}/pages", [
                'title' => 'About Us',
                'slug' => 'about-us',
                'sections' => [],
                'show_in_nav' => true,
            ]);
        
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseHas('growbuilder_pages', [
            'site_id' => $this->site->id,
            'title' => 'About Us',
            'slug' => 'about-us',
        ]);
    });
    
    it('prevents duplicate page slugs', function () {
        $response = $this->actingAs($this->owner)
            ->post("/growbuilder/editor/{$this->site->id}/pages", [
                'title' => 'Home Duplicate',
                'slug' => 'home', // Already exists
                'sections' => [],
            ]);
        
        $response->assertStatus(422);
    });
    
    it('allows owner to update page content', function () {
        $sections = [
            [
                'id' => 'section-1',
                'type' => 'hero',
                'content' => ['title' => 'Welcome'],
                'style' => [],
            ],
        ];
        
        $response = $this->actingAs($this->owner)
            ->post("/growbuilder/editor/{$this->site->id}/pages/{$this->homepage->id}/save", [
                'title' => 'Home',
                'content' => ['sections' => $sections],
            ]);
        
        $response->assertJson(['success' => true]);
        
        $this->homepage->refresh();
        expect($this->homepage->content_json['sections'])->toHaveCount(1);
    });
    
    it('allows owner to update page metadata', function () {
        $response = $this->actingAs($this->owner)
            ->put("/growbuilder/editor/{$this->site->id}/pages/{$this->homepage->id}", [
                'title' => 'Updated Home',
                'slug' => 'home',
                'show_in_nav' => false,
            ]);
        
        $response->assertJson(['success' => true]);
        
        $this->homepage->refresh();
        expect($this->homepage->title)->toBe('Updated Home');
        expect($this->homepage->show_in_nav)->toBeFalse();
    });
    
    it('prevents deleting homepage', function () {
        $response = $this->actingAs($this->owner)
            ->delete("/growbuilder/editor/{$this->site->id}/pages/{$this->homepage->id}");
        
        $response->assertStatus(422);
        $response->assertJson(['error' => 'Cannot delete homepage']);
    });
    
    it('allows deleting non-homepage pages', function () {
        $aboutPage = GrowBuilderPage::create([
            'site_id' => $this->site->id,
            'title' => 'About',
            'slug' => 'about',
            'content_json' => ['sections' => []],
            'is_homepage' => false,
            'is_published' => true,
            'show_in_nav' => true,
            'nav_order' => 1,
        ]);
        
        $response = $this->actingAs($this->owner)
            ->delete("/growbuilder/editor/{$this->site->id}/pages/{$aboutPage->id}");
        
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseMissing('growbuilder_pages', [
            'id' => $aboutPage->id,
        ]);
    });
    
});

describe('Site Settings', function () {
    
    it('allows owner to update navigation settings', function () {
        $response = $this->actingAs($this->owner)
            ->post("/growbuilder/editor/{$this->site->id}/settings", [
                'navigation' => [
                    'logoText' => 'My Brand',
                    'sticky' => true,
                    'style' => 'centered',
                ],
            ]);
        
        $response->assertJson(['success' => true]);
        
        $this->site->refresh();
        expect($this->site->settings['navigation']['logoText'])->toBe('My Brand');
    });
    
    it('allows owner to update footer settings', function () {
        $response = $this->actingAs($this->owner)
            ->post("/growbuilder/editor/{$this->site->id}/settings", [
                'footer' => [
                    'copyrightText' => '© 2025 My Company',
                    'layout' => 'centered',
                ],
            ]);
        
        $response->assertJson(['success' => true]);
        
        $this->site->refresh();
        expect($this->site->settings['footer']['copyrightText'])->toBe('© 2025 My Company');
    });
    
});
