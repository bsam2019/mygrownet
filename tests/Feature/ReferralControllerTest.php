<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\InvestmentTier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReferralControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create basic investment tier for testing
        InvestmentTier::updateOrCreate(
            ['name' => 'Basic'],
            [
                'minimum_investment' => 500,
                'fixed_profit_rate' => 3.0,
                'direct_referral_rate' => 5.0,
                'level2_referral_rate' => 0.0,
                'level3_referral_rate' => 0.0,
                'is_active' => true,
                'order' => 1,
            ]
        );
    }

    /** @test */
    public function referral_dashboard_requires_authentication()
    {
        $response = $this->get(route('referrals.index'));
        $response->assertStatus(302); // Redirect to login
    }

    /** @test */
    public function authenticated_user_can_access_referral_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->get(route('referrals.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function can_get_referral_tree_data()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->getJson(route('referrals.tree'));
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'tree',
                    'statistics',
                    'max_level'
                ]
            ]);
    }

    /** @test */
    public function can_get_referral_statistics()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->getJson(route('referrals.statistics'));
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'referral_statistics',
                    'matrix_performance',
                    'earnings_breakdown'
                ]
            ]);
    }

    /** @test */
    public function can_get_commission_history()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->getJson(route('referrals.commissions'));
        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data']);
    }

    /** @test */
    public function can_generate_referral_code()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->postJson(route('referrals.generate-code'));
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'referral_code',
                    'referral_link',
                    'message'
                ]
            ]);
    }

    /** @test */
    public function can_validate_referral_code()
    {
        $referrer = User::factory()->create(['referral_code' => 'TEST123']);
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->postJson(route('referrals.validate-code'), [
            'referral_code' => 'TEST123'
        ]);
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['is_valid']
            ]);
    }

    /** @test */
    public function referral_code_validation_requires_code()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->postJson(route('referrals.validate-code'), []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['referral_code']);
    }

    /** @test */
    public function can_get_matrix_position_data()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->getJson(route('referrals.matrix-position'));
        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data']);
    }

    /** @test */
    public function can_get_matrix_genealogy_report()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->getJson(route('referrals.matrix-genealogy'));
        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data']);
    }

    /** @test */
    public function can_get_referrals_by_level()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->getJson(route('referrals.by-level', ['level' => 1]));
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'level',
                    'referrals',
                    'count'
                ]
            ]);
    }

    /** @test */
    public function referrals_by_level_validates_level_parameter()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Test missing level
        $response = $this->getJson(route('referrals.by-level'));
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['level']);
        
        // Test invalid level
        $response = $this->getJson(route('referrals.by-level', ['level' => 5]));
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['level']);
    }

    /** @test */
    public function can_calculate_commission_for_investment()
    {
        $user = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        $this->actingAs($user);
        
        $response = $this->postJson(route('referrals.calculate-commission'), [
            'investment_amount' => 1000,
            'tier_id' => $tier->id
        ]);
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'investment_amount',
                    'tier',
                    'referral_commissions'
                ]
            ]);
    }

    /** @test */
    public function commission_calculation_validates_parameters()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Test missing investment amount
        $response = $this->postJson(route('referrals.calculate-commission'), []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['investment_amount']);
        
        // Test invalid investment amount
        $response = $this->postJson(route('referrals.calculate-commission'), [
            'investment_amount' => 100
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['investment_amount']);
    }

    /** @test */
    public function can_get_performance_report()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->getJson(route('referrals.performance-report'));
        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data']);
    }

    /** @test */
    public function can_export_referral_data()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->postJson(route('referrals.export'), [
            'format' => 'csv',
            'type' => 'referrals'
        ]);
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'message'
            ]);
    }

    /** @test */
    public function export_validates_parameters()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Test missing parameters
        $response = $this->postJson(route('referrals.export'), []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['format', 'type']);
        
        // Test invalid format
        $response = $this->postJson(route('referrals.export'), [
            'format' => 'invalid',
            'type' => 'referrals'
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['format']);
    }

    /** @test */
    public function all_endpoints_require_authentication()
    {
        $endpoints = [
            ['GET', route('referrals.index')],
            ['GET', route('referrals.tree')],
            ['GET', route('referrals.statistics')],
            ['GET', route('referrals.commissions')],
            ['GET', route('referrals.matrix-position')],
            ['POST', route('referrals.generate-code')],
            ['POST', route('referrals.validate-code')],
            ['POST', route('referrals.calculate-commission')],
            ['POST', route('referrals.export')]
        ];
        
        foreach ($endpoints as [$method, $url]) {
            $response = $method === 'GET' 
                ? $this->get($url)
                : $this->postJson($url, []);
            
            $response->assertStatus(302); // Redirect to login
        }
    }
}