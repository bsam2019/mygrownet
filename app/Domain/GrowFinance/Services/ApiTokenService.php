<?php

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\ApiToken;
use App\Domain\GrowFinance\Repositories\ApiTokenRepositoryInterface;
use App\Domain\Module\Services\SubscriptionService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApiTokenService
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private ApiTokenRepositoryInterface $apiTokenRepo,
    ) {}

    /**
     * Get all API tokens for a business
     */
    public function getTokens(int $businessId): array
    {
        $tokens = $this->apiTokenRepo->findByBusiness($businessId);

        $sorted = $tokens;
        usort($sorted, fn(ApiToken $a, ApiToken $b) => ($b->createdAt?->getTimestamp() ?? 0) <=> ($a->createdAt?->getTimestamp() ?? 0));

        return array_map(fn(ApiToken $token) => [
            'id' => $token->id,
            'name' => $token->name,
            'masked_token' => substr($token->token, 0, 8) . '...' . substr($token->token, -4),
            'abilities' => $token->abilities,
            'last_used_at' => $token->lastUsedAt?->format('c'),
            'expires_at' => $token->expiresAt?->format('c'),
            'is_active' => $token->isActive,
            'is_expired' => $token->isExpired(),
            'created_at' => $token->createdAt?->format('c'),
        ], $sorted);
    }

    /**
     * Check if user can create API tokens
     */
    public function canCreateToken(User $user): array
    {
        if (!$this->subscriptionService->canPerformAction($user, 'api_access')) {
            return [
                'allowed' => false,
                'reason' => 'API access is available on Business plan. Please upgrade.',
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Create a new API token
     */
    public function createToken(
        int $businessId,
        string $name,
        array $abilities = ['read'],
        ?int $expiresInDays = null
    ): array {
        $plainToken = Str::random(64);

        $token = $this->apiTokenRepo->save(new ApiToken(
            id: null,
            businessId: $businessId,
            name: $name,
            token: hash('sha256', $plainToken),
            abilities: $abilities,
            expiresAt: $expiresInDays ? new \DateTimeImmutable('+' . $expiresInDays . ' days') : null,
        ));

        return [
            'token' => $token,
            'plain_token' => $plainToken,
            'message' => 'Store this token securely. It will not be shown again.',
        ];
    }

    /**
     * Validate an API token
     */
    public function validateToken(string $plainToken): ?array
    {
        $hashedToken = hash('sha256', $plainToken);

        $token = $this->apiTokenRepo->findByToken($hashedToken);

        if (!$token || !$token->isActive || $token->isExpired()) {
            return null;
        }

        $this->apiTokenRepo->save(new ApiToken(
            id: $token->id,
            businessId: $token->businessId,
            name: $token->name,
            token: $token->token,
            abilities: $token->abilities,
            lastUsedAt: new \DateTimeImmutable(),
            expiresAt: $token->expiresAt,
            isActive: $token->isActive,
            createdAt: $token->createdAt,
            updatedAt: null,
        ));

        return [
            'token' => $token,
            'business_id' => $token->businessId,
            'abilities' => $token->abilities,
        ];
    }

    /**
     * Check if token has ability
     */
    public function tokenHasAbility(string $plainToken, string $ability): bool
    {
        $result = $this->validateToken($plainToken);

        if (!$result) {
            return false;
        }

        return $result['token']->hasAbility($ability);
    }

    /**
     * Revoke (deactivate) a token
     */
    public function revokeToken(int $businessId, int $tokenId): bool
    {
        $token = $this->apiTokenRepo->findById($tokenId);
        if (!$token || $token->businessId !== $businessId) {
            return false;
        }

        $this->apiTokenRepo->save(new ApiToken(
            id: $token->id,
            businessId: $token->businessId,
            name: $token->name,
            token: $token->token,
            abilities: $token->abilities,
            lastUsedAt: $token->lastUsedAt,
            expiresAt: $token->expiresAt,
            isActive: false,
            createdAt: $token->createdAt,
            updatedAt: null,
        ));

        return true;
    }

    /**
     * Delete a token permanently
     */
    public function deleteToken(int $businessId, int $tokenId): bool
    {
        $deleted = DB::table('growfinance_api_tokens')
            ->where('id', $tokenId)
            ->where('business_id', $businessId)
            ->delete();

        return $deleted > 0;
    }

    /**
     * Regenerate a token (creates new token, deactivates old)
     */
    public function regenerateToken(int $businessId, int $tokenId): array
    {
        $oldToken = $this->apiTokenRepo->findById($tokenId);
        if (!$oldToken || $oldToken->businessId !== $businessId) {
            throw new \RuntimeException('Token not found');
        }

        $this->apiTokenRepo->save(new ApiToken(
            id: $oldToken->id,
            businessId: $oldToken->businessId,
            name: $oldToken->name,
            token: $oldToken->token,
            abilities: $oldToken->abilities,
            lastUsedAt: $oldToken->lastUsedAt,
            expiresAt: $oldToken->expiresAt,
            isActive: false,
            createdAt: $oldToken->createdAt,
            updatedAt: null,
        ));

        return $this->createToken(
            $businessId,
            $oldToken->name . ' (regenerated)',
            $oldToken->abilities ?? ['read'],
            $oldToken->expiresAt ? (int) ceil((new \DateTimeImmutable())->diff($oldToken->expiresAt)->days) : null
        );
    }

    /**
     * Get available abilities
     */
    public function getAvailableAbilities(): array
    {
        return [
            'general' => [
                'read' => 'Read data (invoices, expenses, customers, reports)',
                'write' => 'Create and update records',
                'delete' => 'Delete records',
            ],
            'resources' => [
                'invoices:read', 'invoices:write', 'invoices:delete',
                'expenses:read', 'expenses:write', 'expenses:delete',
                'customers:read', 'customers:write', 'customers:delete',
                'vendors:read', 'vendors:write', 'vendors:delete',
                'reports:read',
                'accounts:read', 'accounts:write',
            ],
        ];
    }
}
