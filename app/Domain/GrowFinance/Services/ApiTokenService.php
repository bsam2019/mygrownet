<?php

namespace App\Domain\GrowFinance\Services;

use App\Domain\Module\Services\SubscriptionService;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceApiTokenModel;
use App\Models\User;
use Illuminate\Support\Str;

class ApiTokenService
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Get all API tokens for a business
     */
    public function getTokens(int $businessId): array
    {
        return GrowFinanceApiTokenModel::forBusiness($businessId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($token) => [
                'id' => $token->id,
                'name' => $token->name,
                'masked_token' => $token->getMaskedToken(),
                'abilities' => $token->abilities,
                'last_used_at' => $token->last_used_at?->toISOString(),
                'expires_at' => $token->expires_at?->toISOString(),
                'is_active' => $token->is_active,
                'is_expired' => $token->isExpired(),
                'created_at' => $token->created_at->toISOString(),
            ])
            ->toArray();
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

        $token = GrowFinanceApiTokenModel::create([
            'business_id' => $businessId,
            'name' => $name,
            'token' => hash('sha256', $plainToken),
            'abilities' => $abilities,
            'expires_at' => $expiresInDays ? now()->addDays($expiresInDays) : null,
        ]);

        // Return plain token only once - it won't be retrievable later
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

        $token = GrowFinanceApiTokenModel::where('token', $hashedToken)
            ->valid()
            ->first();

        if (!$token) {
            return null;
        }

        // Update last used timestamp
        $token->markAsUsed();

        return [
            'token' => $token,
            'business_id' => $token->business_id,
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
        $token = GrowFinanceApiTokenModel::forBusiness($businessId)
            ->findOrFail($tokenId);

        $token->update(['is_active' => false]);

        return true;
    }

    /**
     * Delete a token permanently
     */
    public function deleteToken(int $businessId, int $tokenId): bool
    {
        $token = GrowFinanceApiTokenModel::forBusiness($businessId)
            ->findOrFail($tokenId);

        $token->delete();

        return true;
    }

    /**
     * Regenerate a token (creates new token, deactivates old)
     */
    public function regenerateToken(int $businessId, int $tokenId): array
    {
        $oldToken = GrowFinanceApiTokenModel::forBusiness($businessId)
            ->findOrFail($tokenId);

        // Deactivate old token
        $oldToken->update(['is_active' => false]);

        // Create new token with same settings
        return $this->createToken(
            $businessId,
            $oldToken->name . ' (regenerated)',
            $oldToken->abilities ?? ['read'],
            $oldToken->expires_at ? now()->diffInDays($oldToken->expires_at) : null
        );
    }

    /**
     * Get available abilities
     */
    public function getAvailableAbilities(): array
    {
        return [
            'general' => GrowFinanceApiTokenModel::ABILITIES,
            'resources' => GrowFinanceApiTokenModel::RESOURCE_ABILITIES,
        ];
    }
}
