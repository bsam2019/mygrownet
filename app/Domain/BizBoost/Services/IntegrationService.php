<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Services;

use App\Domain\BizBoost\Entities\Integration;
use App\Domain\BizBoost\Repositories\IntegrationRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;

class IntegrationService
{
    public function __construct(
        private IntegrationRepositoryInterface $integrationRepo,
    ) {}

    public function getIntegrations(int $businessId): array
    {
        return $this->integrationRepo->findByBusiness($businessId);
    }

    public function getActiveIntegrations(int $businessId): array
    {
        return $this->integrationRepo->findActiveByBusiness($businessId);
    }

    public function findByProvider(int $businessId, string $provider): ?Integration
    {
        return $this->integrationRepo->findByProvider($businessId, $provider);
    }

    public function disconnect(int $id): void
    {
        $data = [
            'status' => 'revoked',
            'access_token' => null,
            'refresh_token' => null,
        ];
        $existing = $this->integrationRepo->findById($id);
        if ($existing) {
            $merged = array_merge($existing->toArray(), $data);
            $merged['id'] = $id;
            $this->integrationRepo->save(Integration::reconstitute($merged));
        }
    }

    public function exchangeFacebookCode(string $code, string $redirectUri): array
    {
        $response = Http::get('https://graph.facebook.com/v25.0/oauth/access_token', [
            'client_id' => config('services.facebook.client_id'),
            'client_secret' => config('services.facebook.client_secret'),
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to get Facebook access token');
        }

        return $response->json();
    }

    public function getLongLivedToken(string $accessToken): string
    {
        $response = Http::get('https://graph.facebook.com/v25.0/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => config('services.facebook.client_id'),
            'client_secret' => config('services.facebook.client_secret'),
            'fb_exchange_token' => $accessToken,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['access_token'];
        }

        return $accessToken;
    }

    public function getFacebookPages(string $accessToken): array
    {
        $response = Http::get('https://graph.facebook.com/v25.0/me/accounts', [
            'access_token' => $accessToken,
            'fields' => 'id,name,access_token,instagram_business_account',
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to get Facebook pages');
        }

        return $response->json()['data'] ?? [];
    }

    public function saveFacebookPage(int $businessId, array $page, string $accessToken): void
    {
        $this->integrationRepo->save(new Integration(
            id: null,
            businessId: $businessId,
            provider: 'facebook',
            providerUserId: null,
            providerPageId: $page['id'],
            providerPageName: $page['name'],
            accessToken: Crypt::encryptString($page['access_token'] ?? $accessToken),
            refreshToken: null,
            tokenExpiresAt: now()->addDays(60)->toDateTimeString(),
            scopes: null,
            meta: ['has_instagram' => isset($page['instagram_business_account'])],
            catalogId: null,
            whatsappCatalogSettings: null,
            status: 'active',
            connectedAt: now()->toDateTimeString(),
            lastUsedAt: null,
            createdAt: null,
            updatedAt: null,
        ));

        if (isset($page['instagram_business_account'])) {
            $this->integrationRepo->save(new Integration(
                id: null,
                businessId: $businessId,
                provider: 'instagram',
                providerUserId: null,
                providerPageId: $page['instagram_business_account']['id'],
                providerPageName: $page['name'] . ' (Instagram)',
                accessToken: Crypt::encryptString($page['access_token'] ?? $accessToken),
                refreshToken: null,
                tokenExpiresAt: now()->addDays(60)->toDateTimeString(),
                scopes: null,
                meta: ['facebook_page_id' => $page['id']],
                catalogId: null,
                whatsappCatalogSettings: null,
                status: 'active',
                connectedAt: now()->toDateTimeString(),
                lastUsedAt: null,
                createdAt: null,
                updatedAt: null,
            ));
        }
    }
}