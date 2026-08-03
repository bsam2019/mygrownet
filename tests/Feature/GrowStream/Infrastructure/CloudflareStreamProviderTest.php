<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowStream\Infrastructure;

use App\Domain\GrowStream\Exceptions\UploadFailedException;
use App\Domain\GrowStream\Infrastructure\Providers\CloudflareStreamProvider;
use App\Domain\GrowStream\Infrastructure\Providers\VideoProviderFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CloudflareStreamProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('growstream.providers.cloudflare', [
            'account_id' => 'test-account',
            'api_token' => 'test-token',
            'customer_subdomain' => null,
            'signing_key_id' => null,
            'signing_key' => null,
        ]);
    }

    #[Test]
    public function factory_resolves_cloudflare_provider(): void
    {
        $provider = VideoProviderFactory::make('cloudflare');

        $this->assertInstanceOf(CloudflareStreamProvider::class, $provider);
    }

    #[Test]
    public function get_video_maps_ready_state(): void
    {
        Http::fake([
            'api.cloudflare.com/*' => Http::response([
                'success' => true,
                'result' => [
                    'uid' => 'abc123',
                    'status' => ['state' => 'ready'],
                    'duration' => 120.5,
                    'size' => 1024,
                ],
            ]),
        ]);

        $provider = new CloudflareStreamProvider;
        $video = $provider->getVideo('abc123');

        $this->assertSame('ready', $video->status);
        $this->assertSame('abc123', $video->providerVideoId);
        $this->assertSame(121, $video->duration);
        $this->assertSame(1024, $video->fileSize);
        $this->assertStringContainsString('abc123', $video->playbackUrl);
    }

    #[Test]
    public function get_video_maps_error_state_to_failed(): void
    {
        Http::fake([
            'api.cloudflare.com/*' => Http::response([
                'success' => true,
                'result' => ['status' => ['state' => 'error']],
            ]),
        ]);

        $provider = new CloudflareStreamProvider;
        $this->assertSame('failed', $provider->getVideo('abc123')->status);
    }

    #[Test]
    public function get_video_maps_queued_state_to_processing(): void
    {
        Http::fake([
            'api.cloudflare.com/*' => Http::response([
                'success' => true,
                'result' => ['status' => ['state' => 'queued']],
            ]),
        ]);

        $provider = new CloudflareStreamProvider;
        $this->assertSame('processing', $provider->getVideo('abc123')->status);
    }

    #[Test]
    public function get_video_throws_on_api_error(): void
    {
        Http::fake([
            'api.cloudflare.com/*' => Http::response(['success' => false], 404),
        ]);

        $provider = new CloudflareStreamProvider;

        $this->expectException(UploadFailedException::class);
        $provider->getVideo('missing');
    }

    #[Test]
    public function get_playback_url_returns_unsigned_when_no_signing_key(): void
    {
        $provider = new CloudflareStreamProvider;
        $url = $provider->getPlaybackUrl('abc123');

        $this->assertStringContainsString('abc123/manifest/video.m3u8', $url);
        $this->assertStringNotContainsString('token=', $url);
    }

    #[Test]
    public function get_playback_url_signs_when_signing_key_configured(): void
    {
        config()->set('growstream.providers.cloudflare', [
            'account_id' => 'test-account',
            'api_token' => 'test-token',
            'customer_subdomain' => 'customer-abc',
            'signing_key_id' => 'key-1',
            'signing_key' => 'secret',
        ]);

        $provider = new CloudflareStreamProvider;
        $url = $provider->getPlaybackUrl('abc123', signed: true, expiresIn: 3600);

        $this->assertStringContainsString('customer-abc.cloudflarestream.com', $url);
        $this->assertMatchesRegularExpression('/token=\d+-[A-Za-z0-9_-]+/', $url);
    }

    #[Test]
    public function get_playback_url_uses_customer_subdomain_when_configured(): void
    {
        config()->set('growstream.providers.cloudflare', [
            'account_id' => 'test-account',
            'api_token' => 'test-token',
            'customer_subdomain' => 'customer-abc',
            'signing_key_id' => null,
            'signing_key' => null,
        ]);

        $provider = new CloudflareStreamProvider;
        $url = $provider->getPlaybackUrl('abc123', signed: false);

        $this->assertStringContainsString('customer-abc.cloudflarestream.com/abc123', $url);
    }

    #[Test]
    public function get_thumbnail_url_uses_customer_subdomain(): void
    {
        config()->set('growstream.providers.cloudflare', [
            'account_id' => 'test-account',
            'api_token' => 'test-token',
            'customer_subdomain' => 'customer-abc',
            'signing_key_id' => null,
            'signing_key' => null,
        ]);

        Http::fake([
            'api.cloudflare.com/*' => Http::response([
                'success' => true,
                'result' => ['status' => ['state' => 'ready']],
            ]),
        ]);

        $provider = new CloudflareStreamProvider;
        $video = $provider->getVideo('abc123');

        $this->assertStringContainsString('customer-abc.cloudflarestream.com/abc123/thumbnails/thumbnail.jpg', $video->thumbnailUrl);
    }

    #[Test]
    public function delete_returns_true_on_success(): void
    {
        Http::fake([
            'api.cloudflare.com/*' => Http::response(['success' => true]),
        ]);

        $provider = new CloudflareStreamProvider;
        $this->assertTrue($provider->delete('abc123'));
    }

    #[Test]
    public function delete_returns_true_when_already_gone(): void
    {
        Http::fake([
            'api.cloudflare.com/*' => Http::response(['success' => false], 404),
        ]);

        $provider = new CloudflareStreamProvider;
        $this->assertTrue($provider->delete('abc123'));
    }

    #[Test]
    public function get_direct_upload_url_creates_direct_upload(): void
    {
        Http::fake([
            'api.cloudflare.com/*' => Http::response([
                'success' => true,
                'result' => [
                    'uid' => 'new-uid',
                    'uploadURL' => 'https://upload.cloudflarestream.com/xyz',
                    'expiry' => '2026-01-01T00:00:00Z',
                ],
            ]),
        ]);

        $provider = new CloudflareStreamProvider;
        $result = $provider->getDirectUploadUrl(['max_duration_seconds' => 600]);

        $this->assertSame('PUT', $result['method']);
        $this->assertSame('new-uid', $result['provider_video_id']);
        $this->assertSame('https://upload.cloudflarestream.com/xyz', $result['upload_url']);
        $this->assertSame('2026-01-01T00:00:00Z', $result['expires_at']);
    }

    #[Test]
    public function get_upload_status_maps_to_failed_on_exception(): void
    {
        Http::fake([
            'api.cloudflare.com/*' => Http::response(['success' => false], 500),
        ]);

        $provider = new CloudflareStreamProvider;
        $this->assertSame('failed', $provider->getUploadStatus('abc123'));
    }

    #[Test]
    public function upload_streams_file_and_returns_response(): void
    {
        Http::fake([
            'api.cloudflare.com/*/direct_upload' => Http::response([
                'success' => true,
                'result' => [
                    'uid' => 'uploaded-uid',
                    'uploadURL' => 'https://upload.cloudflarestream.com/xyz',
                ],
            ]),
            'https://upload.cloudflarestream.com/*' => Http::response('', 200),
        ]);

        $file = UploadedFile::fake()->create('video.mp4', 1024);

        $provider = new CloudflareStreamProvider;
        $response = $provider->upload($file, ['max_duration_seconds' => 120]);

        $this->assertSame('uploaded-uid', $response->providerVideoId);
        $this->assertSame('processing', $response->status);
        $this->assertSame(1048576, $response->fileSize);
    }

    #[Test]
    public function upload_throws_when_upload_fails_and_cleans_up(): void
    {
        Http::fake([
            'api.cloudflare.com/*/direct_upload' => Http::response([
                'success' => true,
                'result' => [
                    'uid' => 'uploaded-uid',
                    'uploadURL' => 'https://upload.cloudflarestream.com/xyz',
                ],
            ]),
            'https://upload.cloudflarestream.com/*' => Http::response('', 500),
            'api.cloudflare.com/*/uploaded-uid' => Http::response(['success' => true]),
        ]);

        $file = UploadedFile::fake()->create('video.mp4', 1024);

        $provider = new CloudflareStreamProvider;

        $this->expectException(UploadFailedException::class);
        $provider->upload($file);
    }
}
