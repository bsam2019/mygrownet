# Media Upload System

**Last Updated:** January 20, 2026
**Status:** Planning/Implementation

## Overview

Centralized media upload system to reduce code duplication across modules while maintaining backward compatibility with existing implementations.

## Current State

### Existing Upload Implementations

Each module currently has its own upload implementation:

1. **Marketplace** - `marketplace.seller.profile.upload-logo`
2. **BizBoost** - `bizboost.setup.logo`, `bizboost.whitelabel.logo`
3. **GrowFinance** - `growfinance.whitelabel.logo`
4. **Quick Invoice** - `quick-invoice.upload-logo`
5. **GrowBuilder** - Uses media library modal

### Common Pattern

All implementations follow similar patterns:
- File validation (type, size)
- FormData upload
- Progress tracking
- Preview generation
- Error handling

## Proposed Solution

### Phase 1: Create Reusable Components (Non-Breaking)

Create optional reusable components that can be adopted gradually:

1. **Composable**: `useMediaUpload.ts` - Core upload logic
2. **Component**: `MediaUploadButton.vue` - Reusable upload button
3. **Component**: `LogoUploader.vue` - Specialized logo uploader

### Phase 2: Backend Service (Optional)

Create a unified backend service for media handling:
- `app/Services/MediaUploadService.php`
- Handles validation, storage, optimization
- Can be used by all controllers

### Phase 3: Gradual Migration (Optional)

Modules can optionally migrate to use shared components:
- No breaking changes
- Existing code continues to work
- New features use shared components

## Implementation Strategy

### 1. Composable (Already Created)

`resources/js/composables/useMediaUpload.ts`
- Handles file validation
- Upload with progress tracking
- Preview generation
- Error handling

### 2. Reusable Components (To Create)

**MediaUploadButton.vue**
```vue
<template>
  <button @click="triggerUpload">
    <slot :uploading="state.uploading" :progress="state.progress">
      Upload
    </slot>
  </button>
</template>
```

**LogoUploader.vue**
```vue
<template>
  <div>
    <img v-if="modelValue" :src="modelValue" />
    <MediaUploadButton @upload="handleUpload" />
  </div>
</template>
```

### 3. Backend Service (Optional)

```php
class MediaUploadService
{
    public function uploadLogo(UploadedFile $file, string $disk = 'public'): string
    {
        // Validate
        // Optimize
        // Store
        // Return URL
    }
}
```

## Migration Path

### For New Features
Use shared components from day one:
```vue
<LogoUploader 
  v-model="form.logo" 
  :endpoint="route('module.upload-logo')"
/>
```

### For Existing Features
Keep current implementation OR gradually refactor:
```vue
// Option 1: Keep existing code (no changes needed)

// Option 2: Refactor to use composable
const { upload, state } = useMediaUpload({
  endpoint: route('marketplace.seller.profile.upload-logo')
});
```

## Benefits

1. **No Breaking Changes** - Existing code continues to work
2. **Reduced Duplication** - New features use shared code
3. **Consistent UX** - Same upload experience across modules
4. **Easier Maintenance** - Fix bugs in one place
5. **Better Testing** - Test shared components once

## Files Created

### Core Files ✅
- `resources/js/composables/useMediaUpload.ts` - Core upload logic
- `resources/js/components/MediaUploadButton.vue` - Reusable button component
- `resources/js/components/LogoUploader.vue` - Complete logo upload solution
- `app/Services/MediaUploadService.php` - Optional backend service

### Documentation ✅
- `docs/MEDIA_UPLOAD_SYSTEM.md` - System overview and strategy
- `docs/MEDIA_UPLOAD_USAGE_EXAMPLES.md` - Complete usage guide with examples

### Existing (No Changes Required)
- All existing upload implementations remain unchanged
- Can be refactored gradually if desired

## Usage Examples

### Example 1: Using Composable Only
```vue
<script setup>
import { useMediaUpload } from '@/composables/useMediaUpload';

const { upload, state } = useMediaUpload({
  endpoint: route('my-module.upload-logo'),
  maxSize: 5 * 1024 * 1024,
  onSuccess: (url) => form.logo = url
});
</script>

<template>
  <input type="file" @change="upload($event.target.files[0])" />
  <div v-if="state.uploading">{{ state.progress }}%</div>
</template>
```

### Example 2: Using LogoUploader Component (Future)
```vue
<LogoUploader 
  v-model="form.logo"
  :endpoint="route('my-module.upload-logo')"
  :max-size="5 * 1024 * 1024"
/>
```

## Decision: Keep or Refactor?

### Keep Existing Code If:
- Module is stable and working well
- No active development planned
- Team prefers not to touch working code

### Refactor to Shared Components If:
- Adding new upload features
- Fixing bugs in upload logic
- Want consistent UX across modules
- Simplifying maintenance

## Recommendation

**Hybrid Approach:**
1. Keep `useMediaUpload.ts` composable available
2. Don't force migration of existing code
3. Use shared components for new features
4. Refactor existing code only when touching that module

This ensures:
- No breaking changes
- Gradual improvement
- Team flexibility
- Reduced future duplication

## Next Steps

1. ✅ Create composable (done)
2. ✅ Document strategy (done)
3. ✅ Create reusable components (done)
4. ✅ Create backend service (done)
5. ✅ Create usage examples (done)
6. ⏳ Use in next new feature that needs upload
7. ⏳ Optionally refactor existing code when touching those modules

## Changelog

### January 20, 2026
- Created composable `useMediaUpload.ts`
- Created `MediaUploadButton.vue` component
- Created `LogoUploader.vue` component
- Created `MediaUploadService.php` backend service
- Documented strategy and migration path
- Created comprehensive usage examples
- Decided on non-breaking, gradual approach
- **Status: Ready for use in new features**
