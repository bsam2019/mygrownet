<?php

namespace Tests\Feature\Commands;

use Tests\TestCase;
use App\Models\User;
use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\TeamVolume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

class InitializeTeamVolumeTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestTiers();
    }

    public function test_command_runs_successfully_in_dry_run_mode()
    {
        $user = User::factory()->create();
        Investment::factory()->create(['user_id' => $user->id, 'status' => 'active']);

        $exitCode = Artisan::call('mygrownet:initialize-team-volumes', ['--dry-run' => true]);

        $this->assertEquals(0, $exitCode);
        
        // Verify no changes were made in dry-run mode
        $this->assertNull($user->fresh()->network_path);
        $this->assertEquals(0, TeamVolume::count());
    }

    public function test_command_initializes_team_volumes_successfully()
    {
        $bronzeTier = InvestmentTier::where('name', 'Bronze')->first();
        
        // Create referral network
        $root = User::factory()->create(['current_investment_tier_id' => $bronzeTier->id]);
        $user1 = User::factory()->create([
            'referrer_id' => $root->id,
            'current_investment_tier_id' => $bronzeTier->id
        ]);

        // Create investments
        Investment::factory()->create(['user_id' => $root->id, 'amount' => 1000, 'status' => 'active']);
        Investment::factory()->create(['user_id' => $user1->id, 'amount' => 500, 'status' => 'active']);

        $exitCode = Artisan::call('mygrownet:initialize-team-volumes');

        $this->assertEquals(0, $exitCode);

        // Verify network paths are created
        $root->refresh();
        $user1->refresh();

        $this->assertNotNull($root->network_path);
        $this->assertNotNull($user1->network_path);
        $this->assertEquals(0, $root->network_level);
        $this->assertEquals(1, $user1->network_level);

        // Verify team volumes are created
        $this->assertDatabaseHas('team_volumes', ['user_id' => $root->id]);
        $this->assertDatabaseHas('team_volumes', ['user_id' => $user1->id]);

        // Verify user fields are updated
        $this->assertEquals(1500, $root->current_team_volume); // 1000 + 500
        $this->assertEquals(500, $user1->current_team_volume);
    }

    public function test_stats_option_displays_statistics()
    {
        $user = User::factory()->create(['network_path' => '1']);
        TeamVolume::factory()->create(['user_id' => $user->id]);

        $exitCode = Artisan::call('mygrownet:initialize-team-volumes', ['--stats' => true]);

        $this->assertEquals(0, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContains('Team Volume Statistics', $output);
        $this->assertStringContains('Total Users', $output);
        $this->assertStringContains('Users with Network Paths', $output);
    }

    public function test_validate_option_validates_calculations()
    {
        $user = User::factory()->create(['network_path' => '1']);
        Investment::factory()->create(['user_id' => $user->id, 'status' => 'active']);
        TeamVolume::factory()->create([
            'user_id' => $user->id,
            'personal_volume' => 1000,
            'team_volume' => 1000
        ]);

        $exitCode = Artisan::call('mygrownet:initialize-team-volumes', ['--validate' => true]);

        $this->assertEquals(0, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContains('Validating Team Volume Calculations', $output);
        $this->assertStringContains('All team volume calculations are valid', $output);
    }

    public function test_validate_option_detects_issues()
    {
        // Create invalid data: team volume less than personal volume
        $user = User::factory()->create(['network_path' => '1']);
        TeamVolume::factory()->create([
            'user_id' => $user->id,
            'personal_volume' => 1000,
            'team_volume' => 500 // Invalid: team < personal
        ]);

        $exitCode = Artisan::call('mygrownet:initialize-team-volumes', ['--validate' => true]);

        $this->assertEquals(1, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContains('Found issues with team volume calculations', $output);
    }

    public function test_recalculate_option_recalculates_specific_users()
    {
        $user = User::factory()->create(['network_path' => '1']);
        Investment::factory()->create(['user_id' => $user->id, 'amount' => 1500, 'status' => 'active']);
        
        // Create team volume with incorrect data
        TeamVolume::factory()->create([
            'user_id' => $user->id,
            'personal_volume' => 1000, // Should be 1500
            'team_volume' => 1000
        ]);

        $exitCode = Artisan::call('mygrownet:initialize-team-volumes', [
            '--recalculate' => (string) $user->id
        ]);

        $this->assertEquals(0, $exitCode);

        // Verify recalculation
        $teamVolume = TeamVolume::where('user_id', $user->id)->first();
        $this->assertEquals(1500, $teamVolume->personal_volume);
        $this->assertEquals(1500, $teamVolume->team_volume);
    }

    public function test_command_handles_multiple_user_recalculation()
    {
        $user1 = User::factory()->create(['network_path' => '1']);
        $user2 = User::factory()->create(['network_path' => '2']);
        
        Investment::factory()->create(['user_id' => $user1->id, 'amount' => 1000, 'status' => 'active']);
        Investment::factory()->create(['user_id' => $user2->id, 'amount' => 2000, 'status' => 'active']);

        TeamVolume::factory()->create(['user_id' => $user1->id, 'personal_volume' => 500]);
        TeamVolume::factory()->create(['user_id' => $user2->id, 'personal_volume' => 1000]);

        $exitCode = Artisan::call('mygrownet:initialize-team-volumes', [
            '--recalculate' => $user1->id . ',' . $user2->id
        ]);

        $this->assertEquals(0, $exitCode);

        $output = Artisan::output();
        $this->assertStringContains('Recalculated team volumes for 2 users', $output);
    }

    public function test_command_displays_progress_and_results()
    {
        $user = User::factory()->create();
        Investment::factory()->create(['user_id' => $user->id, 'status' => 'active']);

        $exitCode = Artisan::call('mygrownet:initialize-team-volumes');

        $this->assertEquals(0, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContains('MyGrowNet Team Volume Initialization', $output);
        $this->assertStringContains('Building network relationships', $output);
        $this->assertStringContains('Calculating historical team volumes', $output);
        $this->assertStringContains('Initializing performance bonus eligibility', $output);
        $this->assertStringContains('INITIALIZATION COMPLETED', $output);
    }

    public function test_command_handles_errors_gracefully()
    {
        // Mock a service that throws an exception
        $this->mock(\App\Services\TeamVolumeInitializationService::class)
            ->shouldReceive('initializeTeamVolumeTracking')
            ->andThrow(new \Exception('Test error'));

        $exitCode = Artisan::call('mygrownet:initialize-team-volumes');

        $this->assertEquals(1, $exitCode);
        
        $output = Artisan::output();
        $this->assertStringContains('Command failed: Test error', $output);
    }

    public function test_command_shows_success_message_with_benefits()
    {
        $user = User::factory()->create();
        Investment::factory()->create(['user_id' => $user->id, 'status' => 'active']);

        Artisan::call('mygrownet:initialize-team-volumes');
        
        $output = Artisan::output();
        $this->assertStringContains('Team volume initialization completed successfully', $output);
        $this->assertStringContains('Accurate team volume tracking', $output);
        $this->assertStringContains('Performance bonus calculations', $output);
        $this->assertStringContains('Tier advancement eligibility', $output);
        $this->assertStringContains('Enhanced network analytics', $output);
    }

    private function createTestTiers(): void
    {
        InvestmentTier::create([
            'name' => 'Bronze',
            'minimum_investment' => 0,
            'monthly_fee' => 150,
            'monthly_share' => 50,
            'monthly_team_volume_bonus_rate' => 0,
            'fixed_profit_rate' => 0,
            'direct_referral_rate' => 12,
            'level2_referral_rate' => 6,
            'level3_referral_rate' => 4,
            'order' => 1,
            'is_active' => true
        ]);

        InvestmentTier::create([
            'name' => 'Silver',
            'minimum_investment' => 0,
            'monthly_fee' => 300,
            'monthly_share' => 150,
            'monthly_team_volume_bonus_rate' => 2,
            'fixed_profit_rate' => 0,
            'direct_referral_rate' => 12,
            'level2_referral_rate' => 6,
            'level3_referral_rate' => 4,
            'order' => 2,
            'is_active' => true
        ]);
    }
}