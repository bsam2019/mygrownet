<?php

namespace App\Domain\BizBoost\Services\SocialMedia;

use App\Infrastructure\Persistence\Eloquent\BizBoostPostModel;

interface SocialMediaServiceInterface
{
    /**
     * Get OAuth authorization URL
     */
    public function getAuthUrl(string $redirectUri, array $scopes = []): string;

    /**
     * Exchange authorization code for access token
     */
    public function exchangeCodeForToken(string $code, string $redirectUri): array;

    /**
     * Publish a post to the platform
     */
    public function publishPost(BizBoostPostModel $post): array;

    /**
     * Delete a post from the platform
     */
    public function deletePost(string $postId): bool;

    /**
     * Get analytics for a post
     */
    public function getPostAnalytics(string $postId): array;

    /**
     * Refresh the access token
     */
    public function refreshToken(): array;

    /**
     * Validate if the token is still valid
     */
    public function validateToken(): bool;
}
