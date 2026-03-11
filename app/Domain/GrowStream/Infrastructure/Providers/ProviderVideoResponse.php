<?php

namespace App\Domain\GrowStream\Infrastructure\Providers;

class ProviderVideoResponse
{
    public function __construct(
        public string $providerVideoId,
        public string $playbackUrl,
        public ?string $thumbnailUrl = null,
        public ?int $duration = null,
        public ?int $fileSize = null,
        public ?string $resolution = null,
        public string $status = 'ready',
        public array $metadata = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            providerVideoId: $data['provider_video_id'] ?? $data['providerVideoId'] ?? '',
            playbackUrl: $data['playback_url'] ?? $data['playbackUrl'] ?? '',
            thumbnailUrl: $data['thumbnail_url'] ?? $data['thumbnailUrl'] ?? null,
            duration: $data['duration'] ?? null,
            fileSize: $data['file_size'] ?? $data['fileSize'] ?? null,
            resolution: $data['resolution'] ?? null,
            status: $data['status'] ?? 'ready',
            metadata: $data['metadata'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'provider_video_id' => $this->providerVideoId,
            'playback_url' => $this->playbackUrl,
            'thumbnail_url' => $this->thumbnailUrl,
            'duration' => $this->duration,
            'file_size' => $this->fileSize,
            'resolution' => $this->resolution,
            'status' => $this->status,
            'metadata' => $this->metadata,
        ];
    }
}
