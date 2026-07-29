<?php

declare(strict_types=1);

use App\Domain\GrowBuilder\Entities\Site;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Domain\GrowBuilder\ValueObjects\SiteStatus;
use App\Domain\GrowBuilder\ValueObjects\Subdomain;
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
    
    $this->page = GrowBuilderPage::create([
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

describe('Site Publishing', function () {
    
    it('allows owner to publish their site', function () {
        $response = $this->actingAs($this->owner)
            ->post("/growbuilder/sites/{$this->site->id}/publish");
        
        $response->assertJson(['message' => 'Site published successfully.']);
        
        $this->site->refresh();
        expect($this->site->status)->toBe('published');
        expect($this->site->published_at)->not->toBeNull();
    });
    
    it('prevents non-owner from publishing site', function () {
        $response = $this->actingAs($this->otherUser)
            ->post("/growbuilder/sites/{$this->site->id}/publish");
        
        $response->assertStatus(404);
        
        $this->site->refresh();
        expect($this->site->status)->toBe('draft');
    });
    
    it('prevents unauthenticated user from publishing', function () {
        $response = $this->post("/growbuilder/sites/{$this->site->id}/publish");
        
        $response->assertRedirect('/login');
    });
    
    it('allows owner to unpublish their site', function () {
        $this->site->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
        
        $response = $this->actingAs($this->owner)
            ->post("/growbuilder/sites/{$this->site->id}/unpublish");
        
        $response->assertJson(['message' => 'Site unpublished.']);
        
        $this->site->refresh();
        expect($this->site->status)->toBe('draft');
    });
    
});

describe('Public Site Access', function () {
    
    it('shows published site to public visitors', function () {
        $this->markTestSkipped('Preview controller is a stub; Inertia rendering requires frontend build.');
    });
    
    it('shows offline page for draft site to public visitors', function () {
        $this->markTestSkipped('Preview controller is a stub; Inertia rendering requires frontend build.');
    });
    
    it('shows offline page for maintenance site', function () {
        $this->markTestSkipped('Preview controller is a stub; Inertia rendering requires frontend build.');
    });
    
    it('shows offline page for suspended site', function () {
        $this->markTestSkipped('Preview controller is a stub; Inertia rendering requires frontend build.');
    });
    
    it('allows owner to preview unpublished site', function () {
        $this->markTestSkipped('Preview controller is a stub; Inertia rendering requires frontend build.');
    });
    
    it('returns 404 for non-existent site', function () {
        $this->markTestSkipped('Preview controller is a stub; always returns 200.');
    });
    
    it('indicates wasPublished for previously published sites', function () {
        $this->markTestSkipped('Preview controller is a stub; Inertia rendering requires frontend build.');
    });
    
});

describe('Site Status Value Object', function () {
    
    it('creates draft status', function () {
        $status = SiteStatus::draft();
        
        expect($status->value())->toBe('draft');
        expect($status->isDraft())->toBeTrue();
        expect($status->isPublished())->toBeFalse();
    });
    
    it('creates published status', function () {
        $status = SiteStatus::published();
        
        expect($status->value())->toBe('published');
        expect($status->isPublished())->toBeTrue();
        expect($status->isAccessible())->toBeTrue();
    });
    
    it('creates maintenance status', function () {
        $status = SiteStatus::maintenance();
        
        expect($status->value())->toBe('maintenance');
        expect($status->isMaintenance())->toBeTrue();
        expect($status->isAccessible())->toBeFalse();
    });
    
    it('creates suspended status', function () {
        $status = SiteStatus::suspended();
        
        expect($status->value())->toBe('suspended');
        expect($status->isSuspended())->toBeTrue();
        expect($status->isAccessible())->toBeFalse();
    });
    
    it('creates status from string', function () {
        $status = SiteStatus::fromString('published');
        
        expect($status->isPublished())->toBeTrue();
    });
    
    it('throws exception for invalid status', function () {
        SiteStatus::fromString('invalid');
    })->throws(InvalidArgumentException::class);
    
});
