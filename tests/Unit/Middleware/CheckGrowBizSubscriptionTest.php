<?php

namespace Tests\Unit\Middleware;

use App\Domain\Module\Services\SubscriptionService;
use App\Http\Middleware\CheckGrowBizSubscription;
use App\Models\User;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class CheckGrowBizSubscriptionTest extends TestCase
{
    private CheckGrowBizSubscription $middleware;
    private SubscriptionService $subscriptionService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Define required routes for testing
        \Illuminate\Support\Facades\Route::get('/login', fn() => 'login')->name('login');
        \Illuminate\Support\Facades\Route::get('/growbiz/upgrade', fn() => 'upgrade')->name('growbiz.upgrade');
        
        $this->subscriptionService = Mockery::mock(SubscriptionService::class);
        $this->middleware = new CheckGrowBizSubscription($this->subscriptionService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_redirects_to_login_when_not_authenticated(): void
    {
        $request = Request::create('/growbiz/dashboard', 'GET');
        $request->setUserResolver(fn() => null);

        $response = $this->middleware->handle($request, fn($req) => response('OK'));

        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString('login', $response->getTargetUrl());
    }

    /** @test */
    public function it_allows_access_when_no_feature_required(): void
    {
        $user = new User(['id' => 1]);
        $request = Request::create('/growbiz/dashboard', 'GET');
        $request->setUserResolver(fn() => $user);
        
        $this->subscriptionService->shouldReceive('getUserTier')
            ->with($user, 'growbiz')
            ->andReturn('basic');
        
        $this->subscriptionService->shouldReceive('getUserLimits')
            ->with($user, 'growbiz')
            ->andReturn(['employees' => 10]);

        $response = $this->middleware->handle($request, fn($req) => response('OK'));

        $this->assertEquals('OK', $response->getContent());
    }

    /** @test */
    public function it_allows_access_when_user_has_feature(): void
    {
        $user = new User(['id' => 1]);
        $request = Request::create('/growbiz/reports', 'GET');
        $request->setUserResolver(fn() => $user);
        
        $this->subscriptionService->shouldReceive('hasFeature')
            ->with($user, 'advanced_reports', 'growbiz')
            ->andReturn(true);
        
        $this->subscriptionService->shouldReceive('getUserTier')
            ->with($user, 'growbiz')
            ->andReturn('professional');
        
        $this->subscriptionService->shouldReceive('getUserLimits')
            ->with($user, 'growbiz')
            ->andReturn(['employees' => -1]);

        $response = $this->middleware->handle($request, fn($req) => response('OK'), 'advanced_reports');

        $this->assertEquals('OK', $response->getContent());
    }

    /** @test */
    public function it_denies_access_when_user_lacks_feature(): void
    {
        $user = new User(['id' => 1]);
        $request = Request::create('/growbiz/analytics', 'GET');
        $request->setUserResolver(fn() => $user);
        
        $this->subscriptionService->shouldReceive('hasFeature')
            ->with($user, 'analytics', 'growbiz')
            ->andReturn(false);
        
        $this->subscriptionService->shouldReceive('getRequiredTierForFeature')
            ->with('analytics', 'growbiz')
            ->andReturn('professional');
        
        $this->subscriptionService->shouldReceive('getUserTier')
            ->with($user, 'growbiz')
            ->andReturn('basic');

        $response = $this->middleware->handle($request, fn($req) => response('OK'), 'analytics');

        $this->assertTrue($response->isRedirect());
    }

    /** @test */
    public function it_returns_json_error_for_api_requests(): void
    {
        $user = new User(['id' => 1]);
        $request = Request::create('/growbiz/api/analytics', 'GET');
        $request->setUserResolver(fn() => $user);
        $request->headers->set('Accept', 'application/json');
        
        $this->subscriptionService->shouldReceive('hasFeature')
            ->with($user, 'analytics', 'growbiz')
            ->andReturn(false);
        
        $this->subscriptionService->shouldReceive('getRequiredTierForFeature')
            ->with('analytics', 'growbiz')
            ->andReturn('professional');
        
        $this->subscriptionService->shouldReceive('getUserTier')
            ->with($user, 'growbiz')
            ->andReturn('free');

        $response = $this->middleware->handle($request, fn($req) => response('OK'), 'analytics');

        $this->assertEquals(403, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('upgrade_required', $content['error']);
        $this->assertEquals('professional', $content['required_tier']);
        $this->assertEquals('free', $content['current_tier']);
        $this->assertArrayHasKey('upgrade_url', $content);
    }

    /** @test */
    public function it_adds_subscription_info_to_request(): void
    {
        $user = new User(['id' => 1]);
        $request = Request::create('/growbiz/dashboard', 'GET');
        $request->setUserResolver(fn() => $user);
        
        $this->subscriptionService->shouldReceive('getUserTier')
            ->with($user, 'growbiz')
            ->andReturn('business');
        
        $this->subscriptionService->shouldReceive('getUserLimits')
            ->with($user, 'growbiz')
            ->andReturn(['employees' => -1, 'tasks' => -1]);

        $capturedRequest = null;
        $this->middleware->handle($request, function ($req) use (&$capturedRequest) {
            $capturedRequest = $req;
            return response('OK');
        });

        $this->assertEquals('business', $capturedRequest->get('subscription_tier'));
        $this->assertEquals(['employees' => -1, 'tasks' => -1], $capturedRequest->get('subscription_limits'));
    }
}
