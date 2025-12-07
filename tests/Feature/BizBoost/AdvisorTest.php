<?php

namespace Tests\Feature\BizBoost;

use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostCustomerModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostPostModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostSaleModel;

class AdvisorTest extends BizBoostTestCase
{
    public function test_advisor_index_page_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('bizboost.advisor.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('BizBoost/Advisor/Index')
            ->has('business')
            ->has('recommendations')
            ->has('stats')
        );
    }

    public function test_advisor_chat_returns_response(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('bizboost.advisor.chat'), [
                'message' => 'How can I increase my sales?',
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'response',
            'suggestions',
        ]);
    }

    public function test_advisor_chat_requires_message(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('bizboost.advisor.chat'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['message']);
    }

    public function test_advisor_recommendations_based_on_business_data(): void
    {
        // Create some business activity
        BizBoostCustomerModel::factory()->count(5)->create([
            'business_id' => $this->business->id,
        ]);

        BizBoostPostModel::factory()->count(3)->create([
            'business_id' => $this->business->id,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('bizboost.advisor.recommendations'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'recommendations' => [
                '*' => [
                    'type',
                    'title',
                    'description',
                    'priority',
                    'action',
                ],
            ],
        ]);
    }

    public function test_advisor_generates_growth_insights(): void
    {
        // Create sales data for insights
        BizBoostSaleModel::factory()->count(10)->create([
            'business_id' => $this->business->id,
            'created_at' => now()->subDays(rand(1, 30)),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('bizboost.advisor.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('stats.total_sales')
            ->has('stats.total_customers')
            ->has('stats.total_posts')
        );
    }

    public function test_advisor_requires_authentication(): void
    {
        $response = $this->get(route('bizboost.advisor.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_advisor_requires_business_setup(): void
    {
        // Delete the business
        $this->business->delete();

        $response = $this->actingAs($this->user)
            ->get(route('bizboost.advisor.index'));

        $response->assertRedirect(route('bizboost.setup.index'));
    }
}
