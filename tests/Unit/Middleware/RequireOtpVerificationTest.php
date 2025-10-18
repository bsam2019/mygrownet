<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\RequireOtpVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class RequireOtpVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected RequireOtpVerification $middleware;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new RequireOtpVerification();
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'phone' => '+1234567890',
            'password' => 'password'
        ]);
    }

    public function test_allows_request_when_otp_verified()
    {
        $request = Request::create('/test', 'POST');
        $request->setUserResolver(fn() => $this->user);

        // Set OTP verification session
        Session::put("otp_verified:{$this->user->id}:withdrawal", now());

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        }, 'withdrawal');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['success' => true], json_decode($response->getContent(), true));
    }

    public function test_blocks_request_when_otp_not_verified()
    {
        $request = Request::create('/test', 'POST');
        $request->setUserResolver(fn() => $this->user);

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        }, 'withdrawal');

        $this->assertEquals(403, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('OTP verification required', $responseData['message']);
        $this->assertTrue($responseData['requires_otp']);
        $this->assertEquals('withdrawal', $responseData['purpose']);
        $this->assertEquals('+123****7890', $responseData['user_phone']);
        $this->assertEquals('t***@example.com', $responseData['user_email']);
    }

    public function test_blocks_request_when_otp_verification_expired()
    {
        $request = Request::create('/test', 'POST');
        $request->setUserResolver(fn() => $this->user);

        // Set expired OTP verification session (older than 5 minutes)
        Session::put("otp_verified:{$this->user->id}:withdrawal", now()->subMinutes(6));

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        }, 'withdrawal');

        $this->assertEquals(403, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('OTP verification required', $responseData['message']);
        $this->assertTrue($responseData['requires_otp']);
    }

    public function test_returns_401_when_user_not_authenticated()
    {
        $request = Request::create('/test', 'POST');
        $request->setUserResolver(fn() => null);

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        }, 'withdrawal');

        $this->assertEquals(401, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Unauthenticated', $responseData['message']);
    }

    public function test_allows_non_sensitive_operations_without_otp()
    {
        $request = Request::create('/test', 'POST');
        $request->setUserResolver(fn() => $this->user);

        // Test with a non-sensitive operation purpose
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        }, 'non_sensitive_operation');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['success' => true], json_decode($response->getContent(), true));
    }

    public function test_masks_phone_number_correctly()
    {
        $request = Request::create('/test', 'POST');
        $request->setUserResolver(fn() => $this->user);

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        }, 'withdrawal');

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('+123****7890', $responseData['user_phone']);
    }

    public function test_masks_email_correctly()
    {
        $request = Request::create('/test', 'POST');
        $request->setUserResolver(fn() => $this->user);

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        }, 'withdrawal');

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('t***@example.com', $responseData['user_email']);
    }

    public function test_handles_user_without_phone()
    {
        $userWithoutPhone = User::factory()->create([
            'email' => 'test@example.com',
            'phone' => null,
            'password' => 'password'
        ]);

        $request = Request::create('/test', 'POST');
        $request->setUserResolver(fn() => $userWithoutPhone);

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        }, 'withdrawal');

        $responseData = json_decode($response->getContent(), true);
        $this->assertNull($responseData['user_phone']);
        $this->assertEquals('t***@example.com', $responseData['user_email']);
    }

    public function test_handles_user_without_email()
    {
        $userWithoutEmail = User::factory()->create([
            'email' => null,
            'phone' => '+1234567890',
            'password' => 'password'
        ]);

        $request = Request::create('/test', 'POST');
        $request->setUserResolver(fn() => $userWithoutEmail);

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        }, 'withdrawal');

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('+123****7890', $responseData['user_phone']);
        $this->assertNull($responseData['user_email']);
    }

    public function test_sensitive_operations_require_otp()
    {
        $request = Request::create('/test', 'POST');
        $request->setUserResolver(fn() => $this->user);

        $sensitiveOperations = [
            'withdrawal',
            'sensitive_operation',
            'tier_upgrade',
            'profile_update'
        ];

        foreach ($sensitiveOperations as $operation) {
            $response = $this->middleware->handle($request, function ($req) {
                return response()->json(['success' => true]);
            }, $operation);

            $this->assertEquals(403, $response->getStatusCode(), "Operation '{$operation}' should require OTP");
            
            $responseData = json_decode($response->getContent(), true);
            $this->assertEquals('OTP verification required', $responseData['message']);
            $this->assertEquals($operation, $responseData['purpose']);
        }
    }

    public function test_masks_short_phone_numbers()
    {
        $userWithShortPhone = User::factory()->create([
            'email' => 'test@example.com',
            'phone' => '123',
            'password' => 'password'
        ]);

        $request = Request::create('/test', 'POST');
        $request->setUserResolver(fn() => $userWithShortPhone);

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        }, 'withdrawal');

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('***', $responseData['user_phone']);
    }

    public function test_handles_malformed_email()
    {
        $userWithMalformedEmail = User::factory()->create([
            'email' => 'malformed-email',
            'phone' => '+1234567890',
            'password' => 'password'
        ]);

        $request = Request::create('/test', 'POST');
        $request->setUserResolver(fn() => $userWithMalformedEmail);

        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        }, 'withdrawal');

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('malformed-email', $responseData['user_email']);
    }
}