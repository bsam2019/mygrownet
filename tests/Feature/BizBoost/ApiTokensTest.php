<?php

namespace Tests\Feature\BizBoost;

use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiTokensTest extends BizBoostTestCase
{
    use RefreshDatabase;

    public function test_can_view_api_tokens_index(): void
    {
        $this->actingAs($this->user)
            ->get('/bizboost/api-tokens')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('BizBoost/Api/Index')
                ->has('tokens')
                ->has('hasApiAccess')
            );
    }

    public function test_business_tier_can_create_api_token(): void
    {
        // Upgrade to business tier
        $this->user->bizboost_tier = 'business';
        $this->user->save();

        $this->actingAs($this->user)
            ->post('/bizboost/api-tokens', [
                'name' => 'My API Token',
                'abilities' => ['read', 'write'],
                'expires_in_days' => 30,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bizboost_api_tokens', [
            'business_id' => $this->business->id,
            'name' => 'My API Token',
        ]);
    }

    public function test_free_tier_cannot_create_api_token(): void
    {
        $this->actingAs($this->user)
            ->post('/bizboost/api-tokens', [
                'name' => 'My API Token',
                'abilities' => ['read'],
            ])
            ->assertSessionHasErrors('access');
    }

    public function test_can_delete_api_token(): void
    {
        $this->user->bizboost_tier = 'business';
        $this->user->save();

        // Create a token
        $tokenId = \DB::table('bizboost_api_tokens')->insertGetId([
            'business_id' => $this->business->id,
            'name' => 'Test Token',
            'token' => hash('sha256', 'test-token'),
            'abilities' => json_encode(['read']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->user)
            ->delete("/bizboost/api-tokens/{$tokenId}")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('bizboost_api_tokens', ['id' => $tokenId]);
    }

    public function test_can_view_api_documentation(): void
    {
        $this->actingAs($this->user)
            ->get('/bizboost/api-tokens/documentation')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('BizBoost/Api/Documentation')
            );
    }

    public function test_token_with_expiry_is_created_correctly(): void
    {
        $this->user->bizboost_tier = 'business';
        $this->user->save();

        $this->actingAs($this->user)
            ->post('/bizboost/api-tokens', [
                'name' => 'Expiring Token',
                'abilities' => ['read'],
                'expires_in_days' => 7,
            ]);

        $token = \DB::table('bizboost_api_tokens')
            ->where('business_id', $this->business->id)
            ->where('name', 'Expiring Token')
            ->first();

        $this->assertNotNull($token->expires_at);
        $this->assertTrue(
            now()->addDays(6)->lt($token->expires_at) &&
            now()->addDays(8)->gt($token->expires_at)
        );
    }
}
