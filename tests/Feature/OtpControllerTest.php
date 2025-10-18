<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\OtpToken;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OtpControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'phone' => '+1234567890',
            'password' => 'password'
        ]);
    }

    public function test_generate_otp_requires_authentication()
    {
        $response = $this->postJson('/otp/generate', [
            'type' => 'email',
            'purpose' => 'withdrawal'
        ]);

        $response->assertStatus(401);
    }

    public function test_generate_otp_validates_input()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/otp/generate', [
                'type' => 'invalid',
                'purpose' => 'invalid_purpose'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type', 'purpose']);
    }

    public function test_generate_email_otp_successfully()
    {
        Notification::fake();

        $response = $this->actingAs($this->user)
            ->postJson('/otp/generate', [
                'type' => 'email',
                'purpose' => 'withdrawal'
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'expires_at',
                'identifier',
                'type',
                'purpose'
            ])
            ->assertJson([
                'type' => 'email',
                'purpose' => 'withdrawal',
                'identifier' => 't***@example.com'
            ]);

        $this->assertDatabaseHas('otp_tokens', [
            'user_id' => $this->user->id,
            'type' => 'email',
            'purpose' => 'withdrawal',
            'is_used' => false
        ]);
    }

    public function test_generate_sms_otp_successfully()
    {
        Notification::fake();

        $response = $this->actingAs($this->user)
            ->postJson('/otp/generate', [
                'type' => 'sms',
                'purpose' => 'withdrawal'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'type' => 'sms',
                'purpose' => 'withdrawal',
                'identifier' => '+123****7890'
            ]);

        $this->assertDatabaseHas('otp_tokens', [
            'user_id' => $this->user->id,
            'type' => 'sms',
            'purpose' => 'withdrawal',
            'identifier' => $this->user->phone,
            'is_used' => false
        ]);
    }

    public function test_generate_otp_fails_when_rate_limited()
    {
        // Mock the OTP service to return rate limit error
        $this->mock(OtpService::class, function ($mock) {
            $mock->shouldReceive('generateOtp')
                ->once()
                ->andReturn([
                    'success' => false,
                    'message' => 'Too many OTP requests. Please try again later.',
                    'retry_after' => 3600
                ]);
        });

        $response = $this->actingAs($this->user)
            ->postJson('/otp/generate', [
                'type' => 'email',
                'purpose' => 'withdrawal'
            ]);

        $response->assertStatus(429)
            ->assertJson([
                'message' => 'Too many OTP requests. Please try again later.',
                'retry_after' => 3600
            ]);
    }

    public function test_verify_otp_requires_authentication()
    {
        $response = $this->postJson('/otp/verify', [
            'token' => '123456',
            'type' => 'email',
            'purpose' => 'withdrawal'
        ]);

        $response->assertStatus(401);
    }

    public function test_verify_otp_validates_input()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/otp/verify', [
                'token' => '12345', // Invalid length
                'type' => 'invalid',
                'purpose' => 'invalid_purpose'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['token', 'type', 'purpose']);
    }

    public function test_verify_otp_successfully()
    {
        $token = '123456';
        $otpToken = OtpToken::create([
            'user_id' => $this->user->id,
            'token' => $token,
            'type' => 'email',
            'purpose' => 'withdrawal',
            'identifier' => $this->user->email,
            'expires_at' => now()->addMinutes(10),
            'is_used' => false,
            'attempts' => 0
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/otp/verify', [
                'token' => $token,
                'type' => 'email',
                'purpose' => 'withdrawal'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'OTP verified successfully',
                'verified' => true,
                'token_id' => $otpToken->id
            ]);

        // Check token is marked as used
        $otpToken->refresh();
        $this->assertTrue($otpToken->is_used);
        $this->assertNotNull($otpToken->verified_at);

        // Check session is set
        $this->assertTrue(session()->has("otp_verified:{$this->user->id}:withdrawal"));
    }

    public function test_verify_otp_fails_with_invalid_token()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/otp/verify', [
                'token' => '999999',
                'type' => 'email',
                'purpose' => 'withdrawal'
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid OTP code',
                'verified' => false
            ]);
    }

    public function test_verify_otp_fails_with_expired_token()
    {
        $token = '123456';
        OtpToken::create([
            'user_id' => $this->user->id,
            'token' => $token,
            'type' => 'email',
            'purpose' => 'withdrawal',
            'identifier' => $this->user->email,
            'expires_at' => now()->subMinutes(1), // Expired
            'is_used' => false,
            'attempts' => 0
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/otp/verify', [
                'token' => $token,
                'type' => 'email',
                'purpose' => 'withdrawal'
            ]);

        $response->assertStatus(400)
            ->assertJson(['verified' => false])
            ->assertJsonFragment(['message' => 'OTP is expired. Please request a new one.']);
    }

    public function test_resend_otp_requires_authentication()
    {
        $response = $this->postJson('/otp/resend', [
            'type' => 'email',
            'purpose' => 'withdrawal'
        ]);

        $response->assertStatus(401);
    }

    public function test_resend_otp_validates_input()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/otp/resend', [
                'type' => 'invalid',
                'purpose' => ''
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type', 'purpose']);
    }

    public function test_resend_otp_successfully()
    {
        Notification::fake();

        $response = $this->actingAs($this->user)
            ->postJson('/otp/resend', [
                'type' => 'email',
                'purpose' => 'withdrawal'
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'expires_at',
                'identifier'
            ]);
    }

    public function test_resend_otp_fails_when_too_recent()
    {
        // Create recent OTP
        OtpToken::create([
            'user_id' => $this->user->id,
            'token' => '123456',
            'type' => 'email',
            'purpose' => 'withdrawal',
            'identifier' => $this->user->email,
            'expires_at' => now()->addMinutes(10),
            'is_used' => false,
            'created_at' => now()->subMinute(1) // 1 minute ago
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/otp/resend', [
                'type' => 'email',
                'purpose' => 'withdrawal'
            ]);

        $response->assertStatus(429)
            ->assertJsonFragment(['message' => 'Please wait before requesting another OTP'])
            ->assertJsonStructure(['retry_after']);
    }

    public function test_status_endpoint_returns_verification_status()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/otp/status?purpose=withdrawal');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'verified',
                'purpose',
                'expires_at'
            ])
            ->assertJson([
                'verified' => false,
                'purpose' => 'withdrawal'
            ]);
    }

    public function test_status_endpoint_validates_purpose()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/otp/status');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['purpose']);
    }

    public function test_status_endpoint_shows_verified_when_session_exists()
    {
        // Set verification session
        session(["otp_verified:{$this->user->id}:withdrawal" => now()]);

        $response = $this->actingAs($this->user)
            ->getJson('/otp/status?purpose=withdrawal');

        $response->assertStatus(200)
            ->assertJson([
                'verified' => true,
                'purpose' => 'withdrawal'
            ])
            ->assertJsonStructure(['expires_at']);
    }

    public function test_stats_endpoint_returns_user_otp_statistics()
    {
        // Create test OTP tokens
        OtpToken::create([
            'user_id' => $this->user->id,
            'token' => '123456',
            'type' => 'email',
            'purpose' => 'withdrawal',
            'identifier' => $this->user->email,
            'expires_at' => now()->addMinutes(10),
            'is_used' => true,
            'attempts' => 1,
            'verified_at' => now()
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/otp/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'stats',
                'period_days'
            ])
            ->assertJson(['period_days' => 30]);
    }

    public function test_stats_endpoint_accepts_custom_period()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/otp/stats?days=7');

        $response->assertStatus(200)
            ->assertJson(['period_days' => 7]);
    }

    public function test_token_cleanup_removes_old_tokens()
    {
        // Create old expired token
        OtpToken::create([
            'user_id' => $this->user->id,
            'token' => '123456',
            'type' => 'email',
            'purpose' => 'withdrawal',
            'identifier' => $this->user->email,
            'expires_at' => now()->subHours(25),
            'is_used' => false
        ]);

        // Create recent token
        OtpToken::create([
            'user_id' => $this->user->id,
            'token' => '654321',
            'type' => 'email',
            'purpose' => 'withdrawal',
            'identifier' => $this->user->email,
            'expires_at' => now()->subHours(1),
            'is_used' => false
        ]);

        $this->artisan('otp:cleanup')
            ->expectsOutput('Starting OTP token cleanup...')
            ->expectsOutputToContain('Cleaned up 1 expired OTP tokens.')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('otp_tokens', ['token' => '123456']);
        $this->assertDatabaseHas('otp_tokens', ['token' => '654321']);
    }
}