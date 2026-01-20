# Media Upload System - Usage Examples

**Last Updated:** January 20, 2026

## Quick Start

The media upload system provides three levels of abstraction:

1. **Composable** - `useMediaUpload()` - Low-level, maximum flexibility
2. **Button Component** - `MediaUploadButton` - Reusable upload button
3. **Logo Component** - `LogoUploader` - Complete logo upload solution

## Example 1: Using LogoUploader (Recommended for Logos)

The simplest way to add logo upload functionality:

```vue
<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import LogoUploader from '@/components/LogoUploader.vue';

const form = ref({
    logo: null as string | null,
});

const handleLogoUploaded = (url: string) => {
    console.log('Logo uploaded:', url);
    // Optionally save to backend immediately
    // router.post(route('my-module.save-logo'), { logo: url });
};
</script>

<template>
    <LogoUploader
        v-model="form.logo"
        :endpoint="route('my-module.upload-logo')"
        label="Business Logo"
        hint="PNG or SVG recommended, max 5MB"
        :required="true"
        @uploaded="handleLogoUploaded"
    />
</template>
```

## Example 2: Using MediaUploadButton

For custom upload UI with more control:

```vue
<script setup lang="ts">
import { ref } from 'vue';
import { route } from 'ziggy-js';
import MediaUploadButton from '@/components/MediaUploadButton.vue';

const logoUrl = ref<string | null>(null);

const handleSuccess = (url: string, file: File) => {
    logoUrl.value = url;
    console.log('Uploaded:', file.name, 'to', url);
};

const handleError = (error: string) => {
    alert('Upload failed: ' + error);
};
</script>

<template>
    <div>
        <img v-if="logoUrl" :src="logoUrl" class="w-32 h-32 object-contain" />
        
        <MediaUploadButton
            :endpoint="route('my-module.upload-logo')"
            variant="dashed"
            size="md"
            @success="handleSuccess"
            @error="handleError"
        >
            <template #default="{ uploading, progress }">
                {{ uploading ? `Uploading ${progress}%` : 'Upload Logo' }}
            </template>
        </MediaUploadButton>
    </div>
</template>
```

## Example 3: Using Composable Only

For maximum flexibility and custom UI:

```vue
<script setup lang="ts">
import { ref } from 'vue';
import { route } from 'ziggy-js';
import { useMediaUpload } from '@/composables/useMediaUpload';

const { state, upload } = useMediaUpload({
    endpoint: route('my-module.upload-logo'),
    maxSize: 5 * 1024 * 1024,
    acceptedTypes: ['image/jpeg', 'image/png', 'image/svg+xml'],
    onSuccess: (url, file) => {
        console.log('Success!', url);
    },
    onError: (error) => {
        console.error('Error:', error);
    },
});

const handleFileSelect = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (file) {
        await upload(file);
    }
};
</script>

<template>
    <div>
        <input type="file" accept="image/*" @change="handleFileSelect" />
        
        <div v-if="state.uploading">
            Uploading: {{ state.progress }}%
        </div>
        
        <div v-if="state.error" class="text-red-600">
            {{ state.error }}
        </div>
        
        <img v-if="state.previewUrl" :src="state.previewUrl" />
    </div>
</template>
```

## Example 4: Backend Controller (Using Service)

Optional backend service for consistent handling:

```php
<?php

namespace App\Http\Controllers\MyModule;

use App\Http\Controllers\Controller;
use App\Services\MediaUploadService;
use Illuminate\Http\Request;

class MyModuleController extends Controller
{
    public function __construct(
        protected MediaUploadService $mediaService
    ) {}

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:5120', // 5MB
        ]);

        try {
            $url = $this->mediaService->uploadLogo(
                $request->file('file'),
                disk: 'public',
                directory: 'my-module/logos'
            );

            return response()->json([
                'success' => true,
                'url' => $url,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
```

## Example 5: Backend Controller (Traditional Way)

You can also continue using traditional upload methods:

```php
public function uploadLogo(Request $request)
{
    $request->validate([
        'file' => 'required|image|max:5120',
    ]);

    $path = $request->file('file')->store('logos', 'public');
    $url = Storage::disk('public')->url($path);

    return response()->json([
        'success' => true,
        'url' => $url,
    ]);
}
```

## Component Props Reference

### LogoUploader Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | `string \| null` | `null` | Current logo URL (v-model) |
| `endpoint` | `string` | required | Upload API endpoint |
| `maxSize` | `number` | `5242880` | Max file size in bytes (5MB) |
| `label` | `string` | `'Logo'` | Label text |
| `hint` | `string` | `'PNG, JPG...'` | Hint text below upload area |
| `required` | `boolean` | `false` | Show required asterisk |
| `disabled` | `boolean` | `false` | Disable upload |
| `aspectRatio` | `'square' \| 'wide' \| 'auto'` | `'auto'` | Preview aspect ratio |
| `showRemove` | `boolean` | `true` | Show remove button |

### LogoUploader Events

| Event | Payload | Description |
|-------|---------|-------------|
| `update:modelValue` | `string \| null` | Emitted when logo changes |
| `uploaded` | `string` | Emitted on successful upload |
| `removed` | - | Emitted when logo is removed |

### MediaUploadButton Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `endpoint` | `string` | required | Upload API endpoint |
| `maxSize` | `number` | `5242880` | Max file size in bytes |
| `acceptedTypes` | `string[]` | `['image/...']` | Accepted MIME types |
| `accept` | `string` | `'image/*'` | HTML accept attribute |
| `disabled` | `boolean` | `false` | Disable button |
| `variant` | `'button' \| 'dashed' \| 'icon'` | `'button'` | Button style |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Button size |

### MediaUploadButton Events

| Event | Payload | Description |
|-------|---------|-------------|
| `success` | `(url: string, file: File)` | Upload succeeded |
| `error` | `(error: string)` | Upload failed |
| `uploading` | `(progress: number)` | Upload progress |

## Migration Guide

### Migrating Existing Upload Code

**Before (existing code):**
```vue
<script setup>
const uploadingLogo = ref(false);
const logoPreview = ref(null);

const handleLogoUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;
    
    logoPreview.value = URL.createObjectURL(file);
    uploadingLogo.value = true;
    
    const formData = new FormData();
    formData.append('logo', file);
    
    try {
        const response = await axios.post(route('upload-logo'), formData);
        form.logo = response.data.url;
    } catch (error) {
        console.error(error);
    } finally {
        uploadingLogo.value = false;
    }
};
</script>

<template>
    <input type="file" @change="handleLogoUpload" />
    <div v-if="uploadingLogo">Uploading...</div>
</template>
```

**After (using LogoUploader):**
```vue
<script setup>
import LogoUploader from '@/components/LogoUploader.vue';

const form = ref({ logo: null });
</script>

<template>
    <LogoUploader
        v-model="form.logo"
        :endpoint="route('upload-logo')"
    />
</template>
```

**Result:** 30+ lines reduced to 3 lines!

## Best Practices

1. **Use LogoUploader for logos** - It handles everything
2. **Use MediaUploadButton for custom UI** - More flexibility
3. **Use composable for complex cases** - Maximum control
4. **Keep existing code if it works** - No need to refactor everything
5. **Use new components for new features** - Reduce duplication going forward

## Troubleshooting

### Upload fails with 422 error
- Check file size limits on backend
- Verify MIME type validation
- Check CSRF token

### Preview not showing
- Ensure URL is accessible
- Check CORS settings for cross-origin images
- Verify storage disk is publicly accessible

### Progress not updating
- Ensure backend supports chunked uploads
- Check network tab for upload progress events

## Next Steps

- Use these components in your next feature
- Optionally refactor existing upload code
- Report any issues or suggestions
