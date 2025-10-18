<?php

namespace Tests\Feature\Commands;

use Tests\TestCase;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\ReferralCommission;
use App\Models\Investment;
use App\Models\TeamVolume;
use App\Models\UserNetwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

class MigrateVBIFUsersToMyGrowNetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create VBIF tiers
        $this->createVBIFTiers();
        
        // Create MyGrowNet tiers
        $this->createMyGrowNetTiers();
    }

    public function test_migration_command_runs_successfully_in_dry_run_mode()
    {
        // Create test users with VBIF tiers
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        $starterTier = InvestmentTier::where('name', 'Starter')->first();
        
        $user1 = User::factory()->create(['current_investment_tier_id' => $basicTier->id]);
        $user2 = User::factory()->create(['current_investment_tier_id' => $starterTier->id]);

        // Run migration in dry-run mode
        $exitCode = Artisan::call('mygrownet:migrate-users', ['--dry-run' => true]);

        $this->assertEquals(0, $exitCode);
        
        // Verify no changes were made in dry-run mode
        $user1->refresh();
        $user2->refresh();
        
        $this->assertEquals($basicTier->id, $user1->current_investment_tier_id);
        $this->assertEquals($starterTier->id, $user2->current_investment_tier_id);
    }

    public function test_migration_maps_vbif_tiers_to_mygrownet_tiers()
    {
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        $starterTier = InvestmentTier::where('name', 'Starter')->first();
        $builderTier = InvestmentTier::where('name', 'Builder')->first();
        
        $bronzeTier = InvestmentTier::where('name', 'Bronze')->first();
        $silverTier = InvestmentTier::where('name', 'Silver')->first();
        $goldTier = InvestmentTier::where('name', 'Gold')->first();

        $user1 = User::factory()->create(['current_investment_tier_id' => $basicTier->id]);
        $user2 = User::factory()->create(['current_investment_tier_id' => $starterTier->id]);
        $user3 = User::factory()->create(['current_investment_tier_id' => $builderTier->id]);

        // Run migration
        $exitCode = Artisan::call('mygrownet:migrate-users');
        $this->assertEquals(0, $exitCode);

        // Verify tier mappings
        $user1->refresh();
        $user2->refresh();
        $user3->refresh();

        $this->assertEquals($bronzeTier->id, $user1->current_investment_tier_id);
        $this->assertEquals($silverTier->id, $user2->current_investment_tier_id);
        $this->assertEquals($goldTier->id, $user3->current_investment_tier_id);
    }

    public function test_migration_preserves_commission_history()
    {
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        $user = User::factory()->create(['current_investment_tier_id' => $basicTier->id]);
        $referrer = User::factory()->create();

        // Create existing commission
        $commission = ReferralCommission::factory()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => $user->id,
            'level' => 1,
            'amount' => 100,
            'percentage' => 10,
            'status' => 'paid'
        ]);

        $exitCode = Artisan::call('mygrownet:migrate-users');
        $this->assertEquals(0, $exitCode);

        // Verify commission is preserved and updated
        $commission->refresh();
        $this->assertEquals('REFERRAL', $commission->commission_type);
        $this->assertEquals('VBIF_INVESTMENT', $commission->package_type);
        $this->assertEquals(1000, $commission->package_amount); // 100 / (10/100)
    }

    public function test_migration_builds_network_relationships()
    {
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        // Create referral chain: referrer -> user1 -> user2
        $referrer = User::factory()->create(['current_investment_tier_id' => $basicTier->id]);
        $user1 = User::factory()->create([
            'current_investment_tier_id' => $basicTier->id,
            'referrer_id' => $referrer->id
        ]);
        $user2 = User::factory()->create([
            'current_investment_tier_id' => $basicTier->id,
            'referrer_id' => $user1->id
        ]);

        $exitCode = Artisan::call('mygrownet:migrate-users');
        $this->assertEquals(0, $exitCode);

        // Verify network paths are created
        $user1->refresh();
        $user2->refresh();

        $this->assertNotNull($user1->network_path);
        $this->assertNotNull($user2->network_path);
        $this->assertEquals(1, $user1->network_level);
        $this->assertEquals(2, $user2->network_level);

        // Verify UserNetwork entries are created
        $this->assertDatabaseHas('user_networks', [
            'user_id' => $user1->id,
            'referrer_id' => $referrer->id,
            'level' => 1
        ]);

        $this->assertDatabaseHas('user_networks', [
            'user_id' => $user2->id,
            'referrer_id' => $referrer->id,
            'level' => 2
        ]);

        $this->assertDatabaseHas('user_networks', [
            'user_id' => $user2->id,
            'referrer_id' => $user1->id,
            'level' => 1
        ]);
    }

    public function test_migration_initializes_team_volume_tracking()
    {
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        
        $referrer = User::factory()->create(['current_investment_tier_id' => $basicTier->id]);
        $user1 = User::factory()->create([
            'current_investment_tier_id' => $basicTier->id,
            'referrer_id' => $referrer->id
        ]);

        // Create investments for volume calculation
        Investment::factory()->create([
            'user_id' => $referrer->id,
            'amount' => 1000,
            'status' => 'active'
        ]);

        Investment::factory()->create([
            'user_id' => $user1->id,
            'amount' => 500,
            'status' => 'active'
        ]);

        $exitCode = Artisan::call('mygrownet:migrate-users');
        $this->assertEquals(0, $exitCode);

        // Verify team volume records are created
        $referrerVolume = TeamVolume::where('user_id', $referrer->id)->first();
        $this->assertNotNull($referrerVolume);
        $this->assertEquals(1000, $referrerVolume->personal_volume);
        $this->assertEquals(1500, $referrerVolume->team_volume); // 1000 + 500
        $this->assertEquals(1, $referrerVolume->active_referrals_count);

        // Verify user fields are updated
        $referrer->refresh();
        $this->assertEquals(1500, $referrer->current_team_volume);
        $this->assertEquals(1000, $referrer->current_personal_volume);
        $this->assertEquals(1, $referrer->active_referrals_count);
    }

    public function test_migration_updates_subscription_status()
    {
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        $bronzeTier = InvestmentTier::where('name', 'Bronze')->first();
        
        $user = User::factory()->create([
            'current_investment_tier_id' => $basicTier->id,
            'subscription_status' => null
        ]);

        $exitCode = Artisan::call('mygrownet:migrate-users');
        $this->assertEquals(0, $exitCode);

        $user->refresh();
        
        $this->assertEquals('active', $user->subscription_status);
        $this->assertEquals($bronzeTier->monthly_fee, $user->monthly_subscription_fee);
        $this->assertNotNull($user->subscription_start_date);
        $this->assertNotNull($user->subscription_end_date);
    }

    public function test_migration_archives_old_vbif_tiers()
    {
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        $starterTier = InvestmentTier::where('name', 'Starter')->first();

        User::factory()->create(['current_investment_tier_id' => $basicTier->id]);

        $exitCode = Artisan::call('mygrownet:migrate-users');
        $this->assertEquals(0, $exitCode);

        // Verify VBIF tiers are archived
        $basicTier->refresh();
        $starterTier->refresh();

        $this->assertFalse($basicTier->is_active);
        $this->assertTrue($basicTier->is_archived);
        $this->assertStringContains('ARCHIVED - Migrated to MyGrowNet', $basicTier->description);
    }

    public function test_migration_records_activity_logs()
    {
        $basicTier = InvestmentTier::where('name', 'Basic')->first();
        $user = User::factory()->create(['current_investment_tier_id' => $basicTier->id]);

        $exitCode = Artisan::call('mygrownet:migrate-users');
        $this->assertEquals(0, $exitCode);

        // Verify activity log is created
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'mygrownet_migration'
        ]);
    }

    private function createVBIFTiers(): void
    {
        $vbifTiers = [
            [
                'name' => 'Basic',
                'minimum_investment' => 500,
                'fixed_profit_rate' => 3,
                'direct_referral_rate' => 5,
                'level2_referral_rate' => null,
                'level3_referral_rate' => null,
                'order' => 1,
                'is_active' => true,
                'is_archived' => false,
                'description' => 'Basic VBIF tier'
            ],
            [
                'name' => 'Starter',
                'minimum_investment' => 1000,
                'fixed_profit_rate' => 6,
                'direct_referral_rate' => 8,
                'level2_referral_rate' => 3,
                'level3_referral_rate' => null,
                'order' => 2,
                'is_active' => true,
                'is_archived' => false,
                'description' => 'Starter VBIF tier'
            ],
            [
                'name' => 'Builder',
                'minimum_investment' => 2500,
                'fixed_profit_rate' => 9,
                'direct_referral_rate' => 10,
                'level2_referral_rate' => 5,
                'level3_referral_rate' => 2,
                'order' => 3,
                'is_active' => true,
                'is_archived' => false,
                'description' => 'Builder VBIF tier'
            ],
            [
                'name' => 'Leader',
                'minimum_investment' => 5000,
                'fixed_profit_rate' => 12,
                'direct_referral_rate' => 12,
                'level2_referral_rate' => 6,
                'level3_referral_rate' => 3,
                'order' => 4,
                'is_active' => true,
                'is_archived' => false,
                'description' => 'Leader VBIF tier'
            ],
            [
                'name' => 'Elite',
                'minimum_investment' => 10000,
                'fixed_profit_rate' => 15,
                'direct_referral_rate' => 15,
                'level2_referral_rate' => 8,
                'level3_referral_rate' => 4,
                'order' => 5,
                'is_active' => true,
                'is_archived' => false,
                'description' => 'Elite VBIF tier'
            ]
        ];

        foreach ($vbifTiers as $tier) {
            InvestmentTier::create($tier);
        }
    }

    private function createMyGrowNetTiers(): void
    {
        // Run the MyGrowNet transformation migration
        Artisan::call('migrate', ['--path' => 'database/migrations/2025_01_15_000002_transform_investment_tiers_to_mygrownet_membership_tiers.php']);
    }
}