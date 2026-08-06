# GrowStream Video Display & Thumbnail Fix

**Date:** August 6, 2026  
**Status:** ✅ Deployed to Production

## Problems Fixed

### 1. Video Thumbnail Not Showing
**Issue:** Video thumbnails displayed as broken images in admin video management  
**Root Cause:** 
- Provider video ID was malformed: `0d976f52aeb61b42318ed6ff2994a7ff?tusv2=true` (included query parameter)
- Thumbnail URL was broken: `https:///0d976f52aeb61b42318ed6ff2994a7ff?tusv2=true/thumbnails/thumbnail.jpg` (missing hostname)

### 2. Video Not Appearing for Users
**Issue:** Video stuck in "processing" status and not published  
**Root Cause:**
- ProcessVideoJob needed to run to update status from "processing" to "ready"
- Video needed to be manually published (`is_published = 1`)

## Solutions Implemented

### Fix 1: Correct Provider Video ID Extraction

**File:** `app/Domain/GrowStream/Infrastructure/Providers/CloudflareStreamProvider.php`

**Before:**
```php
// uid is the last path segment of the tus endpoint (e.g. .../upload/{uid})
$segments = explode('/', rtrim($uploadUrl, '/'));
$uid = (string) end($segments);
```

**After:**
```php
// uid is the last path segment of the tus endpoint (e.g. .../upload/{uid})
// Remove any query parameters from the URL first
$urlWithoutQuery = parse_url($uploadUrl, PHP_URL_PATH) ?: $uploadUrl;
$segments = explode('/', rtrim($urlWithoutQuery, '/'));
$uid = (string) end($segments);
```

**Why:** Cloudflare TUS upload URLs include query parameters like `?tusv2=true`. When extracting the video ID from the URL, we now strip query parameters first using `parse_url()`.

### Fix 2: Handle Missing Customer Subdomain in Thumbnail URL

**File:** `app/Domain/GrowStream/Infrastructure/Providers/CloudflareStreamProvider.php`

**Before:**
```php
protected function getThumbnailUrl(string $providerVideoId): string
{
    $host = $this->normalizedCustomerSubdomain();
    return "https://{$host}/{$providerVideoId}/thumbnails/thumbnail.jpg";
}
```

**After:**
```php
protected function getThumbnailUrl(string $providerVideoId): string
{
    $host = $this->normalizedCustomerSubdomain();
    
    // If no custom subdomain, use default Cloudflare hostname
    if ($host === null) {
        $host = "customer-{$this->accountId}.cloudflarestream.com";
    }

    return "https://{$host}/{$providerVideoId}/thumbnails/thumbnail.jpg";
}
```

**Why:** When `customerSubdomain` is not configured in `config/growstream.php`, the method returned `null`, resulting in broken URLs like `https:///video-id/...`. Now it falls back to the default Cloudflare hostname.

### Fix 3: Manual Database Corrections

For the existing test video (ID 28), we manually corrected:

```php
DB::table('growstream_videos')->where('id', 28)->update([
    'provider_video_id' => '0d976f52aeb61b42318ed6ff2994a7ff', // Removed ?tusv2=true
    'thumbnail_url' => 'https://customer-1d1529172d2e0cd6300114cc1a7ab167.cloudflarestream.com/0d976f52aeb61b42318ed6ff2994a7ff/thumbnails/thumbnail.jpg',
    'playback_url' => 'https://customer-1d1529172d2e0cd6300114cc1a7ab167.cloudflarestream.com/0d976f52aeb61b42318ed6ff2994a7ff/manifest/video.m3u8'
]);
```

### Fix 4: Video Processing & Publishing

1. **Dispatched ProcessVideoJob** to check Cloudflare status and update video
2. **Published the video** so it appears in public listings:
   ```php
   DB::table('growstream_videos')->where('id', 28)->update([
       'is_published' => 1,
       'published_at' => now()
   ]);
   ```

## Video Display Flow

### Where Videos Appear

Videos are displayed on multiple pages after being published:

1. **Home Page** (`/` or `/growstream`)
   - Featured videos (carousel)
   - Trending videos (sorted by view count)
   - Recent videos (latest published)
   - Continue watching (for authenticated users)

2. **Browse Page** (`/browse` or `/growstream/browse`)
   - All published videos with filters:
     - Search by title/description
     - Filter by category
     - Filter by content type (movie, series, etc.)
     - Filter by access level (free/premium)
     - Sort by: latest, popular, trending

3. **Video Detail Page** (`/video/{slug}`)
   - Full video player
   - Video metadata
   - Related videos
   - Comments (if enabled)

4. **Channel/Creator Pages** (`/c/{slug}` or `/channel/{slug}`)
   - All videos by a specific creator

5. **Search Results** (`/search?q=term`)
   - Videos matching search term

### Publishing Requirements

For a video to appear publicly, it must meet ALL these criteria:

1. ✅ `upload_status` = `'ready'` (not "processing", "uploading", or "failed")
2. ✅ `is_published` = `1`
3. ✅ `published_at` IS NOT NULL
4. ✅ Has valid `playback_url` and `thumbnail_url`

### Access Control

Videos have two access levels:
- **Free**: Anyone can watch
- **Premium**: Requires paid subscription

## Files Changed

1. `app/Domain/GrowStream/Infrastructure/Providers/CloudflareStreamProvider.php` - Fixed UID extraction and thumbnail URL generation
2. Database - Corrected existing video (ID 28)

## Deployment

```bash
cd /var/www/mygrownet.com
git pull origin main
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan route:cache
php artisan config:cache
php artisan optimize
```

## Verification

### Admin Side
✅ Video thumbnail displays correctly in admin video management  
✅ Video shows "ready" status (not "processing")  
✅ Video is published

### Public Side
Test video appears on:
- ✅ Home page (in recent videos section)
- ✅ Browse page
- ✅ Search results
- ✅ Direct link: `https://growstream.mygrownet.com/video/test-video`

## Queue Worker Status

The queue worker is running via supervisor:
```bash
systemctl status supervisor
# Shows: php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

ProcessVideoJob runs automatically on the "high" priority queue and:
1. Polls Cloudflare for video status
2. Updates `playback_url`, `thumbnail_url`, `duration`
3. Changes `upload_status` from "processing" → "ready"
4. Dispatches follow-up jobs (GenerateThumbnailsJob, UpdateVideoAnalyticsJob)

## Future Uploads

All future video uploads will now:
1. ✅ Extract correct video ID (without query parameters)
2. ✅ Generate correct thumbnail URL (with proper hostname)
3. ✅ Process automatically via queue worker
4. ✅ Videos still need manual publishing via admin interface

## Notes

- Queue worker runs 24/7 via supervisor
- Videos process automatically but need manual publishing
- Thumbnail generation may take a few seconds after upload completes
- Cloudflare videos are transcoded and may take 1-5 minutes to become "ready"

## Related Documentation

- [GrowStream Upload Fix](GROWSTREAM_UPLOAD_FIX.md) - TUS upload metadata fix
- [Cloudflare Stream Docs](https://developers.cloudflare.com/stream/)
- [TUS Protocol](https://tus.io/protocols/resumable-upload.html)
