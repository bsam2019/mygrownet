# Social Media API Integration for BizBoost

**Last Updated:** December 7, 2025  
**Status:** Production Ready

## Overview

BizBoost now supports automated posting to Facebook, Instagram, WhatsApp Business, and TikTok through official API integrations. This document covers setup, configuration, and usage.

## Supported Platforms

### 1. Facebook
- **Features**: Posts, images, videos, carousels, analytics
- **Requirements**: Facebook Page
- **API**: Facebook Graph API v18.0
- **OAuth**: Facebook Login

### 2. Instagram
- **Features**: Posts, images, videos, carousels, analytics
- **Requirements**: Instagram Business Account connected to Facebook Page
- **API**: Instagram Graph API (via Facebook)
- **OAuth**: Facebook Login

### 3. WhatsApp Business
- **Features**: Broadcast messages, templates, media sharing
- **Requirements**: WhatsApp Business Account
- **API**: WhatsApp Business API (via Facebook)
- **OAuth**: Facebook Login

### 4. TikTok
- **Features**: Video posts, analytics
- **Requirements**: TikTok account
- **API**: TikTok for Developers API v2
- **OAuth**: TikTok Login

## Setup Instructions

### 1. Facebook/Instagram/WhatsApp Setup

#### Step 1: Create Facebook App
1. Go to [Facebook Developers](https://developers.facebook.com/)
2. Click "My Apps" → "Create App"
3. Select "Business" type
4. Fill in app details

#### Step 2: Configure App
1. Add "Facebook Login" product
2. Add "Instagram Graph API" product (for Instagram)
3. Add "WhatsApp" product (for WhatsApp)
4. Configure OAuth redirect URIs:
   ```
   https://yourdomain.com/bizboost/integrations/facebook/callback
   https://yourdomain.com/bizboost/integrations/instagram/callback
   https://yourdomain.com/bizboost/integrations/whatsapp/callback
   ```

#### Step 3: Get Credentials
1. Go to Settings → Basic
2. Copy "App ID" (this is your `FACEBOOK_CLIENT_ID`)
3. Copy "App Secret" (this is your `FACEBOOK_CLIENT_SECRET`)

#### Step 4: Request Permissions
For production, request these permissions through App Review:
- `pages_show_list`
- `pages_read_engagement`
- `pages_manage_posts`
- `pages_manage_engagement`
- `instagram_basic`
- `instagram_content_publish`
- `whatsapp_business_management`
- `whatsapp_business_messaging`

### 2. TikTok Setup

#### Step 1: Register as TikTok Developer
1. Go to [TikTok for Developers](https://developers.tiktok.com/)
2. Sign up and verify your account
3. Create a new app

#### Step 2: Configure App
1. Select "Login Kit" and "Content Posting API"
2. Add redirect URI:
   ```
   https://yourdomain.com/bizboost/integrations/tiktok/callback
   ```

#### Step 3: Get Credentials
1. Go to your app dashboard
2. Copy "Client Key" (this is your `TIKTOK_CLIENT_KEY`)
3. Copy "Client Secret" (this is your `TIKTOK_CLIENT_SECRET`)

#### Step 4: Request Scopes
Request these scopes:
- `user.info.basic`
- `video.list`
- `video.upload`
- `video.publish`

### 3. Environment Configuration

Add to your `.env` file:

```env
# Facebook/Instagram/WhatsApp
FACEBOOK_CLIENT_ID=your_facebook_app_id
FACEBOOK_CLIENT_SECRET=your_facebook_app_secret
FACEBOOK_API_VERSION=v18.0

# TikTok
TIKTOK_CLIENT_KEY=your_tiktok_client_key
TIKTOK_CLIENT_SECRET=your_tiktok_client_secret
```

## Usage

### Connecting Accounts

Users can connect their social media accounts from:
```
BizBoost → Integrations → Connect [Platform]
```

The OAuth flow will:
1. Redirect to platform authorization
2. User grants permissions
3. Callback receives authorization code
4. Exchange code for access token
5. Store credentials securely

### Publishing Posts

#### Automatic Publishing
When a post is scheduled, it will automatically publish to all connected platforms at the scheduled time.

```php
// Posts are published via job queue
PublishPostToSocialMediaJob::dispatch($post, 'facebook');
```

#### Manual Publishing
Users can manually publish from the post editor or calendar view.

### Platform-Specific Features

#### Facebook
- Supports text, images, videos, and carousels
- Max 10 images per carousel
- Max 1GB video size
- Analytics: impressions, engagement, clicks, reactions

#### Instagram
- Requires at least one image or video
- Max 10 images per carousel
- Max 100MB video size
- Max 60 seconds video duration
- Square or portrait aspect ratio (1:1 to 1.91:1)
- Analytics: engagement, impressions, reach, saves

#### WhatsApp
- Broadcast messages to customer lists
- Template messages for automated flows
- Media sharing (images, videos, documents)
- Max 16MB file size
- Message delivery status tracking

#### TikTok
- Video-only platform
- Min 3 seconds, max 180 seconds duration
- Max 287MB video size
- Analytics: views, likes, comments, shares

## Architecture

### Service Layer
```
app/Domain/BizBoost/Services/SocialMedia/
├── SocialMediaServiceInterface.php    # Common interface
├── SocialMediaFactory.php             # Factory for creating services
├── FacebookService.php                # Facebook implementation
├── InstagramService.php               # Instagram implementation
├── WhatsAppService.php                # WhatsApp implementation
└── TikTokService.php                  # TikTok implementation
```

### Job Queue
```
app/Jobs/BizBoost/
└── PublishPostToSocialMediaJob.php    # Async publishing job
```

### Controllers
```
app/Http/Controllers/BizBoost/
└── SocialMediaIntegrationController.php  # OAuth and management
```

### Database
```
bizboost_integrations table:
- business_id
- provider (facebook, instagram, whatsapp, tiktok)
- provider_user_id
- provider_page_id
- provider_page_name
- access_token (encrypted)
- refresh_token (encrypted)
- token_expires_at
- scopes
- meta (JSON)
- status (active, expired, disconnected)
- connected_at
- last_used_at
```

## API Methods

### Common Interface

All services implement:

```php
interface SocialMediaServiceInterface
{
    public function getAuthUrl(string $redirectUri, array $scopes = []): string;
    public function exchangeCodeForToken(string $code, string $redirectUri): array;
    public function publishPost(BizBoostPostModel $post): array;
    public function deletePost(string $postId): bool;
    public function getPostAnalytics(string $postId): array;
    public function refreshToken(): array;
    public function validateToken(): bool;
}
```

### Platform-Specific Methods

#### FacebookService
```php
public function getUserPages(string $accessToken): array;
public function getLongLivedToken(string $shortLivedToken): array;
```

#### InstagramService
```php
public function getInstagramBusinessAccount(string $pageId, string $accessToken): ?array;
```

#### WhatsAppService
```php
public function sendMessage(string $to, string $message, ?string $mediaUrl = null): array;
public function sendTemplateMessage(string $to, string $templateName, array $parameters = []): array;
public function sendBulkMessages(array $recipients, string $message): array;
public function registerWebhook(string $callbackUrl, string $verifyToken): array;
```

#### TikTokService
```php
public function getUserInfo(string $accessToken): array;
public function getUserVideos(int $maxCount = 20): array;
public function revokeToken(): bool;
```

## Error Handling

### Token Expiration
- Tokens are automatically refreshed before publishing
- If refresh fails, integration status is set to 'expired'
- User is notified to reconnect

### Publishing Failures
- Jobs retry 3 times with exponential backoff (1min, 5min, 15min)
- Failed posts are marked with error message
- Users can manually retry from the UI

### Rate Limiting
- Facebook: 200 calls per hour per user
- Instagram: 200 calls per hour per user
- TikTok: Varies by endpoint
- Services handle rate limit errors gracefully

## Security

### Token Storage
- Access tokens are encrypted in database
- Refresh tokens are encrypted in database
- Tokens are hidden from API responses

### OAuth State
- CSRF tokens validate OAuth callbacks
- Business ID stored in session during OAuth flow

### Permissions
- Users can only manage integrations for their own business
- Team members inherit business integrations

## Testing

### Development Mode
During development, use test apps:
- Facebook: Create test app in development mode
- TikTok: Use sandbox environment

### Test Accounts
- Facebook: Create test users in app dashboard
- Instagram: Connect test Instagram account to test Facebook page

## Troubleshooting

### "No pages found"
- Ensure user has admin access to at least one Facebook Page
- Check app permissions include `pages_show_list`

### "No Instagram account found"
- Ensure Instagram account is converted to Business account
- Ensure Instagram is connected to a Facebook Page
- Check app has Instagram permissions

### "Token expired"
- Click "Refresh" button in integrations page
- If refresh fails, disconnect and reconnect

### "Publishing failed"
- Check post meets platform requirements (media size, duration, etc.)
- Verify integration status is 'active'
- Check error message in post details

## Monitoring

### Logs
All API calls are logged:
```
storage/logs/laravel.log
```

### Metrics
Track in database:
- `last_used_at` - Last successful API call
- `retry_count` - Number of publishing retries
- `status` - Integration health status

## Future Enhancements

### Planned Features
- [ ] Twitter/X integration
- [ ] LinkedIn integration
- [ ] Pinterest integration
- [ ] YouTube integration
- [ ] Multi-account support per platform
- [ ] Advanced scheduling (best time to post)
- [ ] A/B testing for posts
- [ ] Automated responses (WhatsApp)
- [ ] Story posting (Instagram/Facebook)
- [ ] Reels posting (Instagram)

### API Version Updates
- Monitor platform API changelog
- Test new versions in development
- Update `FACEBOOK_API_VERSION` when upgrading

## Resources

### Documentation
- [Facebook Graph API](https://developers.facebook.com/docs/graph-api/)
- [Instagram Graph API](https://developers.facebook.com/docs/instagram-api/)
- [WhatsApp Business API](https://developers.facebook.com/docs/whatsapp/)
- [TikTok for Developers](https://developers.tiktok.com/doc/)

### Support
- Facebook: [Developer Community](https://developers.facebook.com/community/)
- TikTok: [Developer Forum](https://developers.tiktok.com/community/)

## Changelog

### December 7, 2025
- Initial implementation
- Facebook, Instagram, WhatsApp, TikTok support
- OAuth flows
- Automated publishing
- Token management
- Error handling and retries
