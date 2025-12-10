<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PublicRoutesTest extends TestCase
{
    /**
     * Test that new public routes are accessible
     */
    public function test_new_public_routes_are_accessible(): void
    {
        // Test new public pages
        $response = $this->get('/starter-kits');
        $response->assertStatus(200);
        
        $response = $this->get('/training');
        $response->assertStatus(200);
        
        $response = $this->get('/rewards');
        $response->assertStatus(200);
        
        $response = $this->get('/roadmap');
        $response->assertStatus(200);
        
        $response = $this->get('/how-we-operate');
        $response->assertStatus(200);
    }
    
    /**
     * Test that old URLs redirect to new ones
     */
    public function test_old_urls_redirect_to_new_ones(): void
    {
        // Test 301 redirects
        $response = $this->get('/investment');
        $response->assertRedirect('/starter-kits');
        $response->assertStatus(301);
        
        $response = $this->get('/join');
        $response->assertRedirect('/starter-kits');
        $response->assertStatus(301);
        
        $response = $this->get('/packages');
        $response->assertRedirect('/starter-kits');
        $response->assertStatus(301);
        
        $response = $this->get('/shop');
        $response->assertRedirect('/marketplace');
        $response->assertStatus(301);
        
        $response = $this->get('/learn');
        $response->assertRedirect('/training');
        $response->assertStatus(301);
        
        $response = $this->get('/learning');
        $response->assertRedirect('/training');
        $response->assertStatus(301);
        
        $response = $this->get('/loyalty');
        $response->assertRedirect('/rewards');
        $response->assertStatus(301);
        
        $response = $this->get('/vision');
        $response->assertRedirect('/roadmap');
        $response->assertStatus(301);
    }
    
    /**
     * Test that home route exists and requires authentication
     */
    public function test_home_route_requires_authentication(): void
    {
        $response = $this->get('/home');
        $response->assertRedirect('/login');
    }
}
