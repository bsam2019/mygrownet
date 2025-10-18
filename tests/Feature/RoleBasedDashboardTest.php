<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleBasedDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // No need to create roles for the simplified approach
    }

    public function test_admin_user_redirected_to_admin_dashboard()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_manager_user_redirected_to_manager_dashboard()
    {
        $manager = User::factory()->create(['rank' => 'manager']);

        $response = $this->actingAs($manager)->get('/dashboard');

        $response->assertRedirect(route('manager.dashboard'));
    }

    public function test_investor_user_redirected_to_investor_dashboard()
    {
        $investor = User::factory()->create(['rank' => 'starter']);

        $response = $this->actingAs($investor)->get('/dashboard');

        $response->assertRedirect(route('investor.dashboard'));
    }

    public function test_user_without_role_redirected_to_investor_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('investor.dashboard'));
    }

    public function test_admin_can_access_admin_dashboard()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    public function test_manager_can_access_manager_dashboard()
    {
        $manager = User::factory()->create(['rank' => 'manager']);

        $response = $this->actingAs($manager)->get(route('manager.dashboard'));

        $response->assertStatus(200);
    }

    public function test_investor_cannot_access_admin_dashboard()
    {
        $investor = User::factory()->create(['rank' => 'starter']);

        $response = $this->actingAs($investor)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_investor_cannot_access_manager_dashboard()
    {
        $investor = User::factory()->create(['rank' => 'starter']);

        $response = $this->actingAs($investor)->get(route('manager.dashboard'));

        $response->assertStatus(403);
    }

    public function test_is_admin_field_grants_admin_access()
    {
        $user = User::factory()->create(['is_admin' => true]);
        // Don't assign admin role, just use is_admin field

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }
}