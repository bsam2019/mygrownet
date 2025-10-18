<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\OtpToken;
use App\Services\OtpService;
use App\Notifications\OtpNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OtpServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OtpService $otpService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->otpService = new OtpService();
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'phone' => '+1234567890',
            'password' => 'password' // Use plain text for testing
        ]);
    }

    public function test_generates_otp_successfully()
    {
        Notification::fake();

        $result = $this->otpService->generateOtp(
            $this->user,
            'email',
            'withdrawal'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('OTP sent successfully', $result['message']);
        $this->assertArrayHasKey('expires_at', $result);
        $this->assertArrayHasKey('identifier', $result);

        // Check database
        $this->assertDatabaseHas('otp_tokens', [
            'user_id' => $this->user->id,
            'type' => 'email',
            'purpose' => 'withdrawal',
            'is_used' => false
        ]);

        // Check notification was sent
        Notification::assertSentTo($this->user, OtpNotification::class);
    }

    public function test_generates_sms_otp_successfully()
    {
        Notification::fake();

        $result = $this->otpService->generateOtp(
            $this->user,
            'sms',
            'withdrawal'
        );

        $this->assertTrue($result['success']);
        
        $this->assertDatabaseHas('otp_tokens', [
            'user_id' => $this->user->id,
            'type' => 'sms',
            'purpose' => 'withdrawal',
            'identifier' => $this->user->phone,
            'is_used' => false
        ]);
    }

    public function test_fails_when_no_phone_for_sms()
    {
        $userWithoutPhone = User::factory()->create([
            'phone' => null,
            'password' => 'password'
        ]);

        $result = $this->otpService->generateOtp(
            $userWithoutPhone,
            'sms',
            'withdrawal'
        );

        $this->assertFalse($result['success']);
        $this->assertEquals('Phone number not found', $result['message']);
    }

    public function test_fails_when_no_email_for_email_otp()
    {
        $userWithoutEmail = User::factory()->make([
            'email' => null,
            'password' => 'password'
        ]);
        $userWithoutEmail->save();

        $result = $this->otpService->generateOtp(
            $userWithoutEmail,
            'email',
            'withdrawal'
        );

        $this->assertFalse($result['success']);
        $this->assertEquals('Email address not found', $result['message']);
    }

    public function test_invalidates_existing_otps_when_generating_new_one()
    {
        // Create existing OTP
        $existingOtp = OtpToken::create([
            'user_id' => $this->user->id,
            'token' => '123456',
            'type' => 'email',
            'purpose' => 'withdrawal',
            'identifier' => $this->user->email,
            'expires_at' => now()->addMinutes(10),
            'is_used' => false
        ]);

        Notification::fake();

        // Generate new OTP
        $this->otpService->generateOtp($this->user, 'email', 'withdrawal');

        // Check existing OTP is invalidated
        $existingOtp->refresh();
        $this->assertTrue($existingOtp->is_used);
    }

    public function test_verifies_otp_successfully()
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

        $result = $this->otpService->verifyOtp(
            $this->user,
            $token,
            'email',
            'withdrawal'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('OTP verified successfully', $result['message']);
        $this->assertEquals($otpToken->id, $result['token_id']);

        // Check token is marked as used
        $otpToken->refresh();
        $this->assertTrue($otpToken->is_used);
        $this->assertNotNull($otpToken->verified_at);
    }

    public function test_fails_verification_with_invalid_token()
    {
        $result = $this->otpService->verifyOtp(
            $this->user,
            '999999',
            'email',
            'withdrawal'
        );

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid OTP code', $result['message']);
    }

    public function test_fails_verification_with_expired_token()
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

        $result = $this->otpService->verifyOtp(
            $this->user,
            $token,
            'email',
            'withdrawal'
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('expired', $result['message']);
    }

    public function test_fails_verification_with_used_token()
    {
        $token = '123456';
        OtpToken::create([
            'user_id' => $this->user->id,
            'token' => $token,
            'type' => 'email',
            'purpose' => 'withdrawal',
            'identifier' => $this->user->email,
            'expires_at' => now()->addMinutes(10),
            'is_used' => true, // Already used
            'attempts' => 1
        ]);

        $result = $this->otpService->verifyOtp(
            $this->user,
            $token,
            'email',
            'withdrawal'
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('already used', $result['message']);
    }

    public function test_increments_attempts_on_verification()
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

        // Successful verification
        $this->otpService->verifyOtp($this->user, $token, 'email', 'withdrawal');

        $otpToken->refresh();
        $this->assertEquals(1, $otpToken->attempts);
    }

    public function test_fails_verification_after_max_attempts()
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
            'attempts' => 3 // Max attempts reached
        ]);

        $result = $this->otpService->verifyOtp(
            $this->user,
            $token,
            'email',
            'withdrawal'
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Too many verification attempts', $result['message']);
    }

    public function test_rate_limiting_prevents_too_many_requests()
    {
        // Mock cache to simulate rate limit reached
        Cache::shouldReceive('get')
            ->with("otp_rate_limit:{$this->user->id}:email:withdrawal", 0)
            ->andReturn(5); // Max attempts reached

        // Mock the getStore method that's called in getRateLimitRetryAfter
        Cache::shouldReceive('getStore->getRedis->ttl')
            ->andReturn(3600);

        $result = $this->otpService->generateOtp(
            $this->user,
            'email',
            'withdrawal'
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Too many OTP requests', $result['message']);
        $this->assertArrayHasKey('retry_after', $result);
    }

    public function test_resend_prevents_too_frequent_requests()
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

        $result = $this->otpService->resendOtp($this->user, 'email', 'withdrawal');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Please wait before requesting another OTP', $result['message']);
        $this->assertArrayHasKey('retry_after', $result);
    }

    public function test_cleanup_expired_tokens()
    {
        // Create expired tokens
        OtpToken::create([
            'user_id' => $this->user->id,
            'token' => '123456',
            'type' => 'email',
            'purpose' => 'withdrawal',
            'identifier' => $this->user->email,
            'expires_at' => now()->subHours(25), // Older than 24 hours
            'is_used' => false
        ]);

        // Create non-expired token
        OtpToken::create([
            'user_id' => $this->user->id,
            'token' => '654321',
            'type' => 'email',
            'purpose' => 'withdrawal',
            'identifier' => $this->user->email,
            'expires_at' => now()->subHours(1), // Recent
            'is_used' => false
        ]);

        $deletedCount = $this->otpService->cleanupExpiredTokens();

        $this->assertEquals(1, $deletedCount);
        $this->assertDatabaseMissing('otp_tokens', ['token' => '123456']);
        $this->assertDatabaseHas('otp_tokens', ['token' => '654321']);
    }

    public function test_masks_phone_identifier_correctly()
    {
        Notification::fake();

        $result = $this->otpService->generateOtp(
            $this->user,
            'sms',
            'withdrawal'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('+123****7890', $result['identifier']);
    }

    public function test_masks_email_identifier_correctly()
    {
        Notification::fake();

        $result = $this->otpService->generateOtp(
            $this->user,
            'email',
            'withdrawal'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('t***@example.com', $result['identifier']);
    }

    public function test_get_otp_stats_returns_correct_data()
    {
        // Create test OTP tokens
        OtpToken::create([
            'user_id' => $this->user->id,
            'token' => '123456',
            'type' => 'email',
            'purpose' => 'withdrawal',
            'identifier' => $this->user->email,
            'expires_at' => now()->addMinutes(10),
            'is_used' => false,
            'attempts' => 1,
            'verified_at' => now()
        ]);

        OtpToken::create([
            'user_id' => $this->user->id,
            'token' => '654321',
            'type' => 'sms',
            'purpose' => 'withdrawal',
            'identifier' => $this->user->phone,
            'expires_at' => now()->addMinutes(10),
            'is_used' => true,
            'attempts' => 2,
            'verified_at' => null
        ]);

        $stats = $this->otpService->getOtpStats($this->user, 30);

        $this->assertIsArray($stats);
        $this->assertCount(2, $stats);
    }
}