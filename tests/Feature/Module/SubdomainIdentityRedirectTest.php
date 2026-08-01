<?php

namespace Tests\Feature\Module;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubdomainIdentityRedirectTest extends TestCase
{
    use RefreshDatabase;

    private const BIZDOCS_HOST = 'http://bizdocs.mygrownet.com';
    private const GROWFINANCE_HOST = 'http://growfinance.mygrownet.com';

    // ── Login flow ───────────────────────────────────────────────────────

    public function test_bizdocs_subdomain_login_redirects_to_identity(): void
    {
        $response = $this->withServerVariables(['HTTP_HOST' => 'bizdocs.mygrownet.com'])
            ->get(self::BIZDOCS_HOST . '/login');

        $response->assertRedirect();
        $target = $response->headers->get('Location');
        $this->assertStringStartsWith(config('platform.identity.login_url'), $target);
        $this->assertStringContainsString('app=bizdocs', $target);
        $this->assertStringContainsString('signature=', $target);
    }

    public function test_growfinance_subdomain_login_redirects_to_identity(): void
    {
        $response = $this->withServerVariables(['HTTP_HOST' => 'growfinance.mygrownet.com'])
            ->get(self::GROWFINANCE_HOST . '/login');

        $response->assertRedirect();
        $target = $response->headers->get('Location');
        $this->assertStringStartsWith(config('platform.identity.login_url'), $target);
        $this->assertStringContainsString('app=growfinance', $target);
        $this->assertStringContainsString('signature=', $target);
    }

    // ── Register flow ────────────────────────────────────────────────────

    public function test_bizdocs_subdomain_register_redirects_to_identity_register(): void
    {
        $response = $this->withServerVariables(['HTTP_HOST' => 'bizdocs.mygrownet.com'])
            ->get(self::BIZDOCS_HOST . '/register');

        $response->assertRedirect();
        $target = $response->headers->get('Location');
        $this->assertStringStartsWith(config('platform.identity.register_url'), $target);
        $this->assertStringContainsString('app=bizdocs', $target);
    }

    public function test_growfinance_subdomain_register_redirects_to_identity_register(): void
    {
        $response = $this->withServerVariables(['HTTP_HOST' => 'growfinance.mygrownet.com'])
            ->get(self::GROWFINANCE_HOST . '/register');

        $response->assertRedirect();
        $target = $response->headers->get('Location');
        $this->assertStringStartsWith(config('platform.identity.register_url'), $target);
        $this->assertStringContainsString('app=growfinance', $target);
    }

    // ── Protected routes require auth ────────────────────────────────────

    public function test_bizdocs_subdomain_protected_route_requires_auth(): void
    {
        config(['platform.identity.app_redirect_enabled.bizdocs' => true]);

        $response = $this->withServerVariables(['HTTP_HOST' => 'bizdocs.mygrownet.com'])
            ->get(self::BIZDOCS_HOST . '/dashboard');

        $this->assertTrue($response->isRedirect());
    }

    public function test_growfinance_subdomain_protected_route_requires_auth(): void
    {
        config(['platform.identity.app_redirect_enabled.growfinance' => true]);

        $response = $this->withServerVariables(['HTTP_HOST' => 'growfinance.mygrownet.com'])
            ->get(self::GROWFINANCE_HOST . '/dashboard');

        $this->assertTrue($response->isRedirect());
    }

    // ── Signature validity ───────────────────────────────────────────────

    public function test_redirect_signature_is_valid_hmac(): void
    {
        $response = $this->withServerVariables(['HTTP_HOST' => 'bizdocs.mygrownet.com'])
            ->get(self::BIZDOCS_HOST . '/login');

        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $params);

        $returnUrl = urldecode($params['return_url']);
        $expected = hash_hmac(
            'sha256',
            $returnUrl . '|' . $params['expires'],
            config('platform.identity.signing_key') ?? '',
        );

        $this->assertSame($expected, $params['signature']);
    }
}