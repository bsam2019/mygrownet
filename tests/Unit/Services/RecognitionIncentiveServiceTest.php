<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\RecognitionIncentiveService;
use App\Services\GamificationService;
use App\Models\IncentiveProgram;
use App\Models\RaffleEntry;
use App\Models\RecognitionEvent;
use App\Models\Certificate;
use App\Models\User;
use App\Models\Achievement;
use App\Models\InvestmentTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class RecognitionIncentiveServiceTest extends TestCase
{
    use RefreshDatabase;

    private RecognitionIncentiveService $service;
    private User $user;
    private InvestmentTier $tier;

    protected function setUp(): void
    {
        parent::setUp();
        
        $gamificationService = $this->createMock(GamificationService::class);
        $this->service = new RecognitionIncentiveService($gamificationService);
        
        // Create test user and tier
        $this->tier = InvestmentTier::factory()->create([
            'name' => 'Silver',
            'monthly_fee' => 300
        ]);
        
        $this->user = User::factory()->create([
            'current_tier_id' => $this->tier->id,
            'referral_count' => 5,
            'total_earnings' => 10000
        ]);
    }

    public function test_processes_weekly_incentive_program()
    {
        // Create a weekly incentive program
        $program = IncentiveProgram::create([
            'name' => 'Weekly Top Recruiters Test',
            'slug' => 'weekly-test',
            'description' => 'Test weekly program',
            'type' => 'competition',
            'period_type' => 'weekly',
            'start_date' => now()->subWeek()->startOfWeek(),
            'end_date' => now()->subWeek()->endOfWeek(),
            'eligibility_criteria' => [
                ['type' => 'referrals', 'weight' => 1.0]
            ],
            'rewards' => [
                ['position' => 1, 'type' => 'monetary', 'amount' => 5000, 'quantity' => 1]
            ],
            'max_winners' => 1,
            'is_active' => true,
            'status' => 'active',
            'total_budget' => 5000
        ]);

        // Add participant
        $program->addParticipant($this->user);

        // Process the program
        $result = $this->service->processIncentiveProgram($program);

        // Assertions
        $this->assertArrayHasKey('winners', $result);
        $this->assertArrayHasKey('reward_results', $result);
        $this->assertCount(1, $result['winners']);
        $this->assertEquals($this->user->id, $result['winners'][0]['user']->id);
        
        // Check program status
        $program->refresh();
        $this->assertEquals('completed', $program->status);
    }

    public function test_processes_quarterly_raffle_program()
    {
        // Create a quarterly raffle program
        $program = IncentiveProgram::create([
            'name' => 'Quarterly Raffle Test',
            'slug' => 'quarterly-raffle-test',
            'description' => 'Test quarterly raffle',
            'type' => 'raffle',
            'period_type' => 'quarterly',
            'start_date' => now()->subQuarter()->startOfQuarter(),
            'end_date' => now()->subQuarter()->endOfQuarter(),
            'eligibility_criteria' => [
                ['type' => 'referrals', 'weight' => 2.0]
            ],
            'rewards' => [
                ['type' => 'physical', 'item' => 'smartphone', 'value' => 3000, 'quantity' => 1]
            ],
            'max_winners' => 1,
            'is_active' => true,
            'status' => 'active'
        ]);

        // Create raffle entry
        $raffleEntry = RaffleEntry::create([
            'incentive_program_id' => $program->id,
            'user_id' => $this->user->id,
            'entry_count' => 5,
            'qualification_score' => 100
        ]);

        // Process the raffle
        $result = $this->service->processRaffleProgram($program);

        // Assertions
        $this->assertArrayHasKey('winners', $result);
        $this->assertArrayHasKey('total_entries', $result);
        $this->assertEquals(5, $result['total_entries']);
        
        // Check if user won (should win since they're the only participant)
        $raffleEntry->refresh();
        $this->assertTrue($raffleEntry->is_winner);
    }

    public function test_creates_certificates_for_achievements()
    {
        // Create an achievement
        $achievement = Achievement::create([
            'name' => 'Test Achievement',
            'slug' => 'test-achievement',
            'description' => 'Test achievement description',
            'category' => 'referral',
            'points' => 100,
            'triggers_celebration' => true,
            'is_active' => true
        ]);

        // Trigger achievement recognition
        $this->service->triggerAchievementRecognition($this->user, $achievement);

        // Check if certificate was created
        $certificate = Certificate::where('user_id', $this->user->id)
            ->where('certificate_type', 'achievement')
            ->first();

        $this->assertNotNull($certificate);
        $this->assertStringContainsString('Test Achievement', $certificate->title);
        $this->assertEquals('pending', $certificate->status);
    }

    public function test_creates_tier_advancement_certificate()
    {
        // Trigger tier advancement recognition
        $this->service->triggerTierAdvancementRecognition($this->user);

        // Check if certificate was created
        $certificate = Certificate::where('user_id', $this->user->id)
            ->where('certificate_type', 'tier_advancement')
            ->first();

        $this->assertNotNull($certificate);
        $this->assertStringContainsString('Silver', $certificate->title);
        $this->assertEquals('pending', $certificate->status);
    }

    public function test_calculates_user_incentive_summary()
    {
        // Create some test data
        $activeProgram = IncentiveProgram::create([
            'name' => 'Active Program',
            'slug' => 'active-program',
            'description' => 'Active test program',
            'type' => 'competition',
            'period_type' => 'weekly',
            'start_date' => now()->startOfWeek(),
            'end_date' => now()->endOfWeek(),
            'eligibility_criteria' => [],
            'is_active' => true,
            'status' => 'active'
        ]);

        $certificate = Certificate::create([
            'user_id' => $this->user->id,
            'certificate_type' => 'achievement',
            'title' => 'Test Certificate',
            'description' => 'Test certificate description',
            'issued_date' => now(),
            'certificate_number' => 'TEST-001',
            'verification_code' => 'VERIFY123',
            'status' => 'issued'
        ]);

        // Get user incentive summary
        $summary = $this->service->getUserIncentiveSummary($this->user);

        // Assertions
        $this->assertArrayHasKey('active_programs', $summary);
        $this->assertArrayHasKey('program_history', $summary);
        $this->assertArrayHasKey('raffle_entries', $summary);
        $this->assertArrayHasKey('certificates_earned', $summary);
        $this->assertArrayHasKey('total_incentive_earnings', $summary);
        $this->assertArrayHasKey('upcoming_events', $summary);

        $this->assertCount(1, $summary['active_programs']);
        $this->assertCount(1, $summary['certificates_earned']);
    }

    public function test_recognition_event_eligibility()
    {
        // Create a recognition event
        $event = RecognitionEvent::create([
            'name' => 'Test Recognition Event',
            'slug' => 'test-event',
            'description' => 'Test event description',
            'event_type' => 'quarterly_ceremony',
            'event_date' => now()->addMonth(),
            'location' => 'Virtual',
            'is_virtual' => true,
            'max_attendees' => 100,
            'registration_deadline' => now()->addWeeks(3),
            'eligibility_criteria' => [
                ['type' => 'tier_level', 'operator' => '>=', 'value' => 2] // Silver and above
            ],
            'status' => 'registration_open'
        ]);

        // Test eligibility
        $this->assertTrue($event->isEligible($this->user));

        // Register user
        $event->registerAttendee($this->user);

        // Check registration
        $this->assertTrue($event->attendees()->where('user_id', $this->user->id)->exists());
    }

    public function test_raffle_entry_calculation()
    {
        // Create a raffle program
        $program = IncentiveProgram::create([
            'name' => 'Test Raffle',
            'slug' => 'test-raffle',
            'description' => 'Test raffle program',
            'type' => 'raffle',
            'period_type' => 'quarterly',
            'start_date' => now()->startOfQuarter(),
            'end_date' => now()->endOfQuarter(),
            'eligibility_criteria' => [],
            'is_active' => true
        ]);

        // Create raffle entry
        $raffleEntry = RaffleEntry::create([
            'incentive_program_id' => $program->id,
            'user_id' => $this->user->id
        ]);

        // Calculate entries
        $entryCount = $raffleEntry->calculateEntries($this->user, $program);

        // Should have base entries plus bonus entries for referrals
        $this->assertGreaterThan(1, $entryCount);

        // Update entry count
        $raffleEntry->updateEntryCount($this->user, $program);
        $raffleEntry->refresh();

        $this->assertEquals($entryCount, $raffleEntry->entry_count);
        $this->assertNotEmpty($raffleEntry->entry_source);
    }
}