# Social Media API Usage Examples

## Basic Usage

### Publishing a Post

```php
use App\Domain\BizBoost\Services\SocialMedia\SocialMediaFactory;
use App\Infrastructure\Persistence\Eloquent\BizBoostIntegrationModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostPostModel;

// Get the integration
$integration = BizBoostIntegrationModel::where('business_id', $businessId)
    ->where('provider', 'facebook')
    ->where('status', 'active')
    ->first();

// Create service instance
$service = SocialMediaFactory::make('facebook', $integration);

// Publish the post
$result = $service->publishPost($post);

// Result contains platform-specific response
// Facebook: ['id' => 'post_id']
// Instagram: ['id' => 'media_id']
// TikTok: ['video_id' => 'video_id', 'share_url' => 'url']
```

### Using the Job Queue (Recommended)

```php
use App\Jobs\BizBoost\PublishPostToSocialMediaJob;

// Dispatch job for async publishing
PublishPostToSocialMediaJob::dispatch($post, 'facebook');

// Publish to multiple platforms
foreach (['facebook', 'instagram', 'tiktok'] as $platform) {
    PublishPostToSocialMediaJob::dispatch($post, $platform);
}
```

## Platform-Specific Examples

### Facebook

#### Publishing a Text Post
```php
$post = BizBoostPostModel::create([
    'business_id' => $businessId,
    'caption' => 'Check out our new products!',
    'status' => 'draft',
    'platform_targets' => ['facebook'],
]);

$service = SocialMediaFactory::make('facebook', $integration);
$result = $service->publishPost($post);
```

#### Publishing with Image
```php
$post = BizBoostPostModel::create([
    'business_id' => $businessId,
    'caption' => 'New arrival! 🎉',
    'status' => 'draft',
    'platform_targets' => ['facebook'],
]);

// Add media
$post->media()->create([
    'media_type' => 'image',
    'media_url' => 'https://example.com/image.jpg',
    'sort_order' => 1,
]);

$service = SocialMediaFactory::make('facebook', $integration);
$result = $service->publishPost($post);
```

#### Publishing a Carousel
```php
$post = BizBoostPostModel::create([
    'business_id' => $businessId,
    'caption' => 'Swipe to see our collection! 👉',
    'status' => 'draft',
    'platform_targets' => ['facebook'],
]);

// Add multiple images
foreach ($imageUrls as $index => $url) {
    $post->media()->create([
        'media_type' => 'image',
        'media_url' => $url,
        'sort_order' => $index + 1,
    ]);
}

$service = SocialMediaFactory::make('facebook', $integration);
$result = $service->publishPost($post);
```

#### Getting Post Analytics
```php
$service = SocialMediaFactory::make('facebook', $integration);
$analytics = $service->getPostAnalytics($externalPostId);

// Returns:
// [
//     'insights' => [
//         'post_impressions' => 1234,
//         'post_engaged_users' => 56,
//         'post_clicks' => 78,
//         'post_reactions_by_type_total' => [...]
//     ]
// ]
```

### Instagram

#### Publishing an Image
```php
$post = BizBoostPostModel::create([
    'business_id' => $businessId,
    'caption' => 'New collection dropping soon! 🔥 #fashion #style',
    'status' => 'draft',
    'platform_targets' => ['instagram'],
]);

$post->media()->create([
    'media_type' => 'image',
    'media_url' => 'https://example.com/square-image.jpg', // Must be square or portrait
    'sort_order' => 1,
]);

$service = SocialMediaFactory::make('instagram', $integration);
$result = $service->publishPost($post);
```

#### Publishing a Video
```php
$post = BizBoostPostModel::create([
    'business_id' => $businessId,
    'caption' => 'Behind the scenes 🎬',
    'status' => 'draft',
    'platform_targets' => ['instagram'],
]);

$post->media()->create([
    'media_type' => 'video',
    'media_url' => 'https://example.com/video.mp4', // Max 60 seconds
    'sort_order' => 1,
]);

$service = SocialMediaFactory::make('instagram', $integration);
$result = $service->publishPost($post);
// Note: Service automatically waits for video processing
```

#### Publishing a Carousel
```php
$post = BizBoostPostModel::create([
    'business_id' => $businessId,
    'caption' => 'Swipe for more! ➡️',
    'status' => 'draft',
    'platform_targets' => ['instagram'],
]);

// Add up to 10 images
foreach ($imageUrls as $index => $url) {
    $post->media()->create([
        'media_type' => 'image',
        'media_url' => $url,
        'sort_order' => $index + 1,
    ]);
}

$service = SocialMediaFactory::make('instagram', $integration);
$result = $service->publishPost($post);
```

### WhatsApp Business

#### Sending a Text Message
```php
$service = SocialMediaFactory::make('whatsapp', $integration);

$result = $service->sendMessage(
    to: '+260971234567',
    message: 'Hello! Check out our new products.'
);
```

#### Sending an Image with Caption
```php
$service = SocialMediaFactory::make('whatsapp', $integration);

$result = $service->sendMessage(
    to: '+260971234567',
    message: 'Check out this product!',
    mediaUrl: 'https://example.com/product.jpg',
    mediaType: 'image'
);
```

#### Sending a Template Message
```php
$service = SocialMediaFactory::make('whatsapp', $integration);

$result = $service->sendTemplateMessage(
    to: '+260971234567',
    templateName: 'order_confirmation',
    parameters: ['John', 'ORD-12345', 'K250']
);
```

#### Bulk Messaging
```php
$service = SocialMediaFactory::make('whatsapp', $integration);

$customers = ['+260971234567', '+260977654321', '+260969876543'];

$results = $service->sendBulkMessages(
    recipients: $customers,
    message: 'Flash sale! 50% off today only!',
    mediaUrl: 'https://example.com/sale-banner.jpg'
);

// Returns array with results for each recipient
foreach ($results as $phone => $result) {
    if (isset($result['error'])) {
        Log::error("Failed to send to {$phone}: {$result['error']}");
    } else {
        Log::info("Sent to {$phone}: {$result['messages'][0]['id']}");
    }
}
```

### TikTok

#### Publishing a Video
```php
$post = BizBoostPostModel::create([
    'business_id' => $businessId,
    'title' => 'Amazing product demo!',
    'caption' => 'Check out this cool feature! #product #demo #viral',
    'status' => 'draft',
    'platform_targets' => ['tiktok'],
]);

$post->media()->create([
    'media_type' => 'video',
    'media_url' => 'https://example.com/video.mp4', // 3-180 seconds
    'sort_order' => 1,
]);

$service = SocialMediaFactory::make('tiktok', $integration);
$result = $service->publishPost($post);
// Note: Service automatically handles upload and publishing
```

#### Getting User's Videos
```php
$service = SocialMediaFactory::make('tiktok', $integration);

$videos = $service->getUserVideos(maxCount: 20);

foreach ($videos as $video) {
    echo "Video ID: {$video['id']}\n";
    echo "Views: {$video['view_count']}\n";
    echo "Likes: {$video['like_count']}\n";
}
```

## Token Management

### Validating a Token
```php
$service = SocialMediaFactory::make('facebook', $integration);

if (!$service->validateToken()) {
    // Token is invalid, try to refresh
    try {
        $tokenData = $service->refreshToken();
        
        $integration->update([
            'access_token' => $tokenData['access_token'],
            'token_expires_at' => isset($tokenData['expires_in'])
                ? now()->addSeconds($tokenData['expires_in'])
                : null,
        ]);
    } catch (\Exception $e) {
        // Refresh failed, mark as expired
        $integration->update(['status' => 'expired']);
    }
}
```

### Refreshing a Token
```php
$service = SocialMediaFactory::make('tiktok', $integration);

try {
    $tokenData = $service->refreshToken();
    
    $integration->update([
        'access_token' => $tokenData['access_token'],
        'refresh_token' => $tokenData['refresh_token'] ?? $integration->refresh_token,
        'token_expires_at' => isset($tokenData['expires_in'])
            ? now()->addSeconds($tokenData['expires_in'])
            : null,
        'status' => 'active',
    ]);
    
    echo "Token refreshed successfully!";
} catch (\Exception $e) {
    echo "Failed to refresh token: {$e->getMessage()}";
}
```

## Error Handling

### Try-Catch Pattern
```php
use App\Domain\BizBoost\Services\SocialMedia\SocialMediaFactory;

try {
    $service = SocialMediaFactory::make('facebook', $integration);
    $result = $service->publishPost($post);
    
    // Update post with external ID
    $post->update([
        'status' => 'published',
        'published_at' => now(),
        'external_ids' => array_merge(
            $post->external_ids ?? [],
            ['facebook' => $result['id']]
        ),
    ]);
    
} catch (\Exception $e) {
    // Log error
    Log::error('Failed to publish post', [
        'post_id' => $post->id,
        'platform' => 'facebook',
        'error' => $e->getMessage(),
    ]);
    
    // Update post status
    $post->update([
        'status' => 'failed',
        'error_message' => $e->getMessage(),
    ]);
}
```

### Handling Specific Errors
```php
try {
    $service = SocialMediaFactory::make('instagram', $integration);
    $result = $service->publishPost($post);
    
} catch (\Exception $e) {
    $errorMessage = $e->getMessage();
    
    if (str_contains($errorMessage, 'Token expired')) {
        // Try to refresh token
        $tokenData = $service->refreshToken();
        $integration->update(['access_token' => $tokenData['access_token']]);
        
        // Retry publishing
        $result = $service->publishPost($post);
        
    } elseif (str_contains($errorMessage, 'Video processing failed')) {
        // Video issue
        $post->update([
            'status' => 'failed',
            'error_message' => 'Video processing failed. Please check video format and size.',
        ]);
        
    } elseif (str_contains($errorMessage, 'Rate limit')) {
        // Rate limited, retry later
        PublishPostToSocialMediaJob::dispatch($post, 'instagram')->delay(now()->addMinutes(15));
        
    } else {
        // Unknown error
        throw $e;
    }
}
```

## Factory Pattern Usage

### Getting Supported Providers
```php
$providers = SocialMediaFactory::getSupportedProviders();

foreach ($providers as $key => $provider) {
    echo "{$provider['name']}: {$provider['color']}\n";
    echo "Supports: " . implode(', ', $provider['supports']) . "\n";
}
```

### Getting Platform Requirements
```php
$requirements = SocialMediaFactory::getProviderRequirements('instagram');

echo "Max images: {$requirements['max_images']}\n";
echo "Max video size: {$requirements['max_video_size_mb']}MB\n";
echo "Max caption length: {$requirements['max_caption_length']}\n";
```

### Validating Post Before Publishing
```php
function validatePostForPlatform($post, $platform) {
    $requirements = SocialMediaFactory::getProviderRequirements($platform);
    
    // Check media count
    $mediaCount = $post->media()->count();
    if (isset($requirements['max_images']) && $mediaCount > $requirements['max_images']) {
        throw new \Exception("Too many images. Max: {$requirements['max_images']}");
    }
    
    // Check caption length
    if (strlen($post->caption) > $requirements['max_caption_length']) {
        throw new \Exception("Caption too long. Max: {$requirements['max_caption_length']} characters");
    }
    
    // Check media types
    foreach ($post->media as $media) {
        if (!in_array($media->media_type, $requirements['media_types'])) {
            throw new \Exception("Media type {$media->media_type} not supported on {$platform}");
        }
    }
    
    return true;
}
```

## Advanced Usage

### Publishing to Multiple Platforms
```php
$platforms = ['facebook', 'instagram', 'tiktok'];
$results = [];

foreach ($platforms as $platform) {
    try {
        $integration = BizBoostIntegrationModel::where('business_id', $businessId)
            ->where('provider', $platform)
            ->where('status', 'active')
            ->first();
        
        if (!$integration) {
            $results[$platform] = ['error' => 'Not connected'];
            continue;
        }
        
        $service = SocialMediaFactory::make($platform, $integration);
        $result = $service->publishPost($post);
        
        $results[$platform] = ['success' => true, 'data' => $result];
        
    } catch (\Exception $e) {
        $results[$platform] = ['error' => $e->getMessage()];
    }
}

return $results;
```

### Scheduled Publishing with Queue
```php
use App\Jobs\BizBoost\PublishPostToSocialMediaJob;

$post = BizBoostPostModel::create([
    'business_id' => $businessId,
    'caption' => 'Scheduled post!',
    'status' => 'scheduled',
    'scheduled_at' => now()->addHours(2),
    'platform_targets' => ['facebook', 'instagram'],
]);

// Schedule jobs to run at the scheduled time
foreach ($post->platform_targets as $platform) {
    PublishPostToSocialMediaJob::dispatch($post, $platform)
        ->delay($post->scheduled_at);
}
```

## Testing

### Mocking Services in Tests
```php
use App\Domain\BizBoost\Services\SocialMedia\FacebookService;
use Mockery;

public function test_post_publishing()
{
    $mockService = Mockery::mock(FacebookService::class);
    $mockService->shouldReceive('publishPost')
        ->once()
        ->andReturn(['id' => 'test_post_id']);
    
    $this->app->instance(FacebookService::class, $mockService);
    
    // Test your code...
}
```

### Testing with Fake Data
```php
use App\Infrastructure\Persistence\Eloquent\BizBoostPostModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostIntegrationModel;

public function test_publishing_flow()
{
    $integration = BizBoostIntegrationModel::factory()->create([
        'provider' => 'facebook',
        'status' => 'active',
    ]);
    
    $post = BizBoostPostModel::factory()->create([
        'business_id' => $integration->business_id,
        'platform_targets' => ['facebook'],
    ]);
    
    // Test publishing...
}
```
