<?php

namespace Tests\Feature\GrowBuilder;

use App\Infrastructure\GrowBuilder\Models\GrowBuilderPage;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Infrastructure\GrowBuilder\Models\SitePermission;
use App\Infrastructure\GrowBuilder\Models\SiteRole;
use App\Infrastructure\GrowBuilder\Models\SiteUser;
use App\Infrastructure\GrowBuilder\Models\SiteUserSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MemberPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private GrowBuilderSite $site;
    private SiteUser $siteUser;
    private array $authCookies = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create();
        $this->site = GrowBuilderSite::create([
            'user_id' => $this->owner->id,
            'name' => 'Portal',
            'subdomain' => 'portal-' . uniqid(),
            'plan' => 'free',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $role = SiteRole::firstOrCreate(
            ['site_id' => $this->site->id, 'slug' => 'member'],
            ['name' => 'Member', 'is_system' => true]
        );

        // Create permission records and attach them to the role
        $permSlugs = ['member.access', 'member.content', 'users.view', 'posts.view', 'messages.view', 'products.view', 'analytics.view', 'settings.view'];
        foreach ($permSlugs as $slug) {
            $perm = SitePermission::firstOrCreate(
                ['slug' => $slug],
                ['name' => $slug, 'description' => $slug, 'group_name' => 'general']
            );
            $role->permissions()->syncWithoutDetaching([$perm->id]);
        }

        $this->siteUser = SiteUser::create([
            'site_id' => $this->site->id,
            'role_id' => $role->id,
            'name' => 'Test Member',
            'email' => 'member@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        // Login and capture session cookie
        $loginResponse = $this->post("/sites/{$this->site->subdomain}/login", [
            'email' => 'member@example.com',
            'password' => 'password123',
        ]);
        foreach ($loginResponse->headers->getCookies() as $c) {
            $this->authCookies[$c->getName()] = $c->getValue();
        }
    }

    public function test_show_login_page(): void
    {
        $response = $this->get("/sites/{$this->site->subdomain}/login");
        $response->assertStatus(200);
    }

    public function test_show_register_page(): void
    {
        $response = $this->get("/sites/{$this->site->subdomain}/register");
        $response->assertStatus(200);
    }

    public function test_register_new_user(): void
    {
        $response = $this->post("/sites/{$this->site->subdomain}/register", [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertContains($response->status(), [200, 302]);
        $this->assertDatabaseHas('site_users', [
            'site_id' => $this->site->id,
            'email' => 'new@example.com',
        ]);
    }

    public function test_member_dashboard_requires_auth(): void
    {
        $response = $this->get("/sites/{$this->site->subdomain}/dashboard");
        $response->assertRedirect("/sites/{$this->site->subdomain}/login");
    }

    public function test_member_dashboard_loads(): void
    {
        $response = $this->call('GET', "/sites/{$this->site->subdomain}/dashboard", [], $this->authCookies);
        $response->assertStatus(200);
    }

    public function test_member_profile_page(): void
    {
        $response = $this->call('GET', "/sites/{$this->site->subdomain}/dashboard/profile", [], $this->authCookies);
        $response->assertStatus(200);
    }

    public function test_member_update_profile(): void
    {
        $response = $this->call('PUT', "/sites/{$this->site->subdomain}/dashboard/profile", [
            'name' => 'Updated Name',
        ], $this->authCookies);

        $response->assertStatus(302);
        $this->siteUser->refresh();
        $this->assertEquals('Updated Name', $this->siteUser->name);
    }

    public function test_member_orders_page(): void
    {
        $response = $this->call('GET', "/sites/{$this->site->subdomain}/dashboard/orders", [], $this->authCookies);
        $response->assertStatus(200);
    }

    public function test_member_analytics_page(): void
    {
        $response = $this->call('GET', "/sites/{$this->site->subdomain}/dashboard/analytics", [], $this->authCookies);
        $this->assertContains($response->status(), [200, 403]);
    }

    public function test_member_settings_page(): void
    {
        $response = $this->call('GET', "/sites/{$this->site->subdomain}/dashboard/settings", [], $this->authCookies);
        $this->assertContains($response->status(), [200, 403]);
    }

    public function test_member_users_page(): void
    {
        $response = $this->call('GET', "/sites/{$this->site->subdomain}/dashboard/users", [], $this->authCookies);
        $this->assertContains($response->status(), [200, 403]);
    }

    public function test_member_posts_page(): void
    {
        $response = $this->call('GET', "/sites/{$this->site->subdomain}/dashboard/posts", [], $this->authCookies);
        $this->assertContains($response->status(), [200, 403]);
    }

    public function test_member_products_page(): void
    {
        $response = $this->call('GET', "/sites/{$this->site->subdomain}/dashboard/products", [], $this->authCookies);
        $this->assertContains($response->status(), [200, 403]);
    }

    public function test_member_messages_page(): void
    {
        $response = $this->call('GET', "/sites/{$this->site->subdomain}/dashboard/messages", [], $this->authCookies);
        $this->assertContains($response->status(), [200, 403]);
    }

    public function test_logout(): void
    {
        $response = $this->call('POST', "/sites/{$this->site->subdomain}/logout", [], $this->authCookies);
        $response->assertStatus(302);

        $response = $this->get("/sites/{$this->site->subdomain}/dashboard");
        $response->assertRedirect("/sites/{$this->site->subdomain}/login");
    }

    public function test_forgot_password_page(): void
    {
        $response = $this->get("/sites/{$this->site->subdomain}/forgot-password");
        $response->assertStatus(200);
    }
}
