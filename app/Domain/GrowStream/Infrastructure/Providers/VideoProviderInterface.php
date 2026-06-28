<?php

namespace App\Domain\GrowStream\Infrastructure\Providers;

use Illuminate\Http\UploadedFile;

interface VideoProviderInterface
{
    /**
     * Upload a video file to the provider
     *
     * @param UploadedFile $file
     * @param array $metadata
     * @return ProviderVideoResponse
     */
    public function upload(UploadedFile $file, array $metadata = []): ProviderVideoResponse;

    /**
     * Get video details from provider
     *
     * @param string $providerVideoId
     * @return ProviderVideoResponse
     */
    public function getVideo(string $providerVideoId): ProviderVideoResponse;

    /**
     * Get playback URL (signed or public)
     *
     * @param string $providerVideoId
     * @param bool $signed
     * @param int $expiresIn Expiration time in seconds
     * @return string
     */
    public function getPlaybackUrl(string $providerVideoId, bool $signed = true, int $expiresIn = 86400): string;

    /**
     * Delete video from provider
     *
     * @param string $providerVideoId
     * @return bool
     */
    public function delete(string $providerVideoId): bool;

    /**
     * Get upload status
     *
     * @param string $providerVideoId
     * @return string
     */
    public function getUploadStatus(string $providerVideoId): string;

    /**
     * Get direct upload URL for client-side uploads
     *
     * @param array $metadata
     * @return array
     */
    public function getDirectUploadUrl(array $metadata = []): array;
}
