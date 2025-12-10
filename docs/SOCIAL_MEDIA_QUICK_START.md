# Social Media Integration - Quick Start Guide

## For Developers

### 1. Get API Credentials

#### Facebook/Instagram/WhatsApp
1. Visit [Facebook Developers](https://developers.facebook.com/)
2. Create a new app (Business type)
3. Add products: Facebook Login, Instagram Graph API, WhatsApp
4. Get App ID and App Secret from Settings → Basic

#### TikTok
1. Visit [TikTok for Developers](https://developers.tiktok.com/)
2. Create a new app
3. Add Login Kit and Content Posting API
4. Get Client Key and Client Secret

### 2. Configure Environment

Add to `.env`:

```env
FACEBOOK_CLIENT_ID=your_app_id_here
FACEBOOK_CLIENT_SECRET=your_app_secret_here
FACEBOOK_API_VERSION=v18.0

TIKTOK_CLIENT_KEY=your_client_key_here
TIKTOK_CLIENT_SECRET=your_client_secret_here
```

### 3. Set OAuth Redirect URIs

In your app settings, add these redirect URIs:

```
https://yourdomain.com/bizboost/integrations/facebook/callback
https://yourdomain.com/bizboost/integrations/instagram/callback
https://yourdomain.com/bizboost/integrations/whatsapp/callback
https://yourdomain.com/bizboost/integrations/tiktok/callback
```

### 4. Run Migrations

The integration table already exists from previous migrations:
```bash
php artisan migrate
```

### 5. Schedule Commands

The following commands are already scheduled in `routes/console.php`:

- `bizboost:process-campaigns` - Daily at 6:00 AM
- `bizboost:refresh-tokens` - Daily at 3:00 AM

Make sure your cron is running:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### 6. Test Integration

1. Log into BizBoost
2. Go to Integrations page
3. Click "Connect" for any platform
4. Complete OAuth flow
5. Create a test post
6. Schedule it to publish

## For End Users

### Connecting Facebook

1. Go to **BizBoost → Integrations**
2. Click **Connect Facebook**
3. Log in to Facebook
4. Select the Page you want to connect
5. Grant permissions
6. Done! Your posts will now publish to Facebook

### Connecting Instagram

1. First, ensure your Instagram account is:
   - Converted to a Business account
   - Connected to a Facebook Page
2. Go to **BizBoost → Integrations**
3. Click **Connect Instagram**
4. Log in to Facebook
5. Select the Page connected to your Instagram
6. Grant permissions
7. Done! Your posts will now publish to Instagram

### Connecting WhatsApp Business

1. Ensure you have a WhatsApp Business account
2. Go to **BizBoost → Integrations**
3. Click **Connect WhatsApp**
4. Log in to Facebook
5. Select your WhatsApp Business account
6. Grant permissions
7. Done! You can now send broadcasts to customers

### Connecting TikTok

1. Go to **BizBoost → Integrations**
2. Click **Connect TikTok**
3. Log in to TikTok
4. Grant permissions
5. Done! Your videos will now publish to TikTok

## Publishing Posts

### Automatic Publishing

When you create a post and schedule it:
1. Select target platforms (Facebook, Instagram, etc.)
2. Set scheduled time
3. Save post
4. At the scheduled time, it will automatically publish to all selected platforms

### Manual Publishing

You can also publish immediately:
1. Create or edit a post
2. Select target platforms
3. Click "Publish Now"
4. Post publishes immediately to all selected platforms

## Platform Requirements

### Facebook
- ✅ Text posts
- ✅ Single image
- ✅ Single video
- ✅ Multiple images (carousel)
- ⚠️ Max 10 images per post
- ⚠️ Max 1GB video size

### Instagram
- ✅ Single image
- ✅ Single video
- ✅ Multiple images (carousel)
- ⚠️ Requires at least one image or video
- ⚠️ Max 10 images per post
- ⚠️ Max 100MB video size
- ⚠️ Max 60 seconds video duration
- ⚠️ Square or portrait aspect ratio

### WhatsApp
- ✅ Text messages
- ✅ Images
- ✅ Videos
- ✅ Documents
- ⚠️ Max 16MB file size
- ⚠️ Broadcast to customer lists only

### TikTok
- ✅ Videos only
- ⚠️ Min 3 seconds duration
- ⚠️ Max 180 seconds duration
- ⚠️ Max 287MB file size

## Troubleshooting

### "No pages found" (Facebook)
- Make sure you're an admin of at least one Facebook Page
- Create a Page if you don't have one

### "No Instagram account found"
- Convert your Instagram to a Business account
- Connect it to a Facebook Page
- Try connecting again

### "Token expired"
- Click the "Refresh" button next to the integration
- If that doesn't work, disconnect and reconnect

### "Publishing failed"
- Check that your media meets platform requirements
- Verify the integration is still connected
- Check the error message in post details
- Try publishing again

## Getting Help

For detailed documentation, see:
- [Full Integration Guide](./SOCIAL_MEDIA_API_INTEGRATION.md)
- [Facebook API Docs](https://developers.facebook.com/docs/)
- [TikTok API Docs](https://developers.tiktok.com/doc/)
