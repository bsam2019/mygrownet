<?php

namespace Tests\Unit\Middleware;

use App\Domain\Module\Services\SubscriptionService;
use App\Http\Middleware\CheckGrowFinanceSubscription;
use App\Models\User;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class CheckGrowFinanceSubscriptionTest extends TestCase
{
    private CheckGrowFinanceSubscription $middleware;
    private SubscriptionService $subscriptionService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->subscriptionService = Mockery::mock(SubscriptionService::class);
        $this->middleware = new CheckGrowFinanceSubscription($this->subscriptionService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_redirects_to_login_when_not_authenticated(): void
    {
        $request = Request::create('/growfinance/dashboard', 'GET');
        $request->setUserResolver(fn() => null);

        $response = $this->middleware->handle($request, fn($req) => response('OK'));

        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString('login', $response->getTargetUrl());
    }

    /** @test */
    public function it_allows_access_when_no_feature_required(): void
    {
        $user = new User(['id' => 1]);
        $request = Request::create('/growfinance/dashboard', 'GET');
        $request->setUserResolver(fn() => $user);
        
        $this->subscriptionService->shouldReceive('getUserTier')
            ->with($user, 'growfinance')
            ->andReturn('basic');
        
        $this->subscriptionService->shouldReceive('getUserLimits')
            ->with($user, 'growfinance')
            ->andReturn(['accounts' => 10]);

        $response = $this->middleware->handle($request, fn($req) => response('OK'));

        $this->assertEquals('OK', $response->getContent());
    }

    /** @test */
    public function it_allows_access_when_user_has_feature(): void
    {
        $user = new User(['id' => 1]);
        $request = Request::create('/growfinance/invoices', 'GET');
        $request->setUserResolver(fn() => $user);
        
        $this->subscriptionService->shouldReceive('canPerformAction')
            ->with($user, 'invoicing', 'growfinance')
            ->andReturn(true);
        
        $this->subscriptionService->shouldReceive('getUserTier')
            ->with($user, 'growfinance')
            ->andReturn('basic');
        
        $this->subscriptionService->shouldReceive('getUserLimits')
            ->with($user, 'growfinance')
            ->andReturn(['accounts' => 10]);

        $response = $this->middleware->handle($request, fn($req) => response('OK'), 'invoicing');

        $this->assertEquals('OK', $response->getContent());
    }

    /** @test */
    public function it_denies_access_when_user_lacks_feature(): void
    {
        $user = new User(['id' => 1]);
        $request = Request::create('/growfinance/api', 'GET');
        $request->setUserResolver(fn() => $user);
        
        $this->subscriptionService->shouldReceive('canPerformAction')
            ->with($user, 'api_access', 'growfinance')
            ->andReturn(false);
        
        $this->subscriptionService->shouldReceive('getRequiredTierForFeature')
            ->with('api_access', 'growfinance')
            ->andReturn('professional');
        
        $this->subscriptionService->shouldReceive('getUserTier')
            ->with($user, 'growfinance')
            ->andReturn('basic');

        $response = $this->middleware->handle($request, fn($req) => response('OK'), 'api_access');

        $this->assertTrue($response->isRedirect());
    }

    /** @test */
    public function it_returns_json_error_for_api_requests(): void
    {
        $user = new User(['id' => 1]);
        $request = Request::create('/growfinance/api', 'GET');
        $request->setUserResolver(fn() => $user);
        $request->headers->set('Accept', 'application/json');
        
        $this->subscriptionService->shouldReceive('canPerformAction')
            ->with($user, 'api_access', 'growfinance')
            ->andReturn(false);
        
        $this->subscriptionService->shouldReceive('getRequiredTierForFeature')
            ->with('api_access', 'growfinance')
            ->andReturn('professional');
        
        $this->subscriptionService->shouldReceive('getUserTier')
            ->with($user, 'growfinance')
            ->andReturn('basic');

        $response = $this->middleware->handle($request, fn($req) => response('OK'), 'api_access');

        $this->assertEquals(403, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('upgrade_required', $content['error']);
        $this->assertEquals('professional', $content['required_tier']);
        $this->assertEquals('basic', $content['current_tier']);
    }

    /** @test */
    public function it_adds_subscription_info_to_request(): void
    {
        $user = new User(['id' => 1]);
        $request = Request::create('/growfinance/dashboard', 'GET');
        $request->setUserResolver(fn() => $user);
        
        $this->subscriptionService->shouldReceive('getUserTier')
            ->with($user, 'growfinance')
            ->andReturn('professional');
        
        $this->subscriptionService->shouldReceive('getUserLimits')
            ->with($user, 'growfinance')
            ->andReturn(['accounts' => -1, 'transactions' => -1]);

        $capturedRequest = null;
        $this->middleware->handle($request, function ($req) use (&$capturedRequest) {
            $capturedRequest = $req;
            return response('OK');
        });

        $this->assertEquals('professional', $capturedRequest->get('subscription_tier'));
        $this->assertEquals(['accounts' => -1, 'transactions' => -1], $capturedRequest->get('subscription_limits'));
    }
}
