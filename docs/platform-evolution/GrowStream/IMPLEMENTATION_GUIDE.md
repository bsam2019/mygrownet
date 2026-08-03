# GrowStream Implementation Quick Start Guide

**Last Updated:** March 11, 2026  
**Status:** Ready for Development  
**Related:** See `GROWSTREAM_CONCEPT.md` for complete specification

---

## Quick Start Checklist

### Prerequisites
- [ ] Laravel 12 installed
- [ ] Vue 3 + TypeScript configured
- [ ] MySQL database ready
- [ ] Redis installed
- [ ] Cloudflare account (or use local provider for dev)
- [ ] Wasabi/S3 bucket for assets

### Phase 1: Database Setup (Day 1)

```bash
# Create migrations
php artisan make:migration create_videos_table
php artisan make:migration create_video_categories_table
php artisan make:migration create_video_series_table
php artisan make:migration create_creator_profiles_table
php artisan make:migration create_watch_history_table
php artisan make:migration create_video_subscriptions_table

# Run migrations
php artisan migrate
```

### Phase 2: Backend Core (Days 2-5)

```bash
# Create models
php artisan make:model Video
php artisan make:model VideoSeries
php artisan make:model VideoCategory
php artisan make:model CreatorProfile
php artisan make:model WatchHistory

# Create controllers
php artisan make:controller Api/VideoController
php artisan make:controller Api/SeriesController
php artisan make:controller Api/WatchController
php artisan make:controller Admin/VideoManagementController

# Create services
mkdir -p app/Modules/GrowStream/Infrastructure/Providers
# Create VideoProviderInterface.php
# Create CloudflareStreamProvider.php
# Create LocalVideoProvider.php
# Create VideoProviderFactory.php
```

### Phase 3: Frontend Setup (Days 6-10)

```bash
# Create Vue pages
mkdir -p resources/js/Pages/GrowStream/Public
mkdir -p resources/js/Pages/GrowStream/Admin
mkdir -p resources/js/Components/GrowStream

# Create components
# VideoCard.vue
# VideoPlayer.vue
# VideoGrid.vue
# Home.vue
# Browse.vue
# Watch.vue
```

### Phase 4: Integration (Days 11-12)

- [ ] Configure Cloudflare Stream or local provider
- [ ] Set up file storage (Wasabi/S3)
- [ ] Configure queues for video processing
- [ ] Set up caching strategy
- [ ] Implement access control middleware

### Phase 5: Testing (Days 13-14)

- [ ] Unit tests for models
- [ ] Feature tests for API endpoints
- [ ] Browser tests for key user flows
- [ ] Performance testing
- [ ] Security testing

---

## Essential Code Snippets

### Video Provider Factory

```php
// app/Modules/GrowStream/Infrastructure/Providers/VideoProviderFactory.php
class VideoProviderFactory
{
    public static function make(string $provider = null): VideoProviderInterface
    {
        $provider = $provider ?? config('growstream.default_provider');
        
        return match($provider) {
            'cloudflare' => app(CloudflareStreamProvider::class),
            'local' => app(LocalVideoProvider::class),
            default => throw new InvalidProviderException()
        };
    }
}
```

### Video Access Middleware

```php
// app/Http/Middleware/CheckVideoAccess.php
class CheckVideoAccess
{
    public function handle(Request $request, Closure $next)
    {
        $video = $request->route('video');
        
        if ($video->access_level === 'free') {
            return $next($request);
        }
        
        if (!$request->user()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }
        
        if (!$request->user()->hasAccessTo($video)) {
            return response()->json(['error' => 'Subscription required'], 403);
        }
        
        return $next($request);
    }
}
```

### Video Upload Component

```vue
<!-- resources/js/Components/GrowStream/UploadForm.vue -->
<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const uploading = ref(false);
const progress = ref(0);

async function uploadVideo(file: File, metadata: any) {
  uploading.value = true;
  
  // Get upload URL
  const { video_id, upload_url } = await api.post('/api/v1/creator/videos/init-upload', metadata);
  
  // Upload to Cloudflare
  const formData = new FormData();
  formData.append('file', file);
  
  const response = await fetch(upload_url, {
    method: 'POST',
    body: formData,
  });
  
  const { uid } = await response.json();
  
  // Complete upload
  await api.post(`/api/v1/creator/videos/${video_id}/complete-upload`, {
    provider_video_id: uid,
  });
  
  uploading.value = false;
  router.visit('/stream/creator/videos');
}
</script>
```

---

## Configuration

### Environment Variables

```bash
# Add to .env
GROWSTREAM_VIDEO_PROVIDER=local  # Use 'cloudflare' when ready
CLOUDFLARE_ACCOUNT_ID=
CLOUDFLARE_API_TOKEN=
WASABI_ACCESS_KEY_ID=
WASABI_SECRET_ACCESS_KEY=
WASABI_BUCKET=mygrownet-growstream
```

### Config File

```bash
# Publish config
php artisan vendor:publish --tag=growstream-config

# Or create manually
touch config/growstream.php
```

---

## Testing Strategy

### Unit Tests
```bash
php artisan make:test VideoTest --unit
php artisan make:test VideoProviderTest --unit
```

### Feature Tests
```bash
php artisan make:test VideoApiTest
php artisan make:test WatchProgressTest
```

### Run Tests
```bash
./vendor/bin/pest
./vendor/bin/pest --filter=Video
```

---

## Deployment Checklist

- [ ] Run migrations on production
- [ ] Seed initial categories
- [ ] Configure Cloudflare Stream
- [ ] Set up Wasabi bucket
- [ ] Configure queue workers
- [ ] Set up cron jobs
- [ ] Configure CDN
- [ ] SSL certificates
- [ ] Monitoring and alerts
- [ ] Backup strategy

---

## Common Issues and Solutions

### Issue: Video upload fails
**Solution:** Check file size limits, MIME types, and provider credentials

### Issue: Playback URL expired
**Solution:** Implement URL refresh mechanism, increase expiration time

### Issue: Slow video loading
**Solution:** Enable CDN, optimize encoding settings, implement caching

### Issue: High Cloudflare costs
**Solution:** Optimize quality settings, implement smart caching, review pricing tier

---

## Resources

- **Main Concept Doc:** `GROWSTREAM_CONCEPT.md`
- **Cloudflare Stream Docs:** https://developers.cloudflare.com/stream/
- **Laravel Docs:** https://laravel.com/docs/12.x
- **Vue 3 Docs:** https://vuejs.org/guide/
- **Video.js Player:** https://videojs.com/

---

## Support and Maintenance

**Development Team Contact:** [Your team contact]  
**Issue Tracking:** [Your issue tracker]  
**Documentation Updates:** Update this file and GROWSTREAM_CONCEPT.md as needed

