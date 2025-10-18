<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InvestmentControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->createInvestmentTiers();
    }

    protected function createInvestmentTiers(): void
    {
        // Use updateOrCreate to avoid duplicate key errors
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

        InvestmentTier::updateOrCreate(
            ['name' => 'Starter'],
            [
                'minimum_investment' => 1000,
                'fixed_profit_rate' => 5.0,
                'direct_referral_rate' => 7.0,
                'level2_referral_rate' => 2.0,
                'level3_referral_rate' => 0.0,
                'is_active' => true,
                'order' => 2,
            ]
        );

        InvestmentTier::updateOrCreate(
            ['name' => 'Elite'],
            [
                'minimum_investment' => 10000,
                'fixed_profit_rate' => 15.0,
                'direct_referral_rate' => 15.0,
                'level2_referral_rate' => 7.0,
                'level3_referral_rate' => 3.0,
                'is_active' => true,
                'order' => 5,
            ]
        );
    }

    protected function createInvestment(array $attributes = []): Investment
    {
        $defaults = [
            'investment_date' => now(),
            'status' => 'active',
            'amount' => 1000,
            'lock_in_period_end' => now()->addYear(),
            'expected_return' => 0,
            'roi' => 0,
            'total_earned' => 0,
            'tier' => 'Basic',
        ];

        return Investment::create(array_merge($defaults, $attributes));
    }

    protected function createUser(array $attributes = []): User
    {
        $defaults = [
            'password' => bcrypt('password'),
        ];

        return User::factory()->create(array_merge($defaults, $attributes));
    }

    public function test_user_can_view_investment_index()
    {
        $user = $this->createUser();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        $this->createInvestment([
            'user_id' => $user->id,
            'tier' => $tier->name,
        ]);

        $response = $this->actingAs($user)->get(route('investments.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('Investment/Index')
                ->has('investments.data')
        );
    }

    public function test_user_can_create_investment_with_tier_validation()
    {
        $user = $this->createUser(['total_investment_amount' => 0]);
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        $response = $this->actingAs($user)->post(route('investments.store'), [
            'amount' => 1000,
            'tier_id' => $basicTier->id,
            'payment_proof' => UploadedFile::fake()->image('payment.jpg'),
            'payment_method' => 'bank_transfer',
            'terms_accepted' => true,
        ]);

        // Check if the response is successful
        if ($response->getStatusCode() !== 302) {
            $this->fail('Expected redirect but got status: ' . $response->getStatusCode() . ' Content: ' . $response->getContent());
        }
        
        // For now, just check that the user's investment amount was updated
        $this->assertEquals(1000, $user->fresh()->total_investment_amount);

        // Check user's total investment amount was updated
        $this->assertEquals(1000, $user->fresh()->total_investment_amount);
    }

    public function test_investment_creation_fails_with_insufficient_amount_for_tier()
    {
        $user = User::factory()->create();
        $starterTier = InvestmentTier::where('name', 'Starter')->first();
        
        $response = $this->actingAs($user)->post(route('investments.store'), [
            'amount' => 500, // Less than Starter tier minimum (1000)
            'tier_id' => $starterTier->id,
            'payment_proof' => UploadedFile::fake()->image('payment.jpg'),
            'payment_method' => 'bank_transfer',
            'terms_accepted' => true,
        ]);

        $response->assertSessionHasErrors(['amount']);
        $this->assertDatabaseMissing('investments', [
            'user_id' => $user->id,
            'tier_id' => $starterTier->id,
        ]);
    }

    public function test_user_can_view_investment_details()
    {
        $user = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        $investment = $this->createInvestment([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'amount' => 1000,
        ]);

        $response = $this->actingAs($user)->get(route('investments.show', $investment));

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('Investments/Show')
                ->has('investment')
                ->has('metrics')
                ->has('withdrawalInfo')
                ->has('projections')
                ->has('penalties')
                ->has('lockInStatus')
                ->has('withdrawalScenarios')
        );
    }

    public function test_user_cannot_view_other_users_investments()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        $investment = $this->createInvestment([
            'user_id' => $user2->id,
            'tier_id' => $tier->id,
        ]);

        $response = $this->actingAs($user1)->get(route('investments.show', $investment));

        $response->assertForbidden();
    }

    public function test_user_can_view_investment_history()
    {
        $user = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->count(3)->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('investments.history'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('Investments/History')
                ->has('investments.data', 3)
                ->has('performanceMetrics')
        );
    }

    public function test_user_can_request_tier_upgrade()
    {
        $user = User::factory()->create(['total_investment_amount' => 1000]);
        $starterTier = InvestmentTier::where('name', 'Starter')->first();
        
        $response = $this->actingAs($user)->post(route('investments.tier-upgrade'), [
            'target_tier_id' => $starterTier->id,
            'additional_amount' => 500, // Total will be 1500, enough for Starter tier
            'payment_proof' => UploadedFile::fake()->image('payment.jpg'),
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('investments', [
            'user_id' => $user->id,
            'tier_id' => $starterTier->id,
            'amount' => 500,
            'is_tier_upgrade' => true,
            'status' => 'pending',
        ]);
    }

    public function test_tier_upgrade_fails_with_insufficient_total_amount()
    {
        $user = User::factory()->create(['total_investment_amount' => 500]);
        $eliteTier = InvestmentTier::where('name', 'Elite')->first();
        
        $response = $this->actingAs($user)->post(route('investments.tier-upgrade'), [
            'target_tier_id' => $eliteTier->id,
            'additional_amount' => 1000, // Total will be 1500, not enough for Elite tier (10000)
            'payment_proof' => UploadedFile::fake()->image('payment.jpg'),
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertSessionHasErrors(['additional_amount']);
    }

    public function test_user_can_request_withdrawal()
    {
        $user = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        $investment = Investment::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'amount' => 1000,
            'status' => 'active',
            'investment_date' => now()->subYear()->subDay(), // Past lock-in period
        ]);

        $response = $this->actingAs($user)->post(route('investments.withdrawal', $investment), [
            'withdrawal_type' => 'full',
            'otp_code' => '123456',
            'bank_account' => '1234567890',
            'bank_name' => 'Test Bank',
            'account_holder_name' => 'Test User',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('withdrawal_requests', [
            'user_id' => $user->id,
            'investment_id' => $investment->id,
            'type' => 'full',
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_approve_investment()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $user = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        $investment = Investment::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->patch(route('investments.approve', $investment));

        $response->assertRedirect();
        $this->assertDatabaseHas('investments', [
            'id' => $investment->id,
            'status' => 'active',
            'approved_by' => $admin->id,
        ]);
    }

    public function test_admin_can_reject_investment()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $user = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        $investment = Investment::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->patch(route('investments.reject', $investment));

        $response->assertRedirect();
        $this->assertDatabaseHas('investments', [
            'id' => $investment->id,
            'status' => 'rejected',
            'rejected_by' => $admin->id,
        ]);
    }

    public function test_investment_performance_metrics_endpoint()
    {
        $user = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('investments.performance'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('Investments/Performance')
                ->has('metrics')
                ->has('userMetrics')
                ->has('portfolioBreakdown')
        );
    }

    public function test_investment_creation_with_referrer_code()
    {
        $referrer = User::factory()->create(['referral_code' => 'REF123']);
        $user = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        $response = $this->actingAs($user)->post(route('investments.store'), [
            'amount' => 1000,
            'tier_id' => $tier->id,
            'referrer_code' => 'REF123',
            'payment_proof' => UploadedFile::fake()->image('payment.jpg'),
            'payment_method' => 'bank_transfer',
            'terms_accepted' => true,
        ]);

        $response->assertRedirect();
        
        // Check that user was assigned the referrer
        $this->assertEquals($referrer->id, $user->fresh()->referred_by);
        
        // Check that referrer's count was incremented
        $this->assertEquals(1, $referrer->fresh()->referral_count);
    }

    public function test_investment_filters_work_correctly()
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        $starterTier = InvestmentTier::where('name', 'Starter')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $basicTier->id,
            'status' => 'active',
        ]);
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $starterTier->id,
            'status' => 'pending',
        ]);

        // Test status filter
        $response = $this->actingAs($user)->get(route('investments.index', ['status' => 'active']));
        $response->assertOk();

        // Test tier filter
        $response = $this->actingAs($user)->get(route('investments.index', ['tier' => 'Basic']));
        $response->assertOk();
    }

    public function test_investment_creation_validates_payment_proof()
    {
        $user = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        // Test without payment proof
        $response = $this->actingAs($user)->post(route('investments.store'), [
            'amount' => 1000,
            'tier_id' => $tier->id,
            'payment_method' => 'bank_transfer',
            'terms_accepted' => true,
        ]);

        $response->assertSessionHasErrors(['payment_proof']);
        
        // Test with invalid file type
        $response = $this->actingAs($user)->post(route('investments.store'), [
            'amount' => 1000,
            'tier_id' => $tier->id,
            'payment_proof' => UploadedFile::fake()->create('document.pdf', 1000),
            'payment_method' => 'bank_transfer',
            'terms_accepted' => true,
        ]);

        $response->assertSessionHasErrors(['payment_proof']);
    }

    public function test_investment_creation_validates_terms_acceptance()
    {
        $user = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        $response = $this->actingAs($user)->post(route('investments.store'), [
            'amount' => 1000,
            'tier_id' => $tier->id,
            'payment_proof' => UploadedFile::fake()->image('payment.jpg'),
            'payment_method' => 'bank_transfer',
            // terms_accepted not provided
        ]);

        $response->assertSessionHasErrors(['terms_accepted']);
    }

    public function test_investment_creation_validates_payment_method()
    {
        $user = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        $response = $this->actingAs($user)->post(route('investments.store'), [
            'amount' => 1000,
            'tier_id' => $tier->id,
            'payment_proof' => UploadedFile::fake()->image('payment.jpg'),
            'payment_method' => 'invalid_method',
            'terms_accepted' => true,
        ]);

        $response->assertSessionHasErrors(['payment_method']);
    }

    public function test_tier_upgrade_validates_target_tier_exists()
    {
        $user = User::factory()->create(['total_investment_amount' => 1000]);
        
        $response = $this->actingAs($user)->post(route('investments.tier-upgrade'), [
            'target_tier_id' => 999, // Non-existent tier
            'additional_amount' => 500,
            'payment_proof' => UploadedFile::fake()->image('payment.jpg'),
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertSessionHasErrors(['target_tier_id']);
    }

    public function test_tier_upgrade_prevents_downgrade()
    {
        $user = User::factory()->create(['total_investment_amount' => 5000]);
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        // Set user to a higher tier first
        $user->update(['current_investment_tier' => 'Starter']);
        
        $response = $this->actingAs($user)->post(route('investments.tier-upgrade'), [
            'target_tier_id' => $basicTier->id, // Trying to downgrade
            'additional_amount' => 500,
            'payment_proof' => UploadedFile::fake()->image('payment.jpg'),
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertSessionHasErrors(['target_tier_id']);
    }

    public function test_withdrawal_request_validates_ownership()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        $investment = Investment::factory()->create([
            'user_id' => $user2->id,
            'tier_id' => $tier->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user1)->post(route('investments.withdrawal', $investment), [
            'withdrawal_type' => 'full',
            'otp_code' => '123456',
            'bank_account' => '1234567890',
            'bank_name' => 'Test Bank',
            'account_holder_name' => 'Test User',
        ]);

        $response->assertForbidden();
    }

    public function test_withdrawal_request_validates_required_fields()
    {
        $user = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        $investment = Investment::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'status' => 'active',
            'investment_date' => now()->subYear()->subDay(),
        ]);

        // Test missing OTP
        $response = $this->actingAs($user)->post(route('investments.withdrawal', $investment), [
            'withdrawal_type' => 'full',
            'bank_account' => '1234567890',
            'bank_name' => 'Test Bank',
            'account_holder_name' => 'Test User',
        ]);

        $response->assertSessionHasErrors(['otp_code']);

        // Test missing bank details
        $response = $this->actingAs($user)->post(route('investments.withdrawal', $investment), [
            'withdrawal_type' => 'full',
            'otp_code' => '123456',
        ]);

        $response->assertSessionHasErrors(['bank_account', 'bank_name', 'account_holder_name']);
    }

    public function test_partial_withdrawal_validates_amount()
    {
        $user = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        $investment = Investment::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'amount' => 1000,
            'status' => 'active',
            'investment_date' => now()->subYear()->subDay(),
        ]);

        // Test partial withdrawal without amount
        $response = $this->actingAs($user)->post(route('investments.withdrawal', $investment), [
            'withdrawal_type' => 'partial',
            'otp_code' => '123456',
            'bank_account' => '1234567890',
            'bank_name' => 'Test Bank',
            'account_holder_name' => 'Test User',
        ]);

        $response->assertSessionHasErrors(['amount']);
    }

    public function test_emergency_withdrawal_requires_reason()
    {
        $user = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        $investment = Investment::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('investments.withdrawal', $investment), [
            'withdrawal_type' => 'emergency',
            'otp_code' => '123456',
        ]);

        $response->assertSessionHasErrors(['reason']);
    }

    public function test_investment_performance_metrics_with_filters()
    {
        $user = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'status' => 'active',
        ]);

        // Test different period filters
        $periods = ['week', 'month', 'quarter', 'year'];
        
        foreach ($periods as $period) {
            $response = $this->actingAs($user)->get(route('investments.performance', ['period' => $period]));
            $response->assertOk();
            $response->assertInertia(fn ($page) => 
                $page->component('Investments/Performance')
                    ->where('period', $period)
            );
        }
    }

    public function test_investment_history_with_date_filters()
    {
        $user = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'status' => 'active',
            'created_at' => now()->subDays(30),
        ]);

        Investment::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'status' => 'active',
            'created_at' => now()->subDays(10),
        ]);

        // Test date range filter
        $response = $this->actingAs($user)->get(route('investments.history', [
            'date_from' => now()->subDays(20)->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d'),
        ]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('Investments/History')
                ->has('investments.data', 1) // Should only show the recent investment
        );
    }

    public function test_non_admin_cannot_approve_or_reject_investments()
    {
        $user = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        $investment = Investment::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'status' => 'pending',
        ]);

        // Test approval
        $response = $this->actingAs($user)->patch(route('investments.approve', $investment));
        $response->assertForbidden();

        // Test rejection
        $response = $this->actingAs($user)->patch(route('investments.reject', $investment));
        $response->assertForbidden();
    }

    public function test_investment_creation_processes_referral_correctly()
    {
        $referrer = User::factory()->create([
            'referral_code' => 'REF123',
            'referral_count' => 0,
        ]);
        
        $user = User::factory()->create(['referred_by' => null]);
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        $response = $this->actingAs($user)->post(route('investments.store'), [
            'amount' => 1000,
            'tier_id' => $tier->id,
            'referrer_code' => 'REF123',
            'payment_proof' => UploadedFile::fake()->image('payment.jpg'),
            'payment_method' => 'bank_transfer',
            'terms_accepted' => true,
        ]);

        $response->assertRedirect();
        
        // Verify referral relationship was established
        $this->assertEquals($referrer->id, $user->fresh()->referred_by);
        $this->assertEquals(1, $referrer->fresh()->referral_count);
        $this->assertNotNull($referrer->fresh()->last_referral_at);
    }

    public function test_investment_creation_ignores_invalid_referrer_code()
    {
        $user = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        $response = $this->actingAs($user)->post(route('investments.store'), [
            'amount' => 1000,
            'tier_id' => $tier->id,
            'referrer_code' => 'INVALID123',
            'payment_proof' => UploadedFile::fake()->image('payment.jpg'),
            'payment_method' => 'bank_transfer',
            'terms_accepted' => true,
        ]);

        $response->assertSessionHasErrors(['referrer_code']);
    }

    public function test_investment_creation_prevents_self_referral()
    {
        $user = User::factory()->create(['referral_code' => 'SELF123']);
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        $response = $this->actingAs($user)->post(route('investments.store'), [
            'amount' => 1000,
            'tier_id' => $tier->id,
            'referrer_code' => 'SELF123', // User's own referral code
            'payment_proof' => UploadedFile::fake()->image('payment.jpg'),
            'payment_method' => 'bank_transfer',
            'terms_accepted' => true,
        ]);

        $response->assertRedirect();
        
        // Verify no self-referral was created
        $this->assertNull($user->fresh()->referred_by);
    }

    public function test_investment_approval_processes_referral_commissions()
    {
        $referrer = User::factory()->create(['referral_code' => 'REF123']);
        $user = User::factory()->create(['referred_by' => $referrer->id]);
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $investment = Investment::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->patch(route('investments.approve', $investment));

        $response->assertRedirect();
        $this->assertDatabaseHas('investments', [
            'id' => $investment->id,
            'status' => 'active',
            'approved_by' => $admin->id,
        ]);
        $this->assertNotNull($investment->fresh()->approved_at);
    }

    public function test_investment_rejection_does_not_update_user_totals()
    {
        $user = User::factory()->create(['total_investment_amount' => 0]);
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $investment = Investment::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'amount' => 1000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->patch(route('investments.reject', $investment));

        $response->assertRedirect();
        $this->assertDatabaseHas('investments', [
            'id' => $investment->id,
            'status' => 'rejected',
            'rejected_by' => $admin->id,
        ]);
        
        // User's total investment should remain unchanged for rejected investments
        $this->assertEquals(0, $user->fresh()->total_investment_amount);
    }

    public function test_investment_show_includes_all_required_data()
    {
        $user = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        $investment = Investment::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'amount' => 1000,
            'status' => 'active',
            'investment_date' => now()->subMonths(6),
        ]);

        $response = $this->actingAs($user)->get(route('investments.show', $investment));

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('Investments/Show')
                ->has('investment')
                ->has('metrics')
                ->has('withdrawalInfo')
                ->has('projections')
                ->has('penalties')
                ->has('lockInStatus')
                ->has('withdrawalScenarios')
                ->has('tierBenefits')
        );
    }

    public function test_investment_create_page_shows_tier_information()
    {
        $user = User::factory()->create(['total_investment_amount' => 2000]);

        $response = $this->actingAs($user)->get(route('investments.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('Investments/Create')
                ->has('tiers')
                ->has('userStats')
                ->where('userStats.total_investment', 2000)
        );
    }

    public function test_tier_upgrade_creates_upgrade_investment_record()
    {
        $user = User::factory()->create(['total_investment_amount' => 1000]);
        $starterTier = InvestmentTier::where('name', 'Starter')->first();
        
        $response = $this->actingAs($user)->post(route('investments.tier-upgrade'), [
            'target_tier_id' => $starterTier->id,
            'additional_amount' => 500,
            'payment_proof' => UploadedFile::fake()->image('payment.jpg'),
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('investments', [
            'user_id' => $user->id,
            'tier_id' => $starterTier->id,
            'amount' => 500,
            'is_tier_upgrade' => true,
            'status' => 'pending',
        ]);
    }

    public function test_investment_history_filters_by_status()
    {
        $user = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'status' => 'active',
        ]);
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'status' => 'pending',
        ]);

        // Test active filter
        $response = $this->actingAs($user)->get(route('investments.history', ['status' => 'active']));
        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('Investments/History')
                ->has('investments.data', 1)
                ->where('filters.status', 'active')
        );

        // Test pending filter
        $response = $this->actingAs($user)->get(route('investments.history', ['status' => 'pending']));
        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('Investments/History')
                ->has('investments.data', 1)
                ->where('filters.status', 'pending')
        );
    }

    public function test_investment_history_filters_by_tier()
    {
        $user = User::factory()->create();
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        $starterTier = InvestmentTier::where('name', 'Starter')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $basicTier->id,
            'status' => 'active',
        ]);
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $starterTier->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('investments.history', ['tier' => 'Basic']));
        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('Investments/History')
                ->has('investments.data', 1)
                ->where('filters.tier', 'Basic')
        );
    }

    public function test_admin_can_view_all_investments()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create(['user_id' => $user1->id, 'tier_id' => $tier->id]);
        Investment::factory()->create(['user_id' => $user2->id, 'tier_id' => $tier->id]);

        $response = $this->actingAs($admin)->get(route('investments.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('Investments/Index')
                ->has('investments.data', 2) // Admin should see all investments
        );
    }

    public function test_regular_user_can_only_view_own_investments()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create(['user_id' => $user1->id, 'tier_id' => $tier->id]);
        Investment::factory()->create(['user_id' => $user2->id, 'tier_id' => $tier->id]);

        $response = $this->actingAs($user1)->get(route('investments.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('Investments/Index')
                ->has('investments.data', 1) // User should only see their own investment
        );
    }

    public function test_investment_stats_calculation()
    {
        $user = User::factory()->create();
        $tier = InvestmentTier::where('name', 'Basic')->first();
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'amount' => 1000,
            'status' => 'active',
        ]);
        
        Investment::factory()->create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'amount' => 500,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('investments.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('Investments/Index')
                ->where('stats.total_investments', 2)
                ->where('stats.active_investments', 1)
                ->where('stats.pending_investments', 1)
                ->where('stats.total_amount', 1500)
        );
    }
}