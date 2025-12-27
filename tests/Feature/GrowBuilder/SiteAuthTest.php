<?php

declare(strict_types=1);

use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderPage;
use App\Infrastructure\GrowBuilder\Models\SiteUser;
use App\Infrastructure\GrowBuilder\Models\SiteRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->owner = User::factory()->create();
    
    $this->site = GrowBuilderSite::create([
        'user_id' => $this->owner->id,
        'name' => 'Test Site',
        'subdomain' => 'testsite',
        'status' => 'published',
        'plan' => 'free',
        'published_at' => now(),
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
    
    // Create member role
    $this->memberRole = SiteRole::firstOrCreate(
        ['site_id' => $this->site->id, 'slug' => 'member'],
        [
            'name' => 'Member',
            'permissions' => ['member.access', 'member.content', 'member.profile'],
            'is_system' => true,
        ]
    );
});

describe('Site User Registration', function () {
    
    it('shows registration page for published site', function () {
        $response = $this->get("/sites/{$this->site->subdomain}/register");
        
        $response->assertStatus(200);
    });
    
    it('allows new user to register on site', function () {
        $response = $this->post("/sites/{$this->site->subdomain}/register", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('site_users', [
            'site_id' => $this->site->id,
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);
    });
    
    it('prevents duplicate email registration on same site', function () {
        SiteUser::create([
            'site_id' => $this->site->id,
            'role_id' => $this->memberRole->id,
            'name' => 'Existing User',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
        ]);
        
        $response = $this->post("/sites/{$this->site->subdomain}/register", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        
        $response->assertSessionHasErrors('email');
    });
    
    it('validates required fields on registration', function () {
        $response = $this->post("/sites/{$this->site->subdomain}/register", []);
        
        $response->assertSessionHasErrors(['name', 'email', 'password']);
    });
    
});

describe('Site User Login', function () {
    
    beforeEach(function () {
        $this->siteUser = SiteUser::create([
            'site_id' => $this->site->id,
            'role_id' => $this->memberRole->id,
            'name' => 'Test Member',
            'email' => 'member@example.com',
            'password' => Hash::make('password123'),
        ]);
    });
    
    it('shows login page for published site', function () {
        $response = $this->get("/sites/{$this->site->subdomain}/login");
        
        $response->assertStatus(200);
    });
    
    it('allows site user to login with correct credentials', function () {
        $response = $this->post("/sites/{$this->site->subdomain}/login", [
            'email' => 'member@example.com',
            'password' => 'password123',
        ]);
        
        $response->assertRedirect("/sites/{$this->site->subdomain}/dashboard");
    });
    
    it('rejects login with incorrect password', function () {
        $response = $this->post("/sites/{$this->site->subdomain}/login", [
            'email' => 'member@example.com',
            'password' => 'wrongpassword',
        ]);
        
        $response->assertSessionHasErrors('email');
    });
    
    it('rejects login for non-existent user', function () {
        $response = $this->post("/sites/{$this->site->subdomain}/login", [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);
        
        $response->assertSessionHasErrors('email');
    });
    
});

describe('Site Member Area Access', function () {
    
    beforeEach(function () {
        $this->siteUser = SiteUser::create([
            'site_id' => $this->site->id,
            'role_id' => $this->memberRole->id,
            'name' => 'Test Member',
            'email' => 'member@example.com',
            'password' => Hash::make('password123'),
        ]);
    });
    
    it('redirects unauthenticated user to login', function () {
        $response = $this->get("/sites/{$this->site->subdomain}/dashboard");
        
        $response->assertRedirect("/sites/{$this->site->subdomain}/login");
    });
    
    it('allows authenticated site user to access member area', function () {
        // Login first
        $this->post("/sites/{$this->site->subdomain}/login", [
            'email' => 'member@example.com',
            'password' => 'password123',
        ]);
        
        $response = $this->get("/sites/{$this->site->subdomain}/dashboard");
        
        $response->assertStatus(200);
    });
    
    it('allows site user to logout', function () {
        // Login first
        $this->post("/sites/{$this->site->subdomain}/login", [
            'email' => 'member@example.com',
            'password' => 'password123',
        ]);
        
        $response = $this->post("/sites/{$this->site->subdomain}/logout");
        
        $response->assertRedirect();
        
        // Should not be able to access member area
        $response = $this->get("/sites/{$this->site->subdomain}/dashboard");
        $response->assertRedirect("/sites/{$this->site->subdomain}/login");
    });
    
});

describe('Site User Profile', function () {
    
    beforeEach(function () {
        $this->siteUser = SiteUser::create([
            'site_id' => $this->site->id,
            'role_id' => $this->memberRole->id,
            'name' => 'Test Member',
            'email' => 'member@example.com',
            'password' => Hash::make('password123'),
        ]);
        
        // Login
        $this->post("/sites/{$this->site->subdomain}/login", [
            'email' => 'member@example.com',
            'password' => 'password123',
        ]);
    });
    
    it('allows user to view their profile', function () {
        $response = $this->get("/sites/{$this->site->subdomain}/dashboard/profile");
        
        $response->assertStatus(200);
    });
    
    it('allows user to update their profile', function () {
        $response = $this->put("/sites/{$this->site->subdomain}/dashboard/profile", [
            'name' => 'Updated Name',
            'phone' => '+260977123456',
        ]);
        
        $response->assertRedirect();
        
        $this->siteUser->refresh();
        expect($this->siteUser->name)->toBe('Updated Name');
    });
    
});
