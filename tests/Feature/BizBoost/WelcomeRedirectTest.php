<?php

use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('welcome page is accessible without authentication', function () {
    $response = $this->get('/bizboost/welcome');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('BizBoost/Welcome'));
});

test('login page stores redirect parameter in session', function () {
    $response = $this->get('/login?redirect=/bizboost');

    $response->assertStatus(200);
    $response->assertSessionHas('url.intended', url('/bizboost'));
});

test('register page stores redirect parameter in session', function () {
    $response = $this->get('/register?redirect=/bizboost');

    $response->assertStatus(200);
    $response->assertSessionHas('url.intended', url('/bizboost'));
});

test('login redirects to bizboost when redirect parameter is set', function () {
    $user = User::factory()->create();

    // First visit login with redirect parameter to set session
    $this->get('/login?redirect=/bizboost');

    // Then login
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/bizboost');
});

test('registration redirects to bizboost when redirect parameter is set', function () {
    // First visit register with redirect parameter to set session
    $this->get('/register?redirect=/bizboost');

    // Then register
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/bizboost');
});

test('bizboost dashboard redirects to setup for new users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/bizboost');

    $response->assertRedirect(route('bizboost.setup'));
});

test('bizboost dashboard redirects to setup when onboarding not complete', function () {
    $user = User::factory()->create();
    
    // Create business but don't complete onboarding
    BizBoostBusinessModel::create([
        'user_id' => $user->id,
        'name' => 'Test Business',
        'slug' => 'test-business',
        'onboarding_completed' => false,
    ]);

    $response = $this->actingAs($user)->get('/bizboost');

    $response->assertRedirect(route('bizboost.setup'));
});

test('bizboost dashboard loads for users with completed setup', function () {
    $user = User::factory()->create();
    
    // Create business with completed onboarding
    BizBoostBusinessModel::create([
        'user_id' => $user->id,
        'name' => 'Test Business',
        'slug' => 'test-business-complete',
        'onboarding_completed' => true,
    ]);

    $response = $this->actingAs($user)->get('/bizboost');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('BizBoost/Dashboard'));
});

test('redirect parameter only accepts internal URLs', function () {
    // External URL should not be stored
    $response = $this->get('/login?redirect=https://evil.com');

    $response->assertStatus(200);
    $response->assertSessionMissing('url.intended');
});

test('redirect parameter only accepts paths starting with slash', function () {
    // Relative path without leading slash should not be stored
    $response = $this->get('/login?redirect=bizboost');

    $response->assertStatus(200);
    $response->assertSessionMissing('url.intended');
});
