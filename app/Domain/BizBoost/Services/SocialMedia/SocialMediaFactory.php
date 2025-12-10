<?php

namespace App\Domain\BizBoost\Services\SocialMedia;

use App\Infrastructure\Persistence\Eloquent\BizBoostIntegrationModel;

class SocialMediaFactory
{
    public static function make(string $provider, ?BizBoostIntegrationModel $integration = null): SocialMediaServiceInterface
    {
        return match ($provider) {
            'facebook' => new FacebookService($integration),
            'instagram' => new InstagramService($integration),
            'whatsapp' => new WhatsAppService($integration),
            'tiktok' => new TikTokService($integration),
            default => throw new \InvalidArgumentException("Unsupported provider: {$provider}"),
        };
    }

    public static function getSupportedProviders(): array
    {
        return [
            'facebook' => [
                'name' => 'Facebook',
                'icon' => 'facebook',
                'color' => '#1877F2',
                'supports' => ['posts', 'images', 'videos', 'carousel', 'analytics'],
            ],
            'instagram' => [
                'name' => 'Instagram',
                'icon' => 'instagram',
                'color' => '#E4405F',
                'supports' => ['posts', 'images', 'videos', 'carousel', 'analytics'],
            ],
            'whatsapp' => [
                'name' => 'WhatsApp Business',
                'icon' => 'whatsapp',
                'color' => '#25D366',
                'supports' => ['messages', 'images', 'videos', 'documents', 'templates'],
            ],
            'tiktok' => [
                'name' => 'TikTok',
                'icon' => 'tiktok',
                'color' => '#000000',
                'supports' => ['videos', 'analytics'],
            ],
        ];
    }

    public static function getProviderRequirements(string $provider): array
    {
        return match ($provider) {
            'facebook' => [
                'requires_page' => true,
                'media_types' => ['image', 'video'],
                'max_images' => 10,
                'max_video_size_mb' => 1024,
                'max_caption_length' => 63206,
            ],
            'instagram' => [
                'requires_page' => true,
                'media_types' => ['image', 'video'],
                'max_images' => 10,
                'max_video_size_mb' => 100,
                'max_video_duration_seconds' => 60,
                'max_caption_length' => 2200,
                'aspect_ratio' => '1:1 to 1.91:1',
            ],
            'whatsapp' => [
                'requires_page' => true,
                'media_types' => ['image', 'video', 'document'],
                'max_file_size_mb' => 16,
                'max_caption_length' => 1024,
            ],
            'tiktok' => [
                'requires_page' => false,
                'media_types' => ['video'],
                'max_video_size_mb' => 287,
                'min_video_duration_seconds' => 3,
                'max_video_duration_seconds' => 180,
                'max_title_length' => 150,
                'max_description_length' => 2200,
            ],
            default => [],
        };
    }
}
