<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\MobileMoneyService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class MobileMoneyServiceTest extends TestCase
{
    protected MobileMoneyService $mobileMoneyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mobileMoneyService = new MobileMoneyService();
    }

    public function test_detects_mtn_provider_correctly()
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->mobileMoneyService);
        $method = $reflection->getMethod('detectProvider');
        $method->setAccessible(true);

        // Test MTN numbers
        $this->assertEquals('mtn', $method->invoke($this->mobileMoneyService, '+260967123456'));
        $this->assertEquals('mtn', $method->invoke($this->mobileMoneyService, '0967123456'));
        $this->assertEquals('mtn', $method->invoke($this->mobileMoneyService, '+260777123456'));
        $this->assertEquals('mtn', $method->invoke($this->mobileMoneyService, '0777123456'));
    }

    public function test_detects_airtel_provider_correctly()
    {
        $reflection = new \ReflectionClass($this->mobileMoneyService);
        $method = $reflection->getMethod('detectProvider');
        $method->setAccessible(true);

        // Test Airtel numbers
        $this->assertEquals('airtel', $method->invoke($this->mobileMoneyService, '+260977123456'));
        $this->assertEquals('airtel', $method->invoke($this->mobileMoneyService, '0977123456'));
    }

    public function test_detects_zamtel_provider_correctly()
    {
        $reflection = new \ReflectionClass($this->mobileMoneyService);
        $method = $reflection->getMethod('detectProvider');
        $method->setAccessible(true);

        // Test Zamtel numbers
        $this->assertEquals('zamtel', $method->invoke($this->mobileMoneyService, '+260955123456'));
        $this->assertEquals('zamtel', $method->invoke($this->mobileMoneyService, '0955123456'));
    }

    public function test_formats_phone_number_correctly()
    {
        $reflection = new \ReflectionClass($this->mobileMoneyService);
        $method = $reflection->getMethod('formatPhoneNumber');
        $method->setAccessible(true);

        // Test various formats
        $this->assertEquals('260977123456', $method->invoke($this->mobileMoneyService, '+260977123456'));
        $this->assertEquals('260977123456', $method->invoke($this->mobileMoneyService, '0977123456'));
        $this->assertEquals('260977123456', $method->invoke($this->mobileMoneyService, '977123456'));
        $this->assertEquals('260977123456', $method->invoke($this->mobileMoneyService, '260977123456'));
    }

    public function test_sends_mtn_payment_successfully()
    {
        // Mock MTN token request
        Http::fake([
            'https://sandbox.momodeveloper.mtn.com/disbursement/token/' => Http::response([
                'access_token' => 'mock-access-token',
                'token_type' => 'Bearer',
                'expires_in' => 3600
            ]),
            'https://sandbox.momodeveloper.mtn.com/disbursement/v1_0/transfer' => Http::response([], 202),
            'https://sandbox.momodeveloper.mtn.com/disbursement/v1_0/transfer/*' => Http::response([
                'status' => 'SUCCESSFUL',
                'financialTransactionId' => 'mock-transaction-id'
            ])
        ]);

        // Set MTN configuration
        config([
            'mygrownet.mobile_money.mtn' => [
                'subscription_key' => 'test-key',
                'user_id' => 'test-user',
                'api_key' => 'test-api-key',
                'environment' => 'sandbox',
                'token_url' => 'https://sandbox.momodeveloper.mtn.com/disbursement/token/',
                'disbursement_url' => 'https://sandbox.momodeveloper.mtn.com/disbursement/v1_0/transfer'
            ]
        ]);

        $paymentData = [
            'phone_number' => '+260967123456',
            'amount' => 100.00,
            'reference' => 'TEST-REF-123',
            'description' => 'Test payment'
        ];

        $result = $this->mobileMoneyService->sendPayment($paymentData);

        $this->assertTrue($result['success']);
        $this->assertEquals('mtn', $result['provider']);
        $this->assertEquals('TEST-REF-123', $result['external_reference']);
    }

    public function test_sends_airtel_payment_successfully()
    {
        // Mock Airtel requests
        Http::fake([
            'https://openapiuat.airtel.africa/auth/oauth2/token' => Http::response([
                'access_token' => 'mock-airtel-token',
                'token_type' => 'Bearer',
                'expires_in' => 3600
            ]),
            'https://openapiuat.airtel.africa/standard/v1/disbursements/' => Http::response([
                'status' => [
                    'success' => true,
                    'code' => '200',
                    'message' => 'Success'
                ],
                'data' => [
                    'transaction' => [
                        'id' => 'airtel-transaction-123'
                    ]
                ]
            ])
        ]);

        config([
            'mygrownet.mobile_money.airtel' => [
                'client_id' => 'test-client-id',
                'client_secret' => 'test-client-secret',
                'token_url' => 'https://openapiuat.airtel.africa/auth/oauth2/token',
                'disbursement_url' => 'https://openapiuat.airtel.africa/standard/v1/disbursements/'
            ]
        ]);

        $paymentData = [
            'phone_number' => '+260977123456',
            'amount' => 150.00,
            'reference' => 'AIRTEL-REF-456',
            'description' => 'Airtel test payment'
        ];

        $result = $this->mobileMoneyService->sendPayment($paymentData);

        $this->assertTrue($result['success']);
        $this->assertEquals('airtel', $result['provider']);
        $this->assertEquals('airtel-transaction-123', $result['external_reference']);
    }

    public function test_handles_mtn_payment_failure()
    {
        // Mock failed MTN requests
        Http::fake([
            'https://sandbox.momodeveloper.mtn.com/disbursement/token/' => Http::response([
                'access_token' => 'mock-access-token'
            ]),
            'https://sandbox.momodeveloper.mtn.com/disbursement/v1_0/transfer' => Http::response([
                'code' => '400',
                'message' => 'Bad Request'
            ], 400)
        ]);

        config([
            'mygrownet.mobile_money.mtn' => [
                'subscription_key' => 'test-key',
                'user_id' => 'test-user',
                'api_key' => 'test-api-key',
                'token_url' => 'https://sandbox.momodeveloper.mtn.com/disbursement/token/',
                'disbursement_url' => 'https://sandbox.momodeveloper.mtn.com/disbursement/v1_0/transfer'
            ]
        ]);

        $paymentData = [
            'phone_number' => '+260967123456',
            'amount' => 100.00,
            'reference' => 'FAIL-REF-123',
            'description' => 'Failed payment test'
        ];

        $result = $this->mobileMoneyService->sendPayment($paymentData);

        $this->assertFalse($result['success']);
        $this->assertEquals('mtn', $result['provider']);
        $this->assertStringContainsString('MTN payment failed', $result['error']);
    }

    public function test_caches_access_tokens()
    {
        Cache::flush();

        // Mock token request
        Http::fake([
            'https://sandbox.momodeveloper.mtn.com/disbursement/token/' => Http::response([
                'access_token' => 'cached-token-123'
            ])
        ]);

        config([
            'mygrownet.mobile_money.mtn' => [
                'subscription_key' => 'test-key',
                'user_id' => 'test-user',
                'api_key' => 'test-api-key',
                'token_url' => 'https://sandbox.momodeveloper.mtn.com/disbursement/token/'
            ]
        ]);

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->mobileMoneyService);
        $method = $reflection->getMethod('getMTNAccessToken');
        $method->setAccessible(true);

        // First call should make HTTP request
        $token1 = $method->invoke($this->mobileMoneyService);
        
        // Second call should use cached token
        $token2 = $method->invoke($this->mobileMoneyService);

        $this->assertEquals('cached-token-123', $token1);
        $this->assertEquals('cached-token-123', $token2);
        
        // Verify token is cached
        $this->assertTrue(Cache::has('mtn_access_token'));
        
        // Should only make one HTTP request due to caching
        Http::assertSentCount(1);
    }

    public function test_validates_configuration()
    {
        // Test with missing configuration
        config(['mygrownet.mobile_money' => []]);

        $validation = $this->mobileMoneyService->validateConfiguration();

        $this->assertFalse($validation['valid']);
        $this->assertNotEmpty($validation['errors']);
        $this->assertContains('MTN subscription key not configured', $validation['errors']);
        $this->assertContains('Airtel client ID not configured', $validation['errors']);
        $this->assertContains('Zamtel username not configured', $validation['errors']);
    }

    public function test_validates_configuration_success()
    {
        // Test with complete configuration
        config([
            'mygrownet.mobile_money' => [
                'mtn' => ['subscription_key' => 'test-key'],
                'airtel' => ['client_id' => 'test-client'],
                'zamtel' => ['username' => 'test-user']
            ]
        ]);

        $validation = $this->mobileMoneyService->validateConfiguration();

        $this->assertTrue($validation['valid']);
        $this->assertEmpty($validation['errors']);
    }

    public function test_sends_generic_payment_in_development()
    {
        // Set environment to local
        app()->detectEnvironment(function () {
            return 'local';
        });

        $paymentData = [
            'phone_number' => '+260999123456', // Unknown provider
            'amount' => 75.00,
            'reference' => 'DEV-REF-789',
            'description' => 'Development test payment'
        ];

        $result = $this->mobileMoneyService->sendPayment($paymentData);

        $this->assertTrue($result['success']);
        $this->assertEquals('simulation', $result['provider']);
        $this->assertEquals('DEV-REF-789', $result['external_reference']);
        $this->assertStringContainsString('Payment simulated successfully', $result['response']['message']);
    }

    public function test_fails_generic_payment_in_production()
    {
        // Set environment to production
        app()->detectEnvironment(function () {
            return 'production';
        });

        $paymentData = [
            'phone_number' => '+260999123456', // Unknown provider
            'amount' => 75.00,
            'reference' => 'PROD-REF-789',
            'description' => 'Production test payment'
        ];

        $result = $this->mobileMoneyService->sendPayment($paymentData);

        $this->assertFalse($result['success']);
        $this->assertEquals('unknown', $result['provider']);
        $this->assertStringContainsString('No supported mobile money provider detected', $result['error']);
    }
}