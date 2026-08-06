# GrowStream Cloudflare Video Upload Fix

**Date:** August 6, 2026  
**Status:** ✅ Deployed to Production

## Problem

When uploading videos to GrowStream in production:
1. Upload shows 100% progress in the form
2. Then shows error: "Upload failed (network error). Try again."
3. In Cloudflare dashboard, video shows as "Pending upload" forever
4. Video has no name in Cloudflare (displays as "Video has no name")

## Root Cause

The backend was calling the wrong upload initialization method:

1. **Wrong Method Call**: `VideoManagementController::tusInit()` was calling `$provider->getDirectUploadUrl()` which internally calls `createDirectUpload()` - this creates a **one-time PUT upload URL**, not a TUS resumable upload.

2. **Frontend Mismatch**: The frontend (`VideoUploadModal.vue`) was doing **TUS PATCH requests** (resumable chunked uploads), but the backend was providing a PUT upload URL.

3. **Missing Video Name**: The video title/name was never being passed to Cloudflare in the TUS metadata, causing videos to appear as "Video has no name" in the Cloudflare dashboard.

## Solution

### 1. Fixed Backend Controllers

**Admin Controller** (`app/Domain/GrowStream/Presentation/Http/Controllers/Admin/VideoManagementController.php`):
```php
// BEFORE (wrong):
$tus = $provider->getDirectUploadUrl([
    'file_size' => $validated['file_size'],
    'video_id' => $video->id,
]);

// AFTER (correct):
$tus = $provider->createTusUpload($validated['file_size'], [
    'name' => $validated['title'],
    'video_id' => $video->id,
]);
```

**Creator Controller** (`app/Domain/GrowStream/Presentation/Http/Controllers/Web/Creator/CreatorVideoController.php`):
```php
// BEFORE (missing name):
$tus = $provider->createTusUpload(
    (int) $request->file_size,
    ['max_duration_seconds' => (int) config('growstream.upload.max_duration_seconds', 10800)],
);

// AFTER (includes name):
$tus = $provider->createTusUpload(
    (int) $request->file_size,
    [
        'max_duration_seconds' => (int) config('growstream.upload.max_duration_seconds', 10800),
        'name' => $request->title,
    ],
);
```

### 2. Enhanced Cloudflare Provider

**CloudflareStreamProvider** (`app/Domain/GrowStream/Infrastructure/Providers/CloudflareStreamProvider.php`):
```php
public function createTusUpload(int $fileSize, array $metadata = []): array
{
    // ... existing code ...
    
    // Build upload metadata with name if provided
    $uploadMetadata = 'maxDurationSeconds '.base64_encode($maxDuration)
        .',expiry '.base64_encode($expiry);
    
    // NEW: Add video name to TUS metadata
    if (!empty($metadata['name'])) {
        $uploadMetadata .= ',name '.base64_encode($metadata['name']);
    }
    
    // ... rest of method ...
}
```

## Files Changed

1. `app/Domain/GrowStream/Presentation/Http/Controllers/Admin/VideoManagementController.php`
2. `app/Domain/GrowStream/Presentation/Http/Controllers/Web/Creator/CreatorVideoController.php`
3. `app/Domain/GrowStream/Infrastructure/Providers/CloudflareStreamProvider.php`
4. `deploy-growstream-fix.sh` (new deployment script)

## Deployment

Deployed using custom deployment script:
```bash
bash deploy-growstream-fix.sh
```

The script:
- Removes untracked test files
- Stashes local changes
- Pulls latest code from GitHub
- Clears all Laravel caches
- Rebuilds route and config caches
- Optimizes application
- Restarts PHP 8.3-FPM

## Verification

Test video upload at:
- **Admin**: https://mygrownet.com/admin/videos
- **Creator**: https://growstream.mygrownet.com/creator/videos/upload

Expected behavior:
1. ✅ Upload shows progress from 0% to 100%
2. ✅ No network error after 100%
3. ✅ Video appears in Cloudflare with correct title/name
4. ✅ Video status changes from "uploading" → "processing" → "ready"
5. ✅ No "Pending upload" or "Video has no name" in Cloudflare

## Technical Details

### TUS Protocol
TUS (Tus Upload Protocol) is a resumable upload protocol:
- Uses PATCH requests with `Content-Type: application/offset+octet-stream`
- Supports resume via `Upload-Offset` header
- Cloudflare requires TUS for files over 200MB (recommended for all)

### Metadata Format
Cloudflare TUS metadata uses base64-encoded key-value pairs:
```
Upload-Metadata: key1 base64(value1),key2 base64(value2)
```

Example:
```
Upload-Metadata: name bVlWaWRlbw==,maxDurationSeconds MzYwMA==
```

## Related Documentation

- [Cloudflare Stream TUS Docs](https://developers.cloudflare.com/stream/uploading-videos/direct-creator-uploads/)
- [TUS Protocol](https://tus.io/protocols/resumable-upload.html)
- GrowStream Architecture: `docs/platform-evolution/`

## Notes

- PHP-FPM service is `php8.3-fpm` (not `php8.2-fpm`)
- Test video file was accidentally committed and has been removed
- All caches cleared and rebuilt after deployment
