<?php

namespace Tests\Feature\BizBoost;

use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;

class DashboardTest extends BizBoostTestCase
{
    public function test_dashboard_redirects_to_setup_when_no_business(): void
    {
        // Create user without business
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/bizboost');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('BizBoost/Setup/Index'));
    }

    public function test_dashboard_redirects_to_setup_when_onboarding_incomplete(): void
    {
        $this->business->update(['onboarding_completed' => false]);

        $response = $this->actingAsUser()->get('/bizboost');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('BizBoost/Setup/Index')
            ->has('business')
        );
    }

    public function test_dashboard_shows_when_onboarding_complete(): void
    {
        $response = $this->actingAsUser()->get('/bizboost');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('BizBoost/Dashboard')
            ->has('business')
            ->has('stats')
            ->has('recentPosts')
            ->has('upcomingPosts')
            ->has('recentSales')
            ->has('topProducts')
        );
    }

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/bizboost');

        $response->assertRedirect('/login');
    }

    public function test_dashboard_shows_correct_stats(): void
    {
        // Create some test data
        $this->createProduct(['name' => 'Product 1', 'price' => 100]);
        $this->createProduct(['name' => 'Product 2', 'price' => 200]);
        $this->createCustomer(['name' => 'Customer 1']);
        $this->createPost(['caption' => 'Test post', 'status' => 'published', 'published_at' => now()]);

        $response = $this->actingAsUser()->get('/bizboost');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('BizBoost/Dashboard')
            ->where('stats.products.total', 2)
            ->where('stats.customers.total', 1)
        );
    }
}
